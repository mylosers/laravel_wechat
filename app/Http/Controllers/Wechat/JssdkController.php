<?php

namespace App\Http\Controllers\Wechat;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use App\Http\Controllers\Wechat\WechatController;
use Illuminate\Support\Facades\Redis;

class JssdkController extends Controller
{
    /**
     * 测试
     */
    public function test(){
        $js_config=$this->jssdk();
        $data=[
            'js_config' =>$js_config
        ];
        return view('wechat.jssdk',$data);
    }

    /**
     * 获取jssdk数据
     */
    public function jssdk(){
        //获取access_token
        $access_token=$this->access_token();
        //计算签名
        $nonceStr=Str::random(10);
        $ticket=$this->ticket($access_token);
        $timestamp=time();
        $current_url=$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $img_url=$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
        /*echo 'nonceStr:'.$nonceStr;echo "</br>";
        echo 'ticket:'.$ticket;echo "</br>";
        echo 'timestamp:'.$timestamp;echo "</br>";
        echo 'current_url:'.$current_url;die;*/
        $string1="jsapi_ticket=$ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$current_url";
        $sign=sha1($string1);
//        echo 'signature:'.$sign;
        $js_config=[
            'appId' =>  env('WX_APPID'),
            'timestamp' =>$timestamp,
            'nonceStr' => $nonceStr,
            'signature'=> $sign,
            'url'=>$current_url,
            'img_url'=>$img_url,
        ];
        return $js_config;
    }

    /**
     * 获取access_token
     */
    public function access_token(){
        $wechat=new WechatController();
        $access_token=$wechat->access_token();
        return $access_token;
    }

    /**
     * 获取ticket
     */
    public function ticket($access_token){
        //请求ticket
        $key='wx_jssdk_ticket';
        $ticket_info=Redis::get($key);
        if($ticket_info){
            return $ticket_info;
        }else{
            $url="https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$access_token&type=jsapi";
            $ticket=json_decode(file_get_contents($url),true);
            if(isset($ticket['ticket'])){
                Redis::set($key,$ticket['ticket']);
                Redis::expire($key,3600);
                return $ticket['ticket'];
            }else{
                return false;
            }
        }
    }
    public function getImg(){
            echo '<pre>';print_r($_GET);echo '</pre>';
    }
}