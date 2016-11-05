<?php

/**
 * Class Path
 */
class Path
{
    /**
     * @var array
     */
    static $paths = array();


    /**
     * Register paths list array
     *
     * @param $paths_array
     */

    static function register($paths_array)
    {
        self::$paths = $paths_array;
    }


    /**
     * Return directory name of the
     * parameter given
     *
     * @param $key
     * @return mixed
     */
    static function to($key)
    {
        return array_key_exists($key, self::$paths) ? self::$paths[$key] : null;
    }
}




