<?php

/**
 * Strife
 * A Fast and Lightweight PHP MVC Framework
 *
 * Author:  Jeyz Strife
 * website: https://github.com/knyteblayde/strife
 * Date:    11/10/15
 */


/**
 * Require the composer autoloader
 */
require_once '../vendor/autoload.php';


/**
 * Boot up the front controller
 */
return new Kernel\Engine;
