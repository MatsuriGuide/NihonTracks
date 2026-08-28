<?php

/** @var \App\Core\Router $router */

$router->get('/admin', 'Admin\DashboardController@index');
$router->get('/admin/diagnostic/openai', 'Admin\DiagnosticController@openai');
$router->get('/admin/tags', 'Admin\TagAdminController@index');
$router->post('/admin/tags', 'Admin\TagAdminController@store');
$router->post('/admin/tags/{id}/delete', 'Admin\TagAdminController@delete');
$router->get('/admin/reports', 'Admin\ReportController@index');
$router->post('/admin/reports/{id}/resolve', 'Admin\ReportController@resolve');
$router->post('/admin/translate/artist/{id}/{lang}', 'Admin\TranslationController@translateArtist');
$router->post('/admin/translate/video/{id}/{lang}', 'Admin\TranslationController@translateVideo');
$router->get('/admin/suggestions', 'Admin\SuggestionController@index');
$router->get('/admin/suggestions/{id}/publish', 'Admin\SuggestionController@publish');
$router->post('/admin/suggestions/{id}/dismiss', 'Admin\SuggestionController@dismiss');
$router->get('/admin/watch', 'Admin\WatchController@index');
$router->post('/admin/watch/{linkId}/scan', 'Admin\WatchController@scanOne');
$router->get('/admin/artist-approvals', 'Admin\ArtistApprovalController@index');
$router->post('/admin/artist-approvals/{id}/approve', 'Admin\ArtistApprovalController@approve');
$router->post('/admin/artist-approvals/{id}/reject', 'Admin\ArtistApprovalController@reject');
$router->get('/admin/incomplete-artists', 'Admin\ArtistCompletionController@index');

// À compléter au fur et à mesure :
// $router->get('/admin/users', 'Admin\UserAdminController@index');
