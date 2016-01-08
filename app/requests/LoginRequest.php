<?php namespace App\Requests;

use Kernel\Request;

class LoginRequest extends Request
{

    /**
     * This is the route that will be used
     * to redirect when errors are present
     */
    protected $route = '/';


    /**
     * Rules to be followed by request
     */
    protected $rules = [
        'username' => 'required',
        'password' => 'required'
    ];

}