<?php

/** @var \App\Core\Router $router */

$router->get('/', 'HomeController@index');

$router->get('/register', 'AuthController@registerForm');
$router->post('/register', 'AuthController@register');
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// À compléter au fur et à mesure :
// $router->get('/artists', 'ArtistController@index');
// $router->get('/artists/{slug}', 'ArtistController@show');
// $router->get('/videos', 'VideoController@index');
// $router->get('/videos/add', 'VideoController@create');
// $router->post('/videos/add', 'VideoController@store');
