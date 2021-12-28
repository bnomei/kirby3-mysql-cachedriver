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
        'host' => '127.0.0.1',
        'dbname' => 'kirby3-mysql-cachedriver',
        'username' => 'root',
        'password' => '',
        'port' => 3306,
],
    'cacheTypes' => [
        'sqlite' => \Bnomei\MySQLCache::class
    ],
]);
