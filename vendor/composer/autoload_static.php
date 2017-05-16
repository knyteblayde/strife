<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit713e105862dfd52d71a5066e67b91d37
{
    public static $files = array (
        '6755616f714c31195872c2fa0a71781c' => __DIR__ . '/../..' . '/kernel/helpers.php',
        '2a2cfef56b1c1960e799301d7a28cddf' => __DIR__ . '/../..' . '/app/routes.php',
        'c98ed9fdd7d7effbba53e0d310ce8174' => __DIR__ . '/../..' . '/config/database.php',
        '8106eed47376e4585e2de908346ea5da' => __DIR__ . '/../..' . '/config/application.php',
    );

    public static $classMap = array (
        'App\\Migrations\\UsersTableMigration' => __DIR__ . '/../..' . '/app/migrations/UsersTableMigration.php',
        'App\\Models\\City' => __DIR__ . '/../..' . '/app/models/City.php',
        'App\\Models\\Country' => __DIR__ . '/../..' . '/app/models/Country.php',
        'App\\Models\\Language' => __DIR__ . '/../..' . '/app/models/Language.php',
        'App\\Models\\User' => __DIR__ . '/../..' . '/app/models/User.php',
        'App\\Requests\\LoginRequest' => __DIR__ . '/../..' . '/app/requests/LoginRequest.php',
        'App\\Seeders\\UsersTableSeeder' => __DIR__ . '/../..' . '/app/seeders/UsersTableSeeder.php',
        'Auth' => __DIR__ . '/../..' . '/kernel/security/Auth.php',
        'Cookie' => __DIR__ . '/../..' . '/kernel/security/Cookie.php',
        'ErrorHandler' => __DIR__ . '/../..' . '/kernel/ErrorHandler.php',
        'Form' => __DIR__ . '/../..' . '/kernel/Form.php',
        'Kernel\\Database\\Connection' => __DIR__ . '/../..' . '/kernel/database/Connection.php',
        'Kernel\\Database\\Database' => __DIR__ . '/../..' . '/kernel/database/Database.php',
        'Kernel\\Database\\Migration' => __DIR__ . '/../..' . '/kernel/database/Migration.php',
        'Kernel\\Database\\QueryBuilder' => __DIR__ . '/../..' . '/kernel/database/QueryBuilder.php',
        'Kernel\\Database\\QueryBuilderInterface' => __DIR__ . '/../..' . '/kernel/database/QueryBuilder.php',
        'Kernel\\Database\\QueryBuilderMagicInterface' => __DIR__ . '/../..' . '/kernel/database/QueryBuilder.php',
        'Kernel\\Engine' => __DIR__ . '/../..' . '/kernel/Engine.php',
        'Kernel\\FileHandler' => __DIR__ . '/../..' . '/kernel/FileHandler.php',
        'Kernel\\Format' => __DIR__ . '/../..' . '/kernel/Format.php',
        'Kernel\\Log' => __DIR__ . '/../..' . '/kernel/Log.php',
        'Kernel\\Requests\\FileRequest' => __DIR__ . '/../..' . '/kernel/requests/FileRequest.php',
        'Kernel\\Requests\\FileRequestInterface' => __DIR__ . '/../..' . '/kernel/requests/FileRequest.php',
        'Kernel\\Requests\\HTTPRequest' => __DIR__ . '/../..' . '/kernel/requests/HTTPRequest.php',
        'Kernel\\Requests\\HTTPRequestInterface' => __DIR__ . '/../..' . '/kernel/requests/HTTPRequest.php',
        'Kernel\\Security\\Encryption' => __DIR__ . '/../..' . '/kernel/security/Encryption.php',
        'Kernel\\Security\\Hash' => __DIR__ . '/../..' . '/kernel/security/Hash.php',
        'Kernel\\Security\\Token' => __DIR__ . '/../..' . '/kernel/security/Token.php',
        'Kernel\\YamatoCLI' => __DIR__ . '/../..' . '/kernel/YamatoCLI.php',
        'LoginProcess' => __DIR__ . '/../..' . '/app/processes/LoginProcess.php',
        'Route' => __DIR__ . '/../..' . '/kernel/Route.php',
        'Session' => __DIR__ . '/../..' . '/kernel/security/Session.php',
        'View' => __DIR__ . '/../..' . '/kernel/View.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit713e105862dfd52d71a5066e67b91d37::$classMap;

        }, null, ClassLoader::class);
    }
}
