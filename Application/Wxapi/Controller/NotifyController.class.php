<?php

//decode by http://www.yunlu99.com/
namespace Wxapi\Controller;

use Think\Controller;
class NotifyController extends Controller
{
	public function agent()
	{
//		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
//		$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
//		$order_id = trim($postObj->attach);
        $order_id = I('order_id');
		$agent_orders = M('agent_orders');
		$order_info = $agent_orders->getByOrder_id($order_id);
//		if ($order_info['is_true'] == 1) {
//			echo '<xml>
//			   <return_code><![CDATA[SUCCESS]]></return_code>
//			   <return_msg><![CDATA[OK]]></return_msg>
//			</xml>';
//			exit;
//		}
		$user_id = trim($order_info['user_id']);
		$type = trim($order_info['type']);
		if (F('daili_info', '', DATA_ROOT)) {
			$daili_info = F('daili_info', '', DATA_ROOT);
		} else {
			$daili_info = M('daili_info')->select();
			F('daili_info', $daili_info, DATA_ROOT);
		}
		switch ($type) {
			case 1:
				$good_name = $daili_info[0]['first_name'];
				break;
			case 2:
				$good_name = $daili_info[0]['second_name'];
				break;
			case 3:
				$good_name = $daili_info[0]['third_name'];
				break;
			default:
				$good_name = "未知产品";
				break;
		}
		$openid = $order_info['openid'];
		$users = M('users');
		$agent = $users->getFieldByUser_id($user_id, 'agent');
		$data = array();
		if ($agent == 0) {
			$data['daijin'] = 1;
			if (!is_dir('Public/daijin/')) {
				mkdir('Public/daijin/');
			}
			import('Org.Util.Erweima');
			$value = "http://" . $_SERVER['SERVER_NAME'] . U('/Home/wap/daijin') . "?user_id=" . $user_id;
			$errorCorrectionLevel = "L";
			$matrixPointSize = "6";
			\QRcode::png($value, 'Public/daijin/' . $user_id . '.png', $errorCorrectionLevel, $matrixPointSize, 1, true);
		}
		$data['agent'] = $type;
		$res = $users->where(" user_id = '{$user_id}' ")->save($data);
		$template_info = array();
		$template_info = F('template_info', '', DATA_ROOT);
//		if ($res) {
			$wechat = A("User/Wechat");
			$wechat->contact_user($user_id);
			$wechat->deal($user_id, $order_info);
			$weixin = A("Wxapi/Weixin");
			$tem_data = '{
			   "touser":"' . $openid . '",
			   "template_id":"' . $template_info['order_done_template_id'] . '",
			   "url":"",            
			   "data":{
					   "first": {
						   "value":"您已经支付成功，成功晋升为平台代理~",
						   "color":"' . $template_info['order_done_top'] . '"
					   },
					   "product":{
						   "value":"' . $good_name . '",
						   "color":"' . $template_info['order_done_text'] . '"
					   },
					   "price": {
						   "value":"' . $order_info['total_fee'] . '元",
						   "color":"' . $template_info['order_done_text'] . '"
					   },
					   "time": {
						   "value":"' . date("Y-m-d H:i:s", $order_info['time']) . '",
						   "color":"' . $template_info['order_done_text'] . '"
					   },
					   "remark":{
						   "value":"",
						   "color":"#555"
					   }
			   }
		   }';

			echo '<pre>';
			print_r($tem_data);

//			$c = $weixin->send_template($openid, $tem_data);
			$agent_orders->where(" order_id = '{$order_id}' ")->setField("is_true", 1);
            die();
//		}
	}
	public function shop()
	{
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
		$pay_id = trim($postObj->attach);
		$total_fee = trim($postObj->cash_fee) / 100;
		$shop_order = M('shop_order');
		$shop_order_detail = M('shop_order_detail');
		$order = $shop_order->getByPay_id($pay_id);
		if ($order['is_true'] == 1) {
			echo '<xml>
			   <return_code><![CDATA[SUCCESS]]></return_code>
			   <return_msg><![CDATA[OK]]></return_msg>
			</xml>';
			exit;
		}
		$user_id = trim($order['user_id']);
		$openid = M('users')->getFieldByUser_id($user_id, 'openid');
		$good_name = "";
		$order_info = $shop_order_detail->field("good_id,good_name,good_num")->where(" pay_id = '{$pay_id}' ")->select();
		foreach ($order_info as $v) {
			$good_name .= $v['good_name'] . ".";
		}
		$wechat = A("User/Wechat");
		$wechat->shop_deal($user_id, $order_info);
		$shop_order->where(" pay_id = '{$pay_id}' ")->save(array('is_true' => 1, 'pay_time' => time()));
		$template_info = array();
		$template_info = F('template_info', '', DATA_ROOT);
		$weixin = A("Wxapi/Weixin");
		$tem_data = '{
			   "touser":"' . $openid . '",
			   "template_id":"' . $template_info['order_done_template_id'] . '",
			   "url":"",            
			   "data":{
				   "first": {
					   "value":"您的订单已付款成功，请等待发货",
					   "color":"' . $template_info['order_done_top'] . '"
				   },
				   "product":{
					   "value":"' . $good_name . '",
					   "color":"' . $template_info['order_done_text'] . '"
				   },
				   "price": {
					   "value":"' . $total_fee . '元",
					   "color":"' . $template_info['order_done_text'] . '"
				   },
				   "time": {
					   "value":"' . date("Y-m-d H:i:s", $order['time']) . '",
					   "color":"' . $template_info['order_done_text'] . '"
				   },
				   "remark":{
					   "value":"",
					   "color":"' . $template_info['order_done_text'] . '"
				   }
			   }
		   }';
		$c = $weixin->send_template($openid, $tem_data);
		echo '<xml>
			   <return_code><![CDATA[SUCCESS]]></return_code>
			   <return_msg><![CDATA[OK]]></return_msg>
			</xml>';
	}
}
