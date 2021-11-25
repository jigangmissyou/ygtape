<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2bc4f313dba415539e266f7ac2c87dcd
{
    public static $prefixLengthsPsr4 = array (
        't' => 
        array (
            'think\\composer\\' => 15,
            'think\\' => 6,
        ),
        'a' => 
        array (
            'app\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'think\\composer\\' => 
        array (
            0 => __DIR__ . '/..' . '/topthink/think-installer/src',
        ),
        'think\\' => 
        array (
            0 => __DIR__ . '/../..' . '/thinkphp/library/think',
        ),
        'app\\' => 
        array (
            0 => __DIR__ . '/../..' . '/application',
        ),
    );

    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'PHPExcel' => 
            array (
                0 => __DIR__ . '/..' . '/phpoffice/phpexcel/Classes',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2bc4f313dba415539e266f7ac2c87dcd::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2bc4f313dba415539e266f7ac2c87dcd::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit2bc4f313dba415539e266f7ac2c87dcd::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit2bc4f313dba415539e266f7ac2c87dcd::$classMap;

        }, null, ClassLoader::class);
    }
}
