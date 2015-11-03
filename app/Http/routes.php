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
    // 文章收藏
    $app->put('articles/{id}/stars', 'App\Http\Controllers\ArticleController@star');
    // 用户注册
    $app->post('users', 'App\Http\Controllers\UserController@store');
    // 用户登录
    $app->post('oauth/access_token', 'App\Http\Controllers\OauthController@postAccessToken');
});
