<?php

/** @var \App\Core\Router $router */

$router->get('/', 'HomeController@index');

$router->get('/register', 'AuthController@registerForm');
$router->post('/register', 'AuthController@register');
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// Attention à l'ordre : les routes fixes (/artists/create) doivent être
// déclarées AVANT le pattern générique /artists/{slug}, sinon "create"
// serait interprété comme un slug par le premier pattern qui matche.
$router->get('/artists', 'ArtistController@index');
$router->get('/artists/create', 'ArtistController@create');
$router->post('/artists/create', 'ArtistController@store');
$router->get('/artists/{id}/edit', 'ArtistController@edit');
$router->post('/artists/{id}/edit', 'ArtistController@update');
$router->post('/artists/{id}/delete', 'ArtistController@delete');
$router->get('/artists/{slug}', 'ArtistController@show');

// À compléter au fur et à mesure :
// $router->get('/videos', 'VideoController@index');
// $router->get('/videos/add', 'VideoController@create');
// $router->post('/videos/add', 'VideoController@store');
