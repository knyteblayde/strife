<?php
use Kernel\Token;

/**
 * Class Auth
 *
 * @package Kernel
 */
class Auth
{
    /**
     * Start Auth
     **/
    public function __construct()
    {
        return self::guard();
    }


    /**
     * Determines whether a user is authenticated
     * by checking keys if they are valid.
     *
     * @return mixed
     **/
    public function guard()
    {
        if (!isset($_SESSION['user'])) {
            return Route::redirect(route('login'));
        } else {
            if (!Token::verify(Session::user()->remember_token)) {
                return $this->restartSession();
            }
        }
    }


    /**
     * Check whether user is authenticated
     * if not, reset session and redirect
     * to login page.
     *
     * @return mixed
     **/
    public function restartSession()
    {
        return Session::destroy();
    }


}