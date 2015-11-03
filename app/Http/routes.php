<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->welcome();
});

$app->get('v2/reports', 'ArticleController@report');

$app->group(['prefix' => 'v2'], function ($app) {
    // 文章列表
    $app->get('articles', 'App\Http\Controllers\ArticleController@index');
    // 文章详情
    $app->get('articles/{id}', 'App\Http\Controllers\ArticleController@show');
});
