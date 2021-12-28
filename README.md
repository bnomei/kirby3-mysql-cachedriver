# Kirby3 MySQL Cache-Driver

![Release](https://flat.badgen.net/packagist/v/bnomei/kirby3-mysql-cachedriver?color=ae81ff)
![Downloads](https://flat.badgen.net/packagist/dt/bnomei/kirby3-mysql-cachedriver?color=272822)
[![Build Status](https://flat.badgen.net/travis/bnomei/kirby3-mysql-cachedriver)](https://travis-ci.com/bnomei/kirby3-mysql-cachedriver)
[![Coverage Status](https://flat.badgen.net/coveralls/c/github/bnomei/kirby3-mysql-cachedriver)](https://coveralls.io/github/bnomei/kirby3-mysql-cachedriver) 
[![Maintainability](https://flat.badgen.net/codeclimate/maintainability/bnomei/kirby3-mysql-cachedriver)](https://codeclimate.com/github/bnomei/kirby3-mysql-cachedriver) 
[![Twitter](https://flat.badgen.net/badge/twitter/bnomei?color=66d9ef)](https://twitter.com/bnomei)

Dolphin - a MySQL Cache Driver for Kirby 3

## Commerical Usage

> <br>
> <b>Support open source!</b><br><br>
> This plugin is free but if you use it in a commercial project please consider to sponsor me or make a donation.<br>
> If my work helped you to make some cash it seems fair to me that I might get a little reward as well, right?<br><br>
> Be kind. Share a little. Thanks.<br><br>
> &dash; Bruno<br>
> &nbsp; 

| M | O | N | E | Y |
|---|----|---|---|---|
| [Github sponsor](https://github.com/sponsors/bnomei) | [Patreon](https://patreon.com/bnomei) | [Buy Me a Coffee](https://buymeacoff.ee/bnomei) | [Paypal dontation](https://www.paypal.me/bnomei/15) | [Buy a Kirby license using this affiliate link](https://a.paddle.com/v2/click/1129/35731?link=1170) |

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
mysql : XXX
file : 0.11837792396545
```

> ATTENTION: This will create and remove a lot of cache files and sqlite entries

### No cache when debugging

When Kirbys global debug config is set to `true` the complete plugin cache will be flushed and no caches will be read. But entries will be created. This will make you live easier – trust me.

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

Use [Kirby 3 Boost](https://github.com/bnomei/kirby3-boost) to setup a cache for content files.


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
| host                      | `127.0.0.1`                | string or callback                                                         |
| dbname                    | `kirby3-mysql-cachedriver` | string or callback                                                         |
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
