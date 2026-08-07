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

// Idem : les routes fixes doivent précéder le pattern générique /videos/{id}
$router->get('/videos', 'VideoController@index');
$router->get('/videos/create', 'VideoController@create');
$router->post('/videos/preview', 'VideoController@preview');
$router->post('/videos/store', 'VideoController@store');
$router->get('/videos/{id}/edit', 'VideoController@edit');
$router->post('/videos/{id}/edit', 'VideoController@update');
$router->post('/videos/{id}/delete', 'VideoController@delete');
$router->get('/videos/{id}', 'VideoController@show');
