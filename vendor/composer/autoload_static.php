<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit17d351b7220f613129bcb3dca106eb6f
{
    public static $prefixLengthsPsr4 = array (
        'd' => 
        array (
            'devmazon\\myorm\\' => 15,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'devmazon\\myorm\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit17d351b7220f613129bcb3dca106eb6f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit17d351b7220f613129bcb3dca106eb6f::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
