<?php

/**
 * Database Configuration
 */

use Kernel\Database\Connection;


/**
 * Here you can specify multiple database
 * connections by giving an alias.
 *
 * first param is the alias for the connection instance
 * second param is an array of connection parameters
 */
Connection::parameters('conn1',[
    'driver'   => 'mysql',
    'hostname' => 'localhost',
    'database' => 'strife',
    'username' => 'root',
    'password' => '',
    'charset'  => 'utf8',
    'port'     => 3306
]);


/**
 * Begin Transaction with database
 */
//Connection::initialize('conn1');