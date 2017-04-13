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
define('APPLICATION_KEY', '8dsp0mvsekyf4mzjuu4deu8xvsc63bekjnihrh7o44sesl5sez');



/**
 * You can quickly switch to Maintenance mode.
 */
define('MAINTENANCE_MODE', FALSE);



/**
 * Directory where to save session files instead of server's
 * default dir
 */
session_save_path('../storage/sessions/');