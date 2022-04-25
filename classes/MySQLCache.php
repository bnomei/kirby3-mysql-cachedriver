<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Cache\FileCache;
use Kirby\Cache\Value;
use Kirby\Toolkit\A;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;
use PDO;
use PDOStatement;

final class MySQLCache extends FileCache
{
    public const DB_VERSION = '1';

    private $shutdownCallbacks = [];

    /**
     * @var PDO
     */
    protected $database;
    /**
     * @var PDOStatement
     */
    private $deleteStatement;
    /**
     * @var PDOStatement
     */
    private $insertStatement;
    /**
     * @var PDOStatement
     */
    private $selectStatement;
    /**
     * @var PDOStatement
     */
    private $updateStatement;

    /** @var array $store */
    private $store;

    /**
     * @var int
     */
    private $transactionOpen = false;

    public function __construct(array $options = [])
    {
        $this->setOptions($options);

        parent::__construct($this->options);

        $this->loadDatabase();


        $this->database->exec(
            'CREATE TABLE IF NOT EXISTS '.$this->dbtbl().' (
                `id` VARCHAR(512) NOT NULL,
                `expire_at` INT NULL,
                `data` MEDIUMTEXT NULL,
                `key` INT NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`key`),
                UNIQUE INDEX `key_UNIQUE` (`key` ASC));'
        );

        $this->prepareStatements();
        $this->store = [];

        if ($this->options['debug']) {
            $this->flush();
        }

        $this->garbagecollect();
    }

    public function dbtbl() :string
    {
        $dbname = $this->option('dbname');
        $tablename = $this->option('tablename') . '_' . static::DB_VERSION;

        return '`'.$dbname.'`.`'.$tablename.'`';
    }

    public function register_shutdown_function($callback)
    {
        $this->shutdownCallbacks[] = $callback;
    }

    public function __destruct()
    {
        foreach ($this->shutdownCallbacks as $callback) {
            if (!is_string($callback) && is_callable($callback)) {
                $callback();
            }
        }

        if ($this->database) {
            // $this->database->close();
            $this->database = null;
        }
    }

    /**
     * @param string|null $key
     * @return array
     */
    public function option(?string $key = null)
    {
        if ($key) {
            return A::get($this->options, $key);
        }
        return $this->options;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value, int $minutes = 0): bool
    {
        /* SHOULD SET EVEN IN DEBUG
        if ($this->option('debug')) {
            return true;
        }
        */

        return $this->updateOrInsert($key, $value, $minutes);
    }

    private function updateOrInsert(string $key, $value, int $minutes = 0): bool
    {
        $rawKey = $key;
        $key = $this->key($key);
        $value = new Value($value, $minutes);
        $expire = $value->expires();
        $data = htmlspecialchars($value->toJson(), ENT_QUOTES);

        if ($this->existsEvenIfExpired($rawKey)) {
            $this->updateStatement->bindValue(':id', $key, PDO::PARAM_STR);
            $this->updateStatement->bindValue(':expire_at', $expire ?? 0, PDO::PARAM_INT);
            $this->updateStatement->bindValue(':data', $data, PDO::PARAM_STR);
            $this->updateStatement->execute();
        } else {
            $this->insertStatement->bindValue(':id', $key, PDO::PARAM_STR);
            $this->insertStatement->bindValue(':expire_at', $expire ?? 0, PDO::PARAM_INT);
            $this->insertStatement->bindValue(':data', $data, PDO::PARAM_STR);
            $this->insertStatement->execute();
        }

        if ($this->option('store') && (empty($this->option('store-ignore')) || str_contains($key, $this->option('store-ignore')) === false)) {
            $this->store[$key] = $value;
        }

        return true;
    }

    private function existsEvenIfExpired(string $key): bool
    {
        $key = $this->key($key);

        $this->selectStatement->bindValue(':id', $key, PDO::PARAM_STR);
        $this->selectStatement->execute();
        $results = $this->selectStatement->fetch(PDO::FETCH_ASSOC);

        return $results !== false;
    }

    /**
     * @inheritDoc
     */
    public function retrieve(string $key): ?Value
    {
        $key = $this->key($key);

        $value = A::get($this->store, $key);
        if ($value === null) {
            $this->selectStatement->bindValue(':id', $key, PDO::PARAM_STR);
            $this->selectStatement->execute();
            $results = $this->selectStatement->fetch(PDO::FETCH_ASSOC);
            if ($results === false) {
                return null;
            }
            $value = htmlspecialchars_decode(strval($results['data']), ENT_QUOTES);
            $value = $value ? Value::fromJson($value) : null;

            if ($this->option('store') && (empty($this->option('store-ignore')) || str_contains($key, $this->option('store-ignore')) === false)) {
                $this->store[$key] = $value;
            }
        }
        return $value;
    }

    public function get(string $key, $default = null)
    {
        if ($this->option('debug')) {
            return $default;
        }

        return parent::get($key, $default);
    }

    /**
     * @inheritDoc
     */
    public function remove(string $key): bool
    {
        $key = $this->key($key);

        if (array_key_exists($key, $this->store)) {
            unset($this->store[$key]);
        }

        $this->deleteStatement->bindValue(':id', $key, PDO::PARAM_STR);
        $this->deleteStatement->execute();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function flush(): bool
    {
        $this->store = [];
        $success = $this->database->exec("DELETE FROM ".$this->dbtbl()." WHERE `id` != '' ");

        return $success && $success > 0;
    }

    public function garbagecollect(): bool
    {
        $success = $this->database->exec("DELETE FROM ".$this->dbtbl()." WHERE `expire_at` > 0 AND `expire_at` <= " . time());

        return $success && $success > 0;
    }

    private static $singleton;
    public static function singleton(array $options = []): self
    {
        if (self::$singleton) {
            return self::$singleton;
        }
        self::$singleton = new self($options);
        return self::$singleton;
    }

    private function loadDatabase()
    {
        try {
            $username = $this->option('username');
            $password = $this->option('password');
            $dbname = $this->option('dbname');
            $dns = [];
            
            if ($socket = $this->option('unix_socket')) {
                $dns = [
                    'mysql:unix_socket=' . $socket,
                    'port=' . $this->option('port'),
                ];
            } else {
                $dns = [
                    'mysql:host=' . $this->option('host')
                ];
            }

            // create db IF NOT EXISTS
            $this->database = new PDO(
                implode(';', $dns),
                $username,
                $password
            );
            $this->database->exec("CREATE DATABASE IF NOT EXISTS `$dbname`;")
            or die(print_r($this->database->errorInfo(), true));

            // connect to db
            $dns[] = 'dbname=' . $dbname;
            $this->database = new PDO(
                implode(';', $dns),
                $username,
                $password
            );
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    private function setOptions(array $options)
    {
        $root = null;
        $cache = kirby()->cache('bnomei.mysql-cachedriver');
        if (is_a($cache, FileCache::class)) {
            $root = A::get($cache->options(), 'root');
            if ($prefix =  A::get($cache->options(), 'prefix')) {
                $root .= '/' . $prefix;
            }
        } else {
            $root = kirby()->roots()->cache();
        }

        $this->options = array_merge([
            'root' => $root,
            'debug' => \option('debug'),
            'store' => \option('bnomei.mysql-cachedriver.store', true),
            'store-ignore' => \option('bnomei.mysql-cachedriver.store-ignore'),
            'host' => \option('bnomei.mysql-cachedriver.host'),
            'unix_socket' => \option('bnomei.mysql-cachedriver.unix_socket'),
            'port' => \option('bnomei.mysql-cachedriver.port'),
            'dbname' => \option('bnomei.mysql-cachedriver.dbname'),
            'tablename' => \option('bnomei.mysql-cachedriver.tablename'),
            'username' => \option('bnomei.mysql-cachedriver.username'),
            'password' => \option('bnomei.mysql-cachedriver.password'),
        ], $options);

        foreach ($this->options as $key => $call) {
            if (!is_string($call) && is_callable($call) && in_array($key, [
                    'host',
                    'unix_socket',
                    'port',
                    'dbname',
                    'tablename',
                    'username',
                    'password',
                ])) {
                $this->options[$key] = $call();
            }
        }
    }

    public function beginTransaction()
    {
        if ($this->transactionOpen === true) {
            return;
        }

        $this->database->beginTransaction();
        $this->transactionOpen = true;
    }

    public function endTransaction()
    {
        if ($this->transactionOpen === false) {
            return;
        }

        $this->database->commit();
        $this->transactionOpen = false;
    }

    private function prepareStatements()
    {
        $this->selectStatement = $this->database->prepare('SELECT `data` FROM '.$this->dbtbl().' WHERE `id` = :id');
        $this->insertStatement = $this->database->prepare('INSERT INTO '.$this->dbtbl().' (`id`, `expire_at`, `data`) VALUES (:id, :expire_at, :data)');
        $this->deleteStatement = $this->database->prepare('DELETE FROM '.$this->dbtbl().' WHERE id = :id');
        $this->updateStatement = $this->database->prepare('UPDATE '.$this->dbtbl().' SET `expire_at` = :expire_at, `data` = :data WHERE `id` = :id');
    }

    public function hasOpenTransaction(): bool
    {
        return $this->transactionOpen;
    }

    public function benchmark(int $count = 10)
    {
        $prefix = "mysql-benchmark-";
        $mysql = $this;
        $file = kirby()->cache('bnomei.mysql-cachedriver'); // neat, right? ;-)

        foreach (['mysql' => $mysql, 'file' => $file] as $label => $driver) {
            $time = microtime(true);
            if ($label === 'mysql') {
                $driver->beginTransaction();
            }
            for ($i = 0; $i < $count; $i++) {
                $key = $prefix . $i;
                if (!$driver->get($key)) {
                    $driver->set($key, Str::random(1000));
                }
            }
            for ($i = $count * 0.6; $i < $count * 0.8; $i++) {
                $key = $prefix . $i;
                $driver->remove($key);
            }
            for ($i = $count * 0.8; $i < $count; $i++) {
                $key = $prefix . $i;
                $driver->set($key, Str::random(1000));
            }
            if ($label === 'mysql') {
                $this->endTransaction();
            }
            echo $label . ' : ' . (microtime(true) - $time) . PHP_EOL;
        }

        // cleanup
        for ($i = 0; $i < $count; $i++) {
            $key = $prefix . $i;
            $driver->remove($key);
        }
    }
}
