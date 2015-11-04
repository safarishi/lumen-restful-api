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

$app->group(['prefix' => 'v2', 'namespace' => 'App\Http\Controllers'], function ($app) {
    // 文章列表
    $app->get('articles', 'ArticleController@index');
    // 文章详情
    $app->get('articles/{id}', 'ArticleController@show');
    // 文章收藏
    $app->put('articles/{id}/stars', 'ArticleController@star');
    // 文章取消收藏
    $app->delete('articles/{id}/stars', 'ArticleController@unstar');
    // 用户注册
    $app->post('users', 'UserController@store');
    // 用户登录
    $app->post('oauth/access_token', 'OauthController@postAccessToken');
    // 修改用户信息
    $app->post('user', 'UserController@modify');
    // 文章评论
    $app->post('articles/{id}/comments', 'ArticleController@comment');
    // 文章评论列表
    $app->get('articles/{id}/comments', 'ArticleController@commentList');
    $app->post('articles/{id}/comments/{comment_id}/replies', 'ArticleController@reply');
    $app->put('articles/{id}/comments/{comment_id}/favours', 'ArticleController@favour');
    $app->delete('articles/{id}/comments/{comment_id}/favours', 'ArticleController@unfavour');
    // 用户退出登录
    $app->delete('oauth/invalidate_token', 'UserController@logout');
    // 获取当前用户的信息
    $app->get('user', 'UserController@show');
});
