<?php

/**
 * List of route paths
 */
assign('homepage', '/', 'HomeController::index()');
assign('welcome', '/welcome', '/cms/WelcomeController::index()');
assign('closure', '/closure', function () {
    return print "This is a function closure";
});


/**
 * Authentication routes
 */
assign('login', '/login', 'AuthController::index()');
assign('attempt', '/attempt', 'AuthController::attempt()');
assign('logout', '/logout', 'AuthController::logout()');