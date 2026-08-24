<?php

/** @var \App\Core\Router $router */

$router->get('/', 'HomeController@index');
$router->get('/about', 'PageController@about');

$router->get('/register', 'AuthController@registerForm');
$router->post('/register', 'AuthController@register');
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// Attention à l'ordre : les routes fixes (/artists/create, /artists/quick-create...)
// doivent être déclarées AVANT le pattern générique /artists/{slug}, sinon
// "create" serait interprété comme un slug par le premier pattern qui matche.
$router->get('/artists', 'ArtistController@index');
$router->get('/artists/create', 'ArtistController@create');
$router->post('/artists/create', 'ArtistController@store');
$router->get('/artists/quick-create', 'ArtistController@quickCreateForm');
$router->post('/artists/quick-create/preview', 'ArtistController@quickCreatePreview');
$router->post('/artists/quick-create/store', 'ArtistController@quickCreateStore');
$router->get('/artists/{id}/edit', 'ArtistController@edit');
$router->post('/artists/{id}/edit', 'ArtistController@update');
$router->post('/artists/{id}/delete', 'ArtistController@delete');
$router->post('/artists/{id}/relations', 'ArtistController@addRelation');
$router->post('/artists/{id}/relations/{relationId}/delete', 'ArtistController@deleteRelation');
$router->post('/artists/{id}/links/bulk', 'ArtistController@addLinksBulk');
$router->post('/artists/{id}/links/{linkId}/delete', 'ArtistController@deleteLink');
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

$router->get('/lang/{lang}', 'LangController@switch');

$router->post('/reports', 'ReportController@store');

// Idem : routes fixes avant le pattern générique /playlists/{id}
$router->get('/playlists', 'PlaylistController@index');
$router->get('/playlists/mine', 'PlaylistController@mine');
$router->get('/playlists/create', 'PlaylistController@create');
$router->post('/playlists/create', 'PlaylistController@store');
$router->get('/playlists/{id}/edit', 'PlaylistController@edit');
$router->post('/playlists/{id}/edit', 'PlaylistController@update');
$router->post('/playlists/{id}/delete', 'PlaylistController@delete');
$router->post('/playlists/{id}/videos', 'PlaylistController@addVideo');
$router->post('/playlists/{id}/videos/{videoId}/remove', 'PlaylistController@removeVideo');
$router->post('/playlists/{id}/videos/{videoId}/toggle', 'PlaylistController@toggleVideo');
$router->get('/playlists/{id}', 'PlaylistController@show');
