<?php

/**
 * List of route paths
 */
assign('homepage', '/', 'HomeController::index()');
assign('welcome', '/welcome', '/cms/WelcomeController::index()');

/**
 * Authentication routes
 */
assign('login', '/login', 'AuthController::index()');
post('attempt', '/attempt', 'AuthController::attempt()');
assign('logout', '/logout', 'AuthController::logout()');