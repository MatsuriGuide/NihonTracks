<?php

/** @var \App\Core\Router $router */

$router->get('/admin', 'Admin\DashboardController@index');
$router->get('/admin/tags', 'Admin\TagAdminController@index');

// À compléter au fur et à mesure :
// $router->get('/admin/reports', 'Admin\ReportController@index');
// $router->post('/admin/reports/{id}/resolve', 'Admin\ReportController@resolve');
// $router->get('/admin/users', 'Admin\UserAdminController@index');
