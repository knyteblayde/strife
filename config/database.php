<?php

/**
 * Database Configuration
 */

use Kernel\Database\Connection;

Connection::parameters([
    'driver'   => 'mysql',
    'hostname' => 'localhost',
    'database' => 'rocketflare',
    'username' => 'root',
    'password' => '',
    'charset'  => 'utf8',
    'port'     => 3306
]);


/**
 * Begin Transaction with database
 */
Connection::initialize();