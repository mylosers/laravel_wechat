<?php

namespace App\Http\Controllers\Wechat;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;
use App\Model\WeixinUser;
use App\Model\WeixinChatModel;
use App\Model\WeixinMedia;
use App\Model\OrderModel;



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
            }else if($xml->MsgType=='text'){            //用户发送文本消息
                if(strpos($xml->Content,"+天气")!==false){
                    $arr=explode('+',$xml->Content);
                    $text=$arr[0];
                    $url="https://www.tianqiapi.com/api/?version=v1&city=$text";
                    $obj=file_get_contents($url);
                    $array=json_decode($obj,true);
                    $city=$array['city'];//城市
                    $day=$array['data'][0]['day']; //日期
                    $date=$array['data'][0]['date']; //具体日期
                    $week=$array['data'][0]['week']; //周几
                    $wea=$array['data'][0]['wea']; //天气情况
                    $air=$array['data'][0]['air']; //空气质量
                    $humidity=$array['data'][0]['humidity']; //湿度
                    $air_level=$array['data'][0]['air_level']; //空气质量等级
                    $air_tips=$array['data'][0]['air_tips']; //空气质量描述
                    $tem1=$array['data'][0]['tem1']; //白天高温温度
                    $tem2=$array['data'][0]['tem2']; //晚上低温温度
                    $tem=$array['data'][0]['tem']; //当前温度
                    $win=$array['data'][0]['win'][1]; //风向
                    $win_speed=$array['data'][0]['win_speed']; //风向
                    $str="城市：$city \n 日期：$day \n 具体日期：$date \n 周：$week \n 天气：$wea \n 空气质量：$air \n 湿度：$humidity \n 空气质量等级：$air_level \n 空气质量描述：$air_tips \n 高温白天温度：$tem1 \n 低温晚上温度：$tem2 \n 当前温度：$tem \n 风向：$win \n 风向：$win_speed";
                    $xml_response = '<xml><ToUserName><![CDATA[' . $FromUserName . ']]></ToUserName><FromUserName><![CDATA[' . $ToUserName . ']]></FromUserName><CreateTime>' . time() . '</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['.$str.']]></Content></xml>';
                    echo $xml_response;
                }else{
                    //记录聊天消息
                    $data = [
                        'text'       => $xml->Content,
                        'msgid'     => $xml->MsgId,
                        'open_id'    => $FromUserName,
                        'ctime' =>time(),
                        'type'  => 1        // 1用户发送消息 2客服发送消息
                    ];
                    $id = WeixinChatModel::insertGetId($data);
                    if($id){
                        $xml_response = '<xml><ToUserName><![CDATA['.$FromUserName.']]></ToUserName><FromUserName><![CDATA['.$xml->ToUserName.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['. $xml->Content. date('Y-m-d H:i:s') .']]></Content></xml>';
                        echo $xml_response;
                    }
                }
            }elseif($xml->MsgType=='image'){       //用户发送图片信息
                //视业务需求是否需要下载保存图片
                    $file_name = $this->dlWxImg($xml->MediaId);
                    //写入数据库
                    $data = [
                        'openid'    => $FromUserName,
                        'add_time'  => time(),
                        'msg_type'  => 'image',
                        'media_id'  => $xml->MediaId,
                        'format'    => $xml->Format,
                        'msg_id'    => $xml->MsgId,
                        'local_file_name'   => $file_name
                    ];
                    $m_id = WeixinMedia::insertGetId($data);
                    if($m_id){
                        $xml_response = '<xml><ToUserName><![CDATA['.$FromUserName.']]></ToUserName><FromUserName><![CDATA['.$xml->ToUserName.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['. date('Y-m-d H:i:s') .']]></Content></xml>';
                        echo $xml_response;
                    }
            }elseif($xml->MsgType=='voice'){        //处理语音信息
                $this->dlVoice($xml->MediaId);
                $xml_response = '<xml><ToUserName><![CDATA['.$FromUserName.']]></ToUserName><FromUserName><![CDATA['.$xml->ToUserName.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['. date('Y-m-d H:i:s') .']]></Content></xml>';
                echo $xml_response;
            }elseif($xml->MsgType=='video'){
                $this->dlVideo($xml->MediaId);
                $xml_response = '<xml><ToUserName><![CDATA['.$FromUserName.']]></ToUserName><FromUserName><![CDATA['.$xml->ToUserName.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['. date('Y-m-d H:i:s') .']]></Content></xml>';
                echo $xml_response;
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
            print_r($data);die;
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

    /**
     * 下载图片素材
     * @param $media_id
     */
    public function dlWxImg($media_id)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->access_token().'&media_id='.$media_id;
        //echo $url;echo '</br>';

        //保存图片
        $client = new GuzzleHttp\Client();
        $response = $client->get($url);
        //$h = $response->getHeaders();

        //获取文件名
        $file_info = $response->getHeader('Content-disposition');
        $file_name = substr(rtrim($file_info[0],'"'),-20);

        $wx_image_path = 'wechat/images/'.$file_name;
        //保存图片
        $r = Storage::disk('local')->put($wx_image_path,$response->getBody());
        if($r){     //保存成功
            echo "ok";
        }else{      //保存失败
            echo "no";
            exit;
        }
    }

    /**
     * 下载语音文件
     * @param $media_id
     */
    public function dlVoice($media_id)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->access_token().'&media_id='.$media_id;

        $client = new GuzzleHttp\Client();
        $response = $client->get($url);
        //$h = $response->getHeaders();
        //echo '<pre>';print_r($h);echo '</pre>';die;
        //获取文件名
        $file_info = $response->getHeader('Content-disposition');
        $file_name = substr(rtrim($file_info[0],'"'),-20);

        $wx_image_path = 'wechat/voice/'.$file_name;
        //保存图片
        $r = Storage::disk('local')->put($wx_image_path,$response->getBody());
        if($r){     //保存成功
            echo "OK";
        }else{      //保存失败
            echo "NO";
            exit;
        }
    }

    /**
     * 下载视频文件
     * @param $media_id
     */
    public function dlVideo($media_id)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->access_token().'&media_id='.$media_id;

        $client = new GuzzleHttp\Client();
        $response = $client->get($url);
        //$h = $response->getHeaders();
        //echo '<pre>';print_r($h);echo '</pre>';die;
        //获取文件名
        $file_info = $response->getHeader('Content-disposition');
        $file_name = substr(rtrim($file_info[0],'"'),-20);

        $wx_image_path = 'wechat/video/'.$file_name;
        //保存图片
        $r = Storage::disk('local')->put($wx_image_path,$response->getBody());
        if($r){     //保存成功
            echo "OK";
        }else{      //保存失败
            echo "NO";
            exit;
        }
    }

    /**
     * 支付商品测试
     */
    public function goods(){
        $list = OrderModel::where(['uid'=>2,'is_pay'=>0])->orderBy('oid','desc')->get()->toArray();
        $data = [
            'list'  => $list
        ];
        return view('goods.order',$data);
    }

}