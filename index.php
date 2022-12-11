<?php

@include_once __DIR__ . '/vendor/autoload.php';

if (!class_exists('Bnomei\MySQLCache')) {
    require_once __DIR__ . '/classes/MySQLCache.php';
}

if (! function_exists('dolphin')) {
    function dolphin(array $options = [])
    {
        return \Bnomei\MySQLCache::singleton($options);
    }
}

Kirby::plugin('bnomei/mysql-cachedriver', [
    'options' => [
        'cache' => true, // create cache folder
        'store' => true, // php memory cache
        'store-ignore' => '', // if contains then ignore
        'transaction' => [
            'limit' => 4096, // exec transaction after n SET commands
        ],
        'host' => '127.0.0.1',
        'unix_socket' => null,
        'port' => 3306,
        'dbname' => 'kirby3-mysql-cachedriver',
        'tablename' => 'kirby3-mysql-cachedriver',
        'username' => 'root',
        'password' => '',
],
    'cacheTypes' => [
        'mysql' => \Bnomei\MySQLCache::class
    ],
]);
