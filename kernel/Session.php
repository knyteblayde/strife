<?php

/**
 * Class Session
 */
class Session
{
    /**
     * make use of session variables
     *
     * @return mixed
     */
    public static function user()
    {
        return $_SESSION['user'];
    }


    /**
     * Get a session variable
     *
     * @param $name
     * @return mixed
     */
    public static function get($name)
    {
        return (array_key_exists($name, $_SESSION)) ? $_SESSION[$name] : '';
    }


    /**
     * Set a session variable
     *
     * @param $name
     * @param $value
     * @return void
     */
    public static function set($name, $value)
    {
        $_SESSION[$name] = $value;

        return true;
    }


    /**
     * Unset a session variable
     *
     * @param $name
     * @return void
     */
    public static function remove($name)
    {
        if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
        } else {
            return false;
        }
    }


    /**
     * Get the session flash message if defined
     * else return an empty string
     *
     * @param $name
     * @return string
     * @var string $flash
     */
    public static function getFlash($name)
    {
        if (isset($_SESSION['__FLASH__'][$name])) {
            $message = $_SESSION['__FLASH__'][$name];
            unset($_SESSION['__FLASH__'][$name]);
            return ($message);
        } else {
            return ("");
        }
    }


    /**
     * Destroy an existing flash message or
     * remove all flash messages if parameter is null
     *
     * @param $name
     * @return boolean
     */
    public static function unsetFlash($name = null)
    {
        if (is_null($name)) {
            if (isset($_SESSION['__FLASH__'])) {
                unset($_SESSION['__FLASH__']);
            }
        } elseif (isset($_SESSION['__FLASH__'][$name])) {
            unset($_SESSION['__FLASH__'][$name]);
        } else {
            return false;
        }
    }


    /**
     * Set a session flash message
     *
     * @param $name
     * @param $message
     * @return void
     */
    public static function setFlash($name, $message)
    {
        if ($_SESSION['__FLASH__'][$name] = $message) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Flush all sessions except session_id
     *
     * @return view
     */
    public static function destroy()
    {
        unset($_SESSION);
        session_destroy();
        session_regenerate_id();

        return header('location: ' . route('login'));
    }

}