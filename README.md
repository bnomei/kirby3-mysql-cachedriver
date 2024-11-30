# 🐬 Kirby MySQL Cache-Driver

![Release](https://flat.badgen.net/packagist/v/bnomei/kirby3-mysql-cachedriver?color=ae81ff&icon=github&label)
[![Discord](https://flat.badgen.net/badge/discord/bnomei?color=7289da&icon=discord&label)](https://discordapp.com/users/bnomei)
[![Buymecoffee](https://flat.badgen.net/badge/icon/donate?icon=buymeacoffee&color=FF813F&label)](https://www.buymeacoffee.com/bnomei)

Dolphin - a MySQL Cache Driver for Kirby

## Installation

- unzip [master.zip](https://github.com/bnomei/kirby3-mysql-cachedriver/archive/master.zip) as folder `site/plugins/kirby3-mysql-cachedriver` or
- `git submodule add https://github.com/bnomei/kirby3-mysql-cachedriver.git site/plugins/kirby3-mysql-cachedriver` or
- `composer require bnomei/kirby3-mysql-cachedriver`

## Usage 

### Cache methods

```php
$cache = \Bnomei\MySQLCache::singleton(); // or
$cache = dolphin();

$cache->set('key', 'value', $expireInMinutes);
$value = dolphin()->get('key', $default);

dolphin()->remove('key');
dolphin()->flush();
```

### Benchmark

```php
dolphin()->benchmark(1000);
```

```shell script
mysql : 0.03287492121384
file : 0.11837792396545
```

> ATTENTION: This will create and remove a lot of cache files and SQLite entries

### No cache when debugging

When Kirbys global debug config is set to `true` the complete plugin cache will be flushed and no caches will be read. But entries will be created.

### How to use MySQL Cache with Lapse or Boost

You need to set the cache driver for the [lapse plugin](https://github.com/bnomei/kirby3-lapse) to `mysql`.

**site/config/config.php**
```php
<?php
return [
    'bnomei.lapse.cache' => ['type' => 'mysql'],
    'bnomei.boost.cache' => ['type' => 'mysql'],
    //... other options
];
```

### Setup Content-File Cache

Use [Kirby Boost](https://github.com/bnomei/kirby3-boost) to setup a cache for content files.


## Settings

You can set the host, dbname, username, password,... in the config.

**site/config/config.php**
```php
return [
    // other config settings ...
    'bnomei.mysql-cachedriver.dbname' => 'YOUR-DBNAME-HERE',
    'bnomei.mysql-cachedriver.username' => 'YOUR-USERNAME-HERE',
    'bnomei.mysql-cachedriver.password' => 'YOUR-PASSWORD-HERE',
];
```

You can also set a callback if you use the [dotenv Plugin](https://github.com/bnomei/kirby3-dotenv).

**site/config/config.php**
```php
return [
    // other config settings ...
    'bnomei.mysql-cachedriver.dbname' => function() { return env('MYSQL_DBNAME'); },
    'bnomei.mysql-cachedriver.username' => function() { return env('MYSQL_USERNAME'); },
    'bnomei.mysql-cachedriver.password' => function() { return env('MYSQL_PASSWORD'); },
];
```

| bnomei.mysql-cachedriver. | Default                    | Description                                                                |            
|---------------------------|----------------------------|----------------------------------------------------------------------------|
| store                     | `true`                     | keep accessed cache items stored in PHP memory for faster recurring access |
| store-ignore              | ``                         | if key contains that string then ignore                                    |
| transaction.limit         | `4096`                     | exec transaction after n SET operations                                    |
| host                      | `127.0.0.1`                | string or callback                                                         |
| unix_socket               | `null`                     | string or callback                                                         |
| dbname                    | `kirby3-mysql-cachedriver` | string or callback (will be created if it does not exists)                 |
| tablename                 | `kirby3-mysql-cachedriver` | string or callback (will be created if it does not exists)                 |
| username                  | `root`                     | string or callback                                                         |
| password                  | ``                         | string or callback                                                         |
| port                      | `3306`                     | int or callback                                                            |

## Dependencies

- PHP MySQL extension

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/bnomei/kirby3-mysql-cachedriver/issues/new).

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.
