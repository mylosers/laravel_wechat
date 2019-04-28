<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use App\Http\Controllers\Wechat\WechatController;
use GuzzleHttp;

class WxController extends Controller
{

    public function addImg(Content $content)
    {

        $wechat = new WechatController();
        $access_token = $wechat->access_token();
        //echo $access_token;
        $url = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$access_token.'&type=image';
        $client = new GuzzleHttp\Client();
        //var_dump($client);
        $response = $client->request('post',$url,[
           'multipart' => [
               [
                   'name' => 'media',
                   'contents' => fopen('image/goods.jpg', 'r'),
               ]
           ]
        ]);

        echo $response->getBody();

        /*return $content
            ->header('Index')
            ->description('description')
            ->body( view('admin.weixin.add_img') );*/
    }
}
