<?php

namespace App\Admin\Controllers;

use App\Model\WeixinUser;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use App\Http\Controllers\Wechat\WechatController;
use GuzzleHttp;



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
}
