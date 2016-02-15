<?php

class WelcomeController extends Auth
{
    /**
     * Controller Index
     * @return view
     **/
    public function index()
    {
        $name = Session::user()->firstname;
        return print "Welcome {$name}! , <a href='/logout'>Logout</a>";
    }
}
