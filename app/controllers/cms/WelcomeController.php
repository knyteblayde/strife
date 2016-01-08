<?php

class WelcomeController extends Auth
{
    /**
     * Controller Index
     * @return view
     **/
    public function index()
    {
        return print "Welcome! dawg, <a href='/logout'>Logout</a>";
    }
}
