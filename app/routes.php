<?php
use App\Models\User;


/**
 * List of route paths
 */
assign('homepage', '/', 'HomeController::index()');
assign('welcome', '/welcome', '/cms/WelcomeController::index()');

assign('test', '/test', function () {

    $usr = User::get();

    dump($usr);


});


/**
 * Authentication routes
 */
assign('login', '/login', 'AuthController::index()');
post('attempt', '/attempt', 'AuthController::attempt()');
assign('logout', '/logout', 'AuthController::logout()');
