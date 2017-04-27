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
 * You can use any kind of file type
 * for template files
 */
define('TEMPLATE_TYPE', '.php');



/**
 * This is an application key that will
 * be used for security purposes.
 *
 * Change this key before
 * deployment of this system.
 */
define('APPLICATION_KEY', 'ds9o1xikuloa72olrqggqldj11ka9e9hxusnunow996rpndlyl');



/**
 * You can quickly switch to Maintenance mode.
 */
define('MAINTENANCE_MODE', FALSE);


/**
 * Allow blocking for IPs.
 */
define('IP_BLACKLISTING', FALSE);



/**
 * Directory where to save session files instead of server's
 * default dir
 */
session_save_path('../storage/sessions/');