<?php

namespace App\Http\Controllers\Wechat;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;
use App\Model\GoodsModel;
use App\Model\TextModel;

class KaoController extends Controller
{
    public function wxEvents()
    {
        $data = file_get_contents("php://input");
        //解析xml数据
        $xml = simplexml_load_string($data);
        //记录日志
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<";
        file_put_contents('logs/wx_event.log', $log_str, FILE_APPEND);
        $MsgType = $xml->MsgType;
        $ToUserName = $xml->ToUserName;
        $FromUserName = $xml->FromUserName;
        if (isset($MsgType)) {
            if ($MsgType == "event") {
                if ($xml->Event == "subscribe") {
                    $xml_str = '<xml>
  <ToUserName><![CDATA[' . $FromUserName . ']]></ToUserName>
  <FromUserName><![CDATA[' . $ToUserName . ']]></FromUserName>
  <CreateTime>' . time() . '</CreateTime>
  <MsgType><![CDATA[text]]></MsgType>
  <Content><![CDATA[输入商品+商品名称，字样]]></Content>
</xml>';
                    echo $xml_str;
                }
            } else if ($MsgType == "text") {
                if (strpos($xml->Content, "商品+") !== false) {
                    $arr = explode('+', $xml->Content);
                    $text = $arr[1];
                    $goods = GoodsModel::where(['goods_name' => $text])->first();
                    if ($goods) {
                        $goods = $goods->toArray();
                    } else {
                        $goods = "";
                    }
                    if ($goods != "") {
                        $current_url=$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
                        $url_goods=$current_url.'/goods/'.$goods['goods_id'];
                        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $this->access_token();
                        $data = [
                            "touser" => "$FromUserName",
                            "template_id" => "ovuxQ0-vaVq6ESso7eopEMs1dP4MIk24x8S0VNhZ--c",
                            "url" =>"$url_goods",
                            "data" => [
                                "first" => [
                                    "value" => "恭喜你购买成功！",
                                    "color" => "#173177"
                                ]
                            ]
                        ];
                        $client = new GuzzleHttp\Client(['base_uri' => $url]);
                        $r = $client->request('POST', $url, [
                            'body' => json_encode($data,JSON_UNESCAPED_UNICODE)
                        ]);
                        // 3 解析微信接口返回信息
                        $response_arr = json_decode($r->getBody(),true);
//                        print_r($response_arr);die;
                    }
                    //使用redis缓存
                    $redis=new \Redis();
                    $redis->connect('127.0.0.1',6379);
                    $key="key";
                    $val=$text;
                    $redis->set($key,$val);
                    //入库
                    $data_text=[
                        'text'=>$text,
                        'openid'=>$FromUserName,
                        'c_time'=>time()
                    ];
                    $i=TextModel::insertGetId($data_text);
                    if($i){
                        echo "成功";
                    }
                }
            }
        }
    }

    /**
     * access_token
     */
    public function access_token(){
        $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_APPSECRET');
        $token=file_get_contents($url);
        $access_token=json_decode($token,true);
        return $access_token['access_token'];
    }
}