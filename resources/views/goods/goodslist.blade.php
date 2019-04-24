{{--商品列表 --}}
@extends('layout.bst')

@section('content')
    <div class="container">
        <table class="table table-striped">
            @foreach($data as $k=>$v)
                <tr>
                    <td>{{$v['goods_name']}}  </td>
                    <td>¥ {{$v['goods_price']}}</td>
                    <td><a href="/goods/{{$v['goods_id']}}">查看商品</a></td>
                </tr>
            @endforeach
        </table>
        <hr>

    </div>
    <script src="/js/jquery-3.3.1.min.js"></script>
    <script src="http://res2.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
    <script>
        wx.config({
            debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
            appId: "{{$js_config['appId']}}", // 必填，公众号的唯一标识
            timestamp:"{{$js_config['timestamp']}}" , // 必填，生成签名的时间戳
            nonceStr: "{{$js_config['nonceStr']}}", // 必填，生成签名的随机串
            signature: "{{$js_config['signature']}}",// 必填，签名
            jsApiList: ['chooseImage'] // 必填，需要使用的JS接口列表
        });

    </script>
@endsection

@section('footer')
    @parent
@endsection