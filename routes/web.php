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

Route::get('/', function () {
    return view('welcome');
});



Route::get('/wechat/url','Wechat\WechatController@getEvent');   //接入
Route::post('/wechat/url','Wechat\WechatController@wxEvent');   //接入
Route::get('/wechat/access_token','Wechat\WechatController@access_token');   //获取access_token
Route::get('/wechat/CustomMenu','Wechat\WechatController@CustomMenu');
Route::get('/wechat/dlWxImg','Wechat\WechatController@dlWxImg');
Route::get('/wechat/Pay','Wechat\PayController@pay');
Route::get('/wechat/goods','Wechat\WechatController@goods');

Route::get('/jssdk/test','Wechat\JssdkController@test');