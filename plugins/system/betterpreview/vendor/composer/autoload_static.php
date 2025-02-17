<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit7819e16b548012bef7f4a33c465c36e2
{
    public static $prefixLengthsPsr4 = array (
        'R' => 
        array (
            'RegularLabs\\Plugin\\System\\BetterPreview\\' => 40,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'RegularLabs\\Plugin\\System\\BetterPreview\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit7819e16b548012bef7f4a33c465c36e2::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit7819e16b548012bef7f4a33c465c36e2::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit7819e16b548012bef7f4a33c465c36e2::$classMap;

        }, null, ClassLoader::class);
    }
}
