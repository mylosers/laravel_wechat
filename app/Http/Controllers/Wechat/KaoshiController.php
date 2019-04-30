<?php

namespace App\Http\Controllers\Wechat;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;
use App\Model\GoodsModel;


class KaoshiController extends Controller
{
    protected $redis_weixin_access_token = 'str:weixin_access_token';     //微信 access_token

    /**
     * 接收微信服务器事件推送
     */
    public function wxEvents()
    {
        $data = file_get_contents("php://input");
        //解析XML
        $xml = simplexml_load_string($data);        //将 xml字符串 转换成对象
        //记录日志
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
        file_put_contents('logs/wx_event.log', $log_str, FILE_APPEND);
        $ToUserName = $xml->ToUserName;         //开发者微信号
        $FromUserName = $xml->FromUserName;     //发送方帐号  用户openid
        $CreateTime = $xml->CreateTime;         //消息创建时间
        $MsgType = $xml->MsgType;               //消息类型，
        $Content = $xml->Content;               //文本消息内容
        $event = $xml->Event;
        if (isset($xml->MsgType)) {
            if ($MsgType == 'event') {           //判断事件类型
                if ($event == 'subscribe') {    //扫码关注事件
                        $xml_response = '<xml><ToUserName><![CDATA[' . $FromUserName . ']]></ToUserName><FromUserName><![CDATA[' . $ToUserName . ']]></FromUserName><CreateTime>' . time() . '</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[请输入商品+商品名字字样' . date('Y-m-d H:i:s') . ']]></Content></xml>';
                        echo $xml_response;
                    }
            }else if($xml->MsgType=='text'){        //用户发送文本消息
                if(strpos($xml->Content,"商品+")!==false){
                    $arr=explode('+',$xml->Content);
                    $text=$arr[1];
                    $goods=GoodsModel::where(['goods_name'=>$text])->first();
                    if($goods){
                        $goods=$goods->toArray();
                    }else{
                        $goods="";
                    }
                    if($goods){
                        $current_url=$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
                        $url_goods=$current_url.'/goods/'.$goods['goods_id'];
                        $url='https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$this->access_token();
                        $datas=[
                            "touser"=>"$FromUserName",
                            "template_id"=>"Av_-FByzbsbpzWUzStK6V0IOR2RFS0wNzz5IJrZghpM",
                            "url"=>"$url_goods",
                            "data"=>[
                                "first" => [
                                    "value" =>"恭喜你购买成功！",
                                    "color" =>"#173177"
                                ]
                            ]
                        ];
                        $client = new GuzzleHttp\Client(['base_uri' => $url]);
                        $r = $client->request('POST', $url, [
                            'body' => json_encode($datas,JSON_UNESCAPED_UNICODE)
                        ]);
                        // 3 解析微信接口返回信息
                        $response_arr = json_decode($r->getBody(),true);
                        print_r($response_arr);
                    }
                    //使用redis缓存
                    $redis=new \Redis();
                    $redis->connect('127.0.0.1',6379);
                    $key="key";
                    $val=$text;
                    $re1=$redis->set($key,$val);
                }
            }
        }
    }

    /**
     * 缓存access_token
     */
    public function access_token(){
        //获取缓存
        $token = Redis::get($this->redis_weixin_access_token);
        if(!$token){        // 无缓存 请求微信接口
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_APPSECRET');
            $data = json_decode(file_get_contents($url),true);
            //记录缓存
            $token = $data['access_token'];
            Redis::set($this->redis_weixin_access_token,$token);
            Redis::setTimeout($this->redis_weixin_access_token,3600);
        }
        return $token;
    }
}