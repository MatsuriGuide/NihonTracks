<?php

/** @var \App\Core\Router $router */

$router->get('/admin', 'Admin\DashboardController@index');
$router->get('/admin/tags', 'Admin\TagAdminController@index');
$router->post('/admin/tags', 'Admin\TagAdminController@store');
$router->post('/admin/tags/{id}/delete', 'Admin\TagAdminController@delete');
$router->get('/admin/reports', 'Admin\ReportController@index');
$router->post('/admin/reports/{id}/resolve', 'Admin\ReportController@resolve');
$router->post('/admin/translate/artist/{id}/{lang}', 'Admin\TranslationController@translateArtist');
$router->post('/admin/translate/video/{id}/{lang}', 'Admin\TranslationController@translateVideo');

// À compléter au fur et à mesure :
// $router->get('/admin/users', 'Admin\UserAdminController@index');
