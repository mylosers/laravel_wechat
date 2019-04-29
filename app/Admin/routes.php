<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('/wechat/user',UserController::class);
    $router->get('/wechat/access_token', 'UserController@access_token');
    $router->get('/wechat/MassAll', 'UserController@MassAll');
    $router->post('/wechat/MassAllAdd', 'UserController@MassAllAdd');
    $router->get('/wechat/snap', 'UserController@snap');
    $router->post('/wechat/upload', 'UserController@upload');
    $router->resource('/wechat/snapList',SnapController::class);
    $router->resource('/wechat/goods',GoodsController::class);



    //素材管理
    $router->get('/wx/media/add_img', 'WxController@addImg');

});
