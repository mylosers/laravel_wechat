@extends('layout.bst')

@section('content')

    <div class="container">
        <h1>{{$goods->goods_name}}</h1>

        <span> 价格： {{$goods->goods_price}}</span>


        <form class="form-inline">
            <meta name="csrf-token" content="{{ csrf_token() }}">
            <div class="form-group">
                <label class="sr-only" for="goods_num">Amount (in dollars)</label>
                <div class="input-group">
                    <input type="number" class="form-control" id="goods_num" value="1">
                </div>
            </div>
            <input type="hidden" id="goods_id" value="{{$goods->goods_id}}">
            <button type="submit" class="btn btn-primary" id="add_cart_btn">加入购物车</button>
        </form>
    </div>
    <script src="../js/jquery-3.3.1.min.js"></script>
    <script src="http://res2.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
    <script>
        wx.config({
            debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
            appId: "{{$js_config['appId']}}", // 必填，公众号的唯一标识
            timestamp:"{{$js_config['timestamp']}}" , // 必填，生成签名的时间戳
            nonceStr: "{{$js_config['nonceStr']}}", // 必填，生成签名的随机串
            signature: "{{$js_config['signature']}}",// 必填，签名
            jsApiList: ['chooseImage','uploadImage','updateAppMessageShareData'] // 必填，需要使用的JS接口列表
        });

        wx.ready(function () {   //需在用户可能点击分享按钮前就先调用
            wx.updateAppMessageShareData({
                title: "{{$goods->goods_name}}", // 分享标题
                desc: '最新福利', // 分享描述
                link: "{{$js_config['url']}}", // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: "http://wechat.myloser.club/image/goods.jpg", // 分享图标
                success: function () {
                    // 设置成功
                    alert('成功')
                }
            })
        });

        $('#goods_num').blur(function(){
            var number=$(this).val();
            var goods_id=$("#goods_id").val();
            if(number<=0){
                $(this).val(1);
            }else if(isNaN(number)){
                $(this).val(1);
            }else{
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url:'/number',
                    type:"post",
                    data:{goods_stock:number,goods_id:goods_id},
                    success:function(info){
                        if(info==1){
                            $("#goods_num").val({{$goods->goods_srcoe}});
                        }
                    }
                })
            }
        })
        //ajax提交
        $("#add_cart_btn").click(function(e){
            e.preventDefault();
            var num = $("#goods_num").val();
            var goods_id = $("#goods_id").val();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url     :   '/goodsAdd',
                type    :   'post',
                data    :   {goods_id:goods_id,num:num},
                dataType:   'json',
                success :   function(d){
                    if(d.error==0){
                        window.location.href='/goods';
                    }else{
                        alert(d.msg);
                    }
                }
            });
            return false;
        })
    </script>
@endsection

@section('footer')
    @parent
@endsection