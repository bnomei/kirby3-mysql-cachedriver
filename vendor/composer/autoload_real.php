<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitdc41c86fe0a2f640e2f69efbd1bcc72b
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInitdc41c86fe0a2f640e2f69efbd1bcc72b', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitdc41c86fe0a2f640e2f69efbd1bcc72b', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitdc41c86fe0a2f640e2f69efbd1bcc72b::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
