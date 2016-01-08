<?php

/**
 * Helper Functions
 *
 * set of useful functions for performing
 * simple tasks.
 **/


if (!function_exists('dump')) {
    /**
     * Spit and Stop
     *
     * @param $variable
     * @param bool $die
     **/
    function dump($variable, $die = true)
    {
        echo '<code><pre>' . var_export($variable, TRUE) . '</pre></code>';

        if ($die) {
            die();
        }
    }
}


if (!function_exists('random_string')) {
    /**
     * Returns a randomized pseudo
     * string value.
     *
     * @param $length
     * @return string
     **/
    function random_string($length = 50)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $string = "";

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, (strlen($characters) - 1))];
        }

        return $string;
    }
}


if (!function_exists('download_file')) {
    /**
     * Forces a file to be downloaded
     * and not be open by default.
     *
     * @param $filename
     * @param $mime_type
     * @param $preferredFilename
     **/

    function download_file($filename, $mime_type, $preferredFilename = '')
    {
        $returnedName = (strlen($preferredFilename) > 0) ? $preferredFilename : pathinfo($filename, PATHINFO_FILENAME);

        header("Content-type: $mime_type");
        header("Content-disposition:attachment;filename=$returnedName");

        readfile($filename);
    }
}


if (!function_exists('file_contents_to_array')) {
    /**
     * Reads a text file and assigns every
     * line to an array
     *
     * @param $filename
     * @return array
     * */

    function file_contents_to_array($filename)
    {
        $file = fopen($filename, "r");
        $values = [];
        $counter = 0;

        while (!feof($file)) {
            $values[$counter] = fgets($file);
            $counter++;
        }

        return $values;
    }
}


if (!function_exists('route')) {
    /**
     * Alternative use of Route::get()
     * inherited the $routes array from
     * Routes class.
     *
     * @param $route_name
     * @return string
     **/

    function route($route_name)
    {
        return call_user_func_array(['Route', 'get'], func_get_args());
    }
}


if (!function_exists('view')) {
    /**
     * Alternative use of Route::get()
     * inherited the $routes array from
     * Routes class.
     *
     * @param $template
     * @param $params
     * @return string
     **/

    function render($template, $params = [])
    {
        return View::render($template, $params);
    }
}


if (!function_exists('extend')) {
    /**
     * Extend or include a layout by giving the
     * layout folder name as first parameter.
     *
     * @param $template
     * @param $params
     * @return string
     **/

    function extend($template, $params = [])
    {
        return View::extend($template, $params);
    }
}


if (!function_exists('endExtend')) {
    /**
     * End an extended layout
     *
     * @param $params
     * @return string
     **/

    function endExtend($params = [])
    {
        return View::endExtend($params);
    }
}


if (!function_exists('parse')) {
    /**
     * Include a file and render content
     *
     * @param $template
     * @return string
     **/

    function parse($template)
    {
        return View::parse($template);
    }
}


if (!function_exists('get_filename')) {
    /**
     * Returns a string(without extension)
     * of a given string variable
     *
     * @param $string_var
     * @return string
     **/

    function get_filename($string_var)
    {
        return pathinfo($string_var, PATHINFO_FILENAME);
    }
}


if (!function_exists('get_extension')) {
    /**
     * Returns an extension of a
     * given string variable
     *
     * @param $string_var
     * @return string
     **/

    function get_extension($string_var)
    {
        return pathinfo($string_var, PATHINFO_EXTENSION);
    }
}


if (!function_exists('css')) {
    /**
     * Shortcut to adding css files
     * multiple arguments are handled recursively
     *
     * @param $path
     * @return string
     **/

    function css($path)
    {
        $styles = "";
        if (count(func_get_args() > 1)) {
            foreach (func_get_args() as $arg) {
                $styles .= "<link href=\"$arg\" rel=\"stylesheet\" type=\"text/css\">\n";
            }
        } else {
            $styles = "<link href=\"$path\" rel=\"stylesheet\" type=\"text/css\">\n";
        }

        return ($styles);
    }
}


if (!function_exists('js')) {
    /**
     * Shortcut to adding JS files
     * multiple arguments are handled recursively
     *
     * @param $path
     * @return string
     **/

    function js($path)
    {
        $scripts = "";
        if (count(func_get_args() > 1)) {
            foreach (func_get_args() as $arg) {
                $scripts .= "<script type=\"text/javascript\" src=\"$arg\"></script>\n";
            }
        } else {
            $scripts = "<script type=\"text/javascript\" src=\"$path\"></script>\n";
        }
        return ($scripts);
    }
}


if (!function_exists('date_difference')) {
    /**
     * Compares two days and returns
     * number of days.
     *
     * @param $date1
     * @param $date2
     * @return string
     */
    function date_difference($date1, $date2)
    {
        $date_entry = new \DateTime($date1);
        $dateNow = new \DateTime($date2);

        return $dateNow->diff($date_entry)->format("%a");
    }
}


if (!function_exists('errors')) {
    /**
     * Return the errors session
     * values
     *
     * @param $errorName
     * @return string
     */
    function errors($errorName)
    {
        if (isset($_SESSION['__ERRORS__'][$errorName])) {
            return $_SESSION['__ERRORS__'][$errorName];
        } else {
            return '';
        }
    }
}


if (!function_exists('fields')) {
    /**
     * Return the last form values returned
     * from request class
     *
     * @param $name
     * @return string
     */
    function fields($name)
    {
        if (isset($_SESSION['__FIELDS__'][$name])) {
            return $_SESSION['__FIELDS__'][$name];
        } else {
            return '';
        }
    }
}


if (!function_exists('date_now')) {
    /**
     * Return current date
     *
     * @return string
     */
    function date_now()
    {
        return Date('F j, Y');
    }
}


if (!function_exists('time_now')) {
    /**
     * Return current time
     *
     * @return string
     */
    function time_now()
    {
        return Date('h:i A');
    }
}


if (!function_exists('year_now')) {
    /**
     * Return current year
     *
     * @return string
     */
    function year_now()
    {
        return Date('Y');
    }
}


use Kernel\Database\Database;
use Kernel\Hash;

if (!function_exists('query')) {

    /**
     * Performs query and returns object
     *
     * @param $string
     * @return mixed
     */
    function query($string)
    {
        $db = new Database;

        return $db->query($string);
    }
}

if (!function_exists('hash_encode')) {

    /**
     * Hash the string
     *
     * @param $string
     * @return mixed
     */
    function hash_encode($string)
    {
        return Hash::encode($string);
    }
}


if (!function_exists('hash_verify')) {

    /**
     * Compare string to hashed string
     *
     * @param $string
     * @param $hashed
     * @return mixed
     */
    function hash_verify($string, $hashed)
    {
        return Hash::verify($string, $hashed);
    }
}

if (!function_exists('assign')) {

    /**
     * Assign a route config.
     * same as Route::assign() method.
     *
     * @param string $name
     * @param string $url
     * @param string $action
     * @param null $namespace
     * @return mixed
     */
    function assign($name, $url, $action, $namespace = null)
    {
        return Route::assign($name, $url, $action, $namespace);
    }
}

if (!function_exists('setflash')) {

    /**
     * Create a session flash message
     *
     * @param string $name
     * @param string $message
     * @return mixed
     */
    function setflash($name, $message)
    {
        return Session::setFlash($name, $message);
    }
}

if (!function_exists('getflash')) {

    /**
     * Get and destroy a flash messsage
     *
     * @param string $name
     * @return mixed
     */
    function getflash($name)
    {
        return Session::getFlash($name);
    }
}

if (!function_exists('hostname')) {

    /**
     * Return current host's name
     * @return mixed
     */
    function hostname()
    {
        return $_SERVER['HTTP_HOST'];
    }
}

if (!function_exists('referer')) {

    /**
     * Return current referer name
     * @return mixed
     */
    function referer()
    {
        return $_SERVER['HTTP_REFERER'];
    }
}


/**
 * Global Directory names.
 * directory pointer positioned from /public/index.php
 */
define("APP_PATH", "../app/");
define("CONTROLLERS_PATH", "../app/controllers/");
define("MIGRATIONS_PATH", "../app/migrations/");
define("MODELS_PATH", "../app/models/");
define("PROCESSES_PATH", "../app/processes/");
define("REQUESTS_PATH", "../app/requests/");
define("CONFIG_PATH", "../app/requests/");
define("KERNEL_PATH", "../kernel/");
define("PUBLIC_PATH", "../public/");
define("STORAGE_PATH", "../storage/");
define("VENDOR_PATH", "../vendor/");
define("VIEWS_PATH", "../views/");
