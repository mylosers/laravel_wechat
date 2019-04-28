<?php

namespace App\Admin\Controllers;

use Illuminate\Http\Request;
use App\Model\WeixinUser;
use App\Model\SnapModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use App\Http\Controllers\Wechat\WechatController;
use GuzzleHttp;
use Illuminate\Support\Str;



class UserController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WeixinUser);

        $grid->id('Id');
        $grid->uid('Uid');
        $grid->FromUserName('FromUserName');
        $grid->CreateTime('CreateTime')->display(function ($time) {
            return date('Y-m-d H:i:s', $time);
        });
        $grid->nickname('Nickname');
        $grid->sex('Sex');
        $grid->headimgurl('Headimgurl')->display(function ($image) {
            return '<img src="' . $image . '">';
        });
        $grid->subscribe_time('Subscribe time')->display(function ($time) {
            return date('Y-m-d H:i:s', $time);
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(WeixinUser::findOrFail($id));

        $show->id('Id');
        $show->uid('Uid');
        $show->FromUserName('FromUserName');
        $show->CreateTime('CreateTime');
        $show->nickname('Nickname');
        $show->sex('Sex');
        $show->headimgurl('Headimgurl');
        $show->subscribe_time('Subscribe time');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new WeixinUser);

        $form->number('uid', 'Uid');
        $form->text('FromUserName', 'FromUserName');
        $form->number('CreateTime', 'CreateTime');
        $form->text('nickname', 'Nickname');
        $form->switch('sex', 'Sex');
        $form->text('headimgurl', 'Headimgurl');
        $form->number('subscribe_time', 'Subscribe time');

        return $form;
    }

    /**
     * access_token
     */
    public function access_token()
    {
        $wechat = new WechatController();
        $access_token = $wechat->access_token();
        return $access_token;
    }

    /**
     * 群发页面
     */
    public function MassAll(Content $content)
    {
        $access_token = $this->access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=' . $access_token . '&next_openid=';
        $data = json_decode(file_get_contents($url), true);
        $data = $data['data']['openid'];
        return $content
            ->header('微信')
            ->description('群发列表')
            ->body(view('wechat.massall')->with('data', $data));
    }

    /**
     * 执行群发
     */
    public function MassAllAdd()
    {
        $openid = $_POST['openid'];
        $media_id = $_POST['media_id'];
        $type = $_POST['type'];
        /*        print_r($openid);
                print_r($media_id);
                print_r($type);die;*/
        $access_token = $this->access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=$access_token";
        if ($type == "text") {
            $data = [
                "touser" => $openid,
                "msgtype" => "text",
                "text" => [
                    "content" => "$media_id"
                ]
            ];
        } else if ($type == "mpnews") {
            $data = [
                "touser" => $openid,
                "$type" => [
                    "media_id" => "$media_id"
                ],
                "msgtype" => "$type",
                "send_ignore_reprint" => 0
            ];
        } else if ($type == "mpvideo") {
            $data = [
                "touser" => $openid,
                "$type" => [
                    "media_id" => "$media_id",
                    "title" => "TITLE",
                    "description" => "DESCRIPTION"
                ],
                "msgtype" => "$type",
                "send_ignore_reprint" => 0
            ];
        } else {
            $data = [
                "touser" => $openid,
                "$type" => [
                    "media_id" => "$media_id"
                ],
                "msgtype" => "$type"
            ];
        }
        $client = new GuzzleHttp\Client(['base_uri' => $url]);
        $r = $client->request('POST', $url, [
            'body' => json_encode($data, JSON_UNESCAPED_UNICODE)
        ]);
        // 3 解析微信接口返回信息
        $response_arr = json_decode($r->getBody(), true);
        if ($response_arr['errcode'] == 0) {
            return 'ok';
        }
    }

    /**
     * 上传临时素材
     */
    public function snap(Content $content){
        return $content
            ->header('微信')
            ->description('上传临时素材')
            ->body(view('wechat.snap'));
    }

    /**
     * 上传
     */
    public function upload(Request $request){
        $type=$_FILES['file']['type'];
        $type=explode("/",$type);
        if($type['0']=="image"){
            $type_file="image";
        }else if($type['0']=="video"){
            $type_file="video";
        }else if($type['1']=="mp3"||$type['1']=="amr"){
            $type_file="voice";
        }else{
            $type_file="thumb";
        }
        if (request()->hasFile('file') && request()->file('file')->isValid()) {
            $photo = request()->file('file');
            $extension = $photo->getClientOriginalExtension();
            //文件名称s
            $path = time() . 'test' . Str::random(8) . '.' . $extension;
            $store_result = $photo->storeAs("$type_file", $path);
        }
        $wechat = new WechatController();
        $access_token = $wechat->access_token();
        //echo $access_token;
        $url = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$access_token.'&type='.$type_file;
        $client = new GuzzleHttp\Client();
        //var_dump($client);
        $response = $client->request('post',$url,[
            'multipart' => [
                [
                    'name' => 'media',
                    'contents' => fopen('../storage/app/'.$type_file.'/'.$path, 'r'),
                ]
            ]
        ]);

        $json=$response->getBody();
        $arr=json_decode($json,true);

        $data = [
            'type' =>$arr['type'],
            'media_id'=>$arr['media_id'],
            'created_at'=>$arr['created_at'],
            'status'=>1,
        ];

        $cid = SnapModel::insertGetId($data);
        if($cid){
            $img_url=$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
            $url=$img_url.'/admin/wechat/snapList';
            header('Location:'.$url);
        }
    }
}
