<?php

/**
 * This is your project's main configuration file.
 */


/**
 * Define the name for your app here, this will be used for
 * template titles.
 */
define('APP_NAME', 'Strife App');



/**
 * This is an application key that will
 * be used for security purposes.
 *
 * Change this key before
 * deployment of this system.
 */
define('APPLICATION_KEY', '9ar4jto6mh5cy7xpyyp59bczar3uac4cl8r1hhg70395zroulf');



/**
 * You can quickly switch to Maintenance mode.
 */
define('MAINTENANCE_MODE', FALSE);



/**
 * Directory where to save session files instead of server's
 * default dir
 */
session_save_path('../storage/sessions/');