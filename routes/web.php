<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
/*
我们使用路由来定义 URL 和 URL 的请求方式，再将该 URL 分配到相对应的控制器动作中进行处理
get 表明这个路由将会响应 GET 请求，并将请求映射到指定的控制器动作上home方法*/
Route::get('/', 'StaticPagesController@home')->name('home');
Route::get('/help', 'StaticPagesController@help')->name('help');
Route::get('/about', 'StaticPagesController@about')->name('about');
Route::get('/signup', 'UsersController@create')->name('signup');//注册
/*
Route::get('/users', 'UsersController@index')->name('users.index');用户列表
Route::get('/users/create', 'UsersController@create')->name('users.create');创建用户页面
Route::get('/users/{user}', 'UsersController@show')->name('users.show');用户个人信息
Route::post('/users', 'UsersController@store')->name('users.store');创建用户
Route::get('/users/{user}/edit', 'UsersController@edit')->name('users.edit');编辑用户个人资料
Route::patch('/users/{user}', 'UsersController@update')->name('users.update');更新用户
Route::delete('/users/{user}', 'UsersController@destroy')->name('users.destroy');删除用户
*/
/*新增的 resource 方法将遵从 RESTful 架构为用户资源生成路由。该方法接收两个参数，第一个参数为资源名称，第二个参数为控制器名称*/
Route::resource('users', 'UsersController');
Route::get('login', 'SessionsController@create')->name('login');
Route::post('login', 'SessionsController@store')->name('login');
Route::delete('logout', 'SessionsController@destroy')->name('logout');

Route::get('signup/confirm/{token}', 'UsersController@confirmEmail')->name('confirm_email');

//显示重置密码的邮箱发送页面
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
//邮箱发送重设链接
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
//密码更新页面
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
//执行密码更新操作
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');
//处理创建微博的请求和删除
Route::resource('statuses', 'StatusesController', ['only' => ['store', 'destroy']]);

//显示用户的关注人列表
Route::get('/users/{user}/followings', 'UsersController@followings')->name('users.followings');
//显示用户的粉丝列表
Route::get('/users/{user}/followers', 'UsersController@followers')->name('users.followers');
//关注用户
Route::post('/users/followers/{user}', 'FollowersController@store')->name('followers.store');
//取消关注用户
Route::delete('/users/followers/{user}', 'FollowersController@destroy')->name('followers.destroy');