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
Route::any('/index','Index\indexController@index');
Route::any('/goods','Cart\IndexController@index');
Route::any('/goodsList','Cart\IndexController@goodsList'); //商品列表
Route::any('/goodsSelect','Cart\IndexController@goodsSelect'); //商品搜索
Route::any('/goodsAdd','Cart\IndexController@goodsAdd');      //添加商品
Route::any('/number','Cart\IndexController@number');      //判断库存
Route::any('/goods/{goods_id}','Cart\IndexController@goods');      //添加商品
Route::any('/goodsDel/{goods_id}','Cart\IndexController@del');      //删除商品
Route::any('/numGoods','Cart\IndexController@numGoods');      //查询浏览记录
//订单
Route::any('/order','Cart\OrderController@orderList');           //订单列表
Route::any('/orderListAdd','Cart\OrderController@orderListAdd');           //订单列表展示
Route::any('/orderAdd','Cart\OrderController@add');           //下单


Route::get('/wechat/url','Wechat\WechatController@getEvent');   //接入
//Route::post('/wechat/url','Wechat\WechatController@wxEvent');   //接入
Route::post('/wechat/url','Wechat\KaoController@wxEvents');   //接入
Route::get('/wechat/access_token','Wechat\WechatController@access_token');   //获取access_token
Route::get('/wechat/CustomMenu','Wechat\WechatController@CustomMenu');
Route::get('/wechat/dlWxImg','Wechat\WechatController@dlWxImg');
Route::get('/wechat/Pay','Wechat\PayController@pay');
Route::get('/wechat/goods','Wechat\WechatController@goods');

Route::get('/jssdk/test','Wechat\JssdkController@test');
Route::get('/jssdk/getImg','Wechat\JssdkController@getImg');
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/wechat/code','Wechat\WechatController@code');
Route::get('/result','Wechat\WechatController@result');

Route::get('/wechat/qbcode','Wechat\WechatController@qbcode');
