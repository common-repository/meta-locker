<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb5202f5c19309d458155a8603a6c0bed
{
    public static $prefixLengthsPsr4 = array (
        'J' => 
        array (
            'Jaybizzle\\CrawlerDetect\\' => 24,
        ),
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Jaybizzle\\CrawlerDetect\\' => 
        array (
            0 => __DIR__ . '/..' . '/jaybizzle/crawler-detect/src',
        ),
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb5202f5c19309d458155a8603a6c0bed::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb5202f5c19309d458155a8603a6c0bed::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb5202f5c19309d458155a8603a6c0bed::$classMap;

        }, null, ClassLoader::class);
    }
}
