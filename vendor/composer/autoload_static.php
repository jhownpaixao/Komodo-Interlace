<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0bf01fa1aa03aad862e799ae26538aab
{
    public static $prefixLengthsPsr4 = array (
        'K' => 
        array (
            'Komodo\\Logger\\' => 14,
            'Komodo\\Interlace\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Komodo\\Logger\\' => 
        array (
            0 => __DIR__ . '/..' . '/komodo/logger/src',
        ),
        'Komodo\\Interlace\\' => 
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
            $loader->prefixLengthsPsr4 = ComposerStaticInit0bf01fa1aa03aad862e799ae26538aab::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0bf01fa1aa03aad862e799ae26538aab::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit0bf01fa1aa03aad862e799ae26538aab::$classMap;

        }, null, ClassLoader::class);
    }
}
