<?php

namespace App\Http\Controllers\Wechat;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;
use App\Model\WeixinUser;

class WechatController extends Controller
{
    protected $redis_weixin_access_token = 'str:weixin_access_token';     //微信 access_token
    /**
     * 首次接入
     */
    public function getEvent()
    {
        //$get = json_encode($_GET);
        //$str = '>>>>>' . date('Y-m-d H:i:s') .' '. $get . "<<<<<\n";
        //file_put_contents('logs/weixin.log',$str,FILE_APPEND);
        echo $_GET['echostr'];
    }

    /**
     * 接收微信服务器事件推送
     */
    public function wxEvent()
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
                    //获取用户信息
                    $user_info = $this->getUserInfo($FromUserName);
                    //保存用户信息
                    $u = WeixinUser::where(['FromUserName' => $FromUserName])->first();
                    if ($u) {       //用户不存在
                        //echo '用户已存在';
                        $xml_response = '<xml><ToUserName><![CDATA[' . $FromUserName . ']]></ToUserName><FromUserName><![CDATA[' . $ToUserName . ']]></FromUserName><CreateTime>' . time() . '</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[' . '欢迎回来'.$user_info['nickname'] .date('Y-m-d H:i:s') . ']]></Content></xml>';
                        echo $xml_response;
                    } else {
                        $user_data = [
                            'FromUserName' => $FromUserName,
                            'CreateTime' => time(),
                            'nickname' => $user_info['nickname'],
                            'sex' => $user_info['sex'],
                            'headimgurl' => $user_info['headimgurl'],
                            'subscribe_time' => $CreateTime,
                        ];

                        WeixinUser::insertGetId($user_data);      //保存用户信息
                        $xml_response = '<xml><ToUserName><![CDATA[' . $FromUserName . ']]></ToUserName><FromUserName><![CDATA[' . $ToUserName . ']]></FromUserName><CreateTime>' . time() . '</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[' . 'Hello'.$user_info['nickname'].', 欢迎关注' . date('Y-m-d H:i:s') . ']]></Content></xml>';
                        echo $xml_response;
                    }
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

    /**
     * 获取用户信息
     * @param $FromUserName
     * @return mixed
     */
    public function getUserInfo($FromUserName)
    {
        $access_token = $this->access_token();      //请求每一个接口必须有 access_token
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$FromUserName.'&lang=zh_CN';
        $data = json_decode(file_get_contents($url),true);
        echo '<pre>';print_r($data);echo '</pre>';
        return $data;
    }

    /**
     * 创建服务号菜单
     */
    public function CustomMenu(){
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->access_token();
        $client = new GuzzleHttp\Client(['base_uri' => $url]);
        $data = [
            "button"    => [
                [
                    "name" => "相册拍照",
                    "sub_button" => [
                        [
                            "type"  => "pic_sysphoto",      // view类型 跳转指定 URL
                            "name"  => "系统拍照发图",
                            "key"   => "rselfmenu_1_0",
                            "sub_button"=> [ ]
                        ],
                        [
                            "type" =>  "pic_photo_or_album",
                            "name" => "拍照或者相册发图",
                            "key" => "rselfmenu_1_1",
                            "sub_button" => [ ]
                        ],
                        [
                            "type" => "pic_weixin",
                            "name" => "微信相册发图",
                            "key" => "rselfmenu_1_2",
                            "sub_button" => [ ]
                        ]
                    ]
                ],
                [
                    "name" => "点击跳转",
                    "sub_button" => [
                        [
                            "name" => "发送位置",
                            "type" => "location_select",
                            "key" => "rselfmenu_2_0"
                        ],
                        [
                            "name" => "回复时间",
                            'type' => "click",
                            "key" => "kefu01"
                        ],
                        [
                            "name" => "哔哩哔哩",
                            'type' => "view",
                            "url" => "https://www.bilibili.com/"
                        ]
                    ]
                ],
                [
                    "name" => "扫码功能",
                    "sub_button" => [
                        [
                            "name" => "扫码带提示",
                            "type" => "scancode_waitmsg",
                            "key" => "rselfmenu_0_0",
                            "sub_button"=>[
                                'text'=>"text"
                            ]
                        ],
                        [
                            "name" => "扫码推事件",
                            "type" => "scancode_push",
                            "key" => "rselfmenu_0_1",
                            "sub_button"=>[]
                        ],
                    ]
                ]
            ]
        ];
        $r = $client->request('POST', $url, [
            'body' => json_encode($data,JSON_UNESCAPED_UNICODE)
        ]);
        // 3 解析微信接口返回信息
        $response_arr = json_decode($r->getBody(),true);
        if($response_arr['errcode'] == 0){
            echo "菜单创建成功";
        }else{
            echo "菜单创建失败，请重试";echo '</br>';
            echo $response_arr['errmsg'];
        }
    }

}