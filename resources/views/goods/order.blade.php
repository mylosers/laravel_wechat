<div class="container">
        <h3>未支付订单：</h3>
        <table class="table table-striped">
            @foreach($list as $k=>$v)
                <tr>
                    <td>订单ID: {{$v['order_sn']}} --  订单总价：¥{{$v['order_amount']}}   --  下单时间：{{date('Y-m-d H:i:s',$v['add_time'])}}</td>
                    <td><a href="/wechat/PayCode?orderId={{$v['order_sn']}}&total={{$v['order_amount']}}&oid={{$v['oid']}}" class="btn btn-info">微信扫码支付</a></td>
                </tr>
            @endforeach
        </table>
</div>
