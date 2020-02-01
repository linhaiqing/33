<?php

//decode by http://www.yunlu99.com/
namespace Agent\Controller;

use Think\Controller;
class BuyController extends Controller
{
	public function index()
	{
		if (session('user_id')) {
			$user_id = session('user_id');
		} else {
			$openid = $this->openid();
			if ($openid == '') {
				echo '<div style="font-size:66px;text-align:center;margin-top:40%;">获取身份信息错误，请关闭重新打开</div>';
				exit;
			}
			$wechat = A("User/Wechat");
			$user_id = $wechat->get_user_id($openid);
			if ($user_id == 0) {
				echo '<div style="font-size:66px;text-align:center;margin-top:40%;">系统没有您的数据<br />请取消关注后重新关注</div>';
				exit;
			}
			session('user_id', $user_id);
		}
		$user_info = M('users')->getByUser_id($user_id);
		if (F('daili_info', '', DATA_ROOT)) {
			$daili_info = F('daili_info', '', DATA_ROOT);
		} else {
			$daili_info = M('daili_info')->select();
			F('daili_info', $daili_info, DATA_ROOT);
		}
		$first_fee = $daili_info[0]['first_fee'];
		$second_fee = $daili_info[0]['second_fee'];
		$third_fee = $daili_info[0]['third_fee'];
		$fee = array("first_fee" => $first_fee, "second_fee" => $second_fee, "third_fee" => $third_fee);
		$this->assign("base_fee", $fee);
		if ($user_info['agent'] == 1) {
			$second_fee -= $first_fee;
			$third_fee -= $first_fee;
			$first_fee = 0.0;
		}
		if ($user_info['agent'] == 2) {
			$third_fee -= $second_fee;
			$first_fee = 0.0;
			$second_fee = 0.0;
		}
		if ($user_info['agent'] == 3) {
			$first_fee = 0.0;
			$second_fee = 0.0;
			$third_fee = 0.0;
		}
		$weixin = A("Wxapi/Weixin");
		$signPackage = $weixin->getSignPackage();
		$fee = array("first_fee" => $first_fee, "second_fee" => $second_fee, "third_fee" => $third_fee);
		$address_info = M('user_address')->getByUser_id($user_id);
		$banner = M('daili_banner')->select();
		$this->assign("banner", $banner);
		$this->assign("daili_info", $daili_info);
		$this->assign("fee", $fee);
		$this->assign("address_info", $address_info);
		$this->assign("user_info", $user_info);
		$this->assign("signPackage", $signPackage);
		$this->display();
	}
	private function openid()
	{
		if (F('config_info', '', DATA_ROOT)) {
			$config_info = F('config_info', '', DATA_ROOT);
		} else {
			$config_info = M('config')->field("appid,appsecret")->select();
			F('config_info', $config_info, DATA_ROOT);
		}
		$appid = $config_info[0]['appid'];
		$appserect = $config_info[0]['appsecret'];
		if (empty($_GET['code'])) {
			$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . $redirect_uri . "&response_type=code&scope=snsapi_base&state=333#wechat_redirect";
			header("Location:{$url}");
		} else {
			$code = $_GET['code'];
			$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $appid . "&secret=" . $appserect . "&code=" . $code . "&grant_type=authorization_code";
			$res = $this->http_request($url);
			$result = json_decode($res, true);
			$access_token = $result['access_token'];
			$openid = $result['openid'];
			return $openid;
		}
	}
	public function buy()
	{
		if ($_POST == NULL || !session('user_id')) {
			echo '<div style="font-size:66px;text-align:center;margin-top:40%;">支付请求过期，请重试</div>';
			exit;
		}
		$address_data = array('user_id' => session('user_id'), 'username' => $_POST['username'], 'telphone' => $_POST['telphone'], 'address' => $_POST['address']);
		M('users')->where(" user_id = '{$address_data['user_id']}' ")->setField("wxid", $_POST['wxid']);
		if ($_POST['address_id'] == null) {
			M('user_address')->add($address_data);
		} else {
			M('user_address')->where(" address_id = '{$_POST['address_id']}' ")->save($address_data);
		}
		if (F('daili_info', '', DATA_ROOT)) {
			$daili_info = F('daili_info', '', DATA_ROOT);
		} else {
			$daili_info = M('daili_info')->select();
			F('daili_info', $daili_info, DATA_ROOT);
		}
		$template_info = array();
		$template_info = F('template_info', '', DATA_ROOT);
		switch ($_POST['radio1']) {
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
		$first_fee = $daili_info[0]['first_fee'];
		$second_fee = $daili_info[0]['second_fee'];
		$third_fee = $daili_info[0]['third_fee'];
		$total_fee = 0;
		$bucha = 0;
		$agent = M('users')->getFieldByUser_id(session('user_id'), 'agent');
		switch ($agent) {
			case 0:
				if ($_POST['radio1'] == 1) {
					$total_fee = $first_fee;
				}
				if ($_POST['radio1'] == 2) {
					$total_fee = $second_fee;
				}
				if ($_POST['radio1'] == 3) {
					$total_fee = $third_fee;
				}
				break;
			case 1:
				if ($_POST['radio1'] == 2) {
					$total_fee = $second_fee - $first_fee;
					$bucha = 1;
				}
				if ($_POST['radio1'] == 3) {
					$total_fee = $third_fee - $first_fee;
					$bucha = 2;
				}
				break;
			case 2:
				if ($_POST['radio1'] == 3) {
					$total_fee = $third_fee - $second_fee;
					$bucha = 1;
				}
				break;
			default:
				break;
		}
		if ($total_fee == 0) {
			echo '<div style="font-size:66px;text-align:center;margin-top:40%;">订单金额不符，请核对</div>';
			exit;
		}
		$total_fee = $total_fee * 100;
		if (F('config_info', '', DATA_ROOT)) {
			$config_info = F('config_info', '', DATA_ROOT);
		} else {
			$config_info = M('config')->select();
			F('config_info', $config_info, DATA_ROOT);
		}
		$appid = $config_info[0]['appid'];
		$openid = M('users')->getFieldByUser_id(session('user_id'), 'openid');
		$mch_id = $config_info[0]['machid'];
		$key = $config_info[0]['mkey'];
		$nonce_str = $this->createNonceStr();
		$notify_url = "http://" . $_SERVER['SERVER_NAME'] . U('/Wxapi/Notify/agent');
		$out_trade_no = "2016" . session('user_id') . time();
		$save = array('user_id' => session('user_id'), 'order_sn' => $out_trade_no, 'openid' => $openid, 'type' => $_POST['radio1'], 'bucha' => $bucha, 'total_fee' => $total_fee / 100, 'time' => time());
		$agent_orders = M('agent_orders');
		$order_id = $agent_orders->add($save);
		$sign = $this->signjiami($appid, $openid, $mch_id, $nonce_str, $total_fee, $out_trade_no, $notify_url, $order_id, $key);
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		$data = "<xml>\r\n\t\t   <appid>" . $appid . "</appid>\r\n\t\t   <attach>" . $order_id . "</attach>\r\n\t\t   <body>代理费</body>\r\n\t\t   <mch_id>" . $mch_id . "</mch_id>\r\n\t\t   <nonce_str>" . $nonce_str . "</nonce_str>\r\n\t\t   <notify_url>" . $notify_url . "</notify_url>\r\n\t\t   <openid>" . $openid . "</openid>\r\n\t\t   <out_trade_no>" . $out_trade_no . "</out_trade_no>\r\n\t\t   <spbill_create_ip>14.23.150.211</spbill_create_ip>\r\n\t\t   <total_fee>" . $total_fee . "</total_fee>\r\n\t\t   <trade_type>JSAPI</trade_type>\r\n\t\t   <sign>" . $sign . "</sign>\r\n\t\t</xml>";
		$result = $this->http_request($url, $data);
		$postObj = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
		$prepay_id = trim($postObj->prepay_id);
		$paysign = $this->paysign($prepay_id, $appid, $key);
		$weixin = A("Wxapi/Weixin");
		$tem_data = '{
           "touser":"' . $openid . '",
           "template_id":"' . $template_info['order_create_template_id'] . '",
           "url":"http://' . $_SERVER['SERVER_NAME'] . U('/Agent/Buy/pay') . '?openid=' . $openid . '&order_id=' . $order_id . '&prepay_id=' . $prepay_id . '",            
           "data":{
                   "first": {
                       "value":"您的订单已创建成功！请您及时完成付款，如果您已完成付款，请忽略本条通知~",
                       "color":"' . $template_info['order_create_top'] . '"
                   },
                   "orderno":{
                       "value":"' . $save['order_sn'] . '",
                       "color":"' . $template_info['order_create_text'] . '"
                   },
                   "refundno": {
                       "value":"' . $good_name . '*1",
                       "color":"' . $template_info['order_create_text'] . '"
                   },
                   "refundproduct": {
                       "value":"' . $save['total_fee'] . '元",
                       "color":"' . $template_info['order_create_text'] . '"
                   },
                   "remark":{
                       "value":"订单创建时间：' . date("Y-m-d H:i:s", $save['time']) . '",
                       "color":"' . $template_info['order_create_text'] . '"
                   }
           }
       }';
		$c = $weixin->send_template($openid, $tem_data);
		$signPackage = $weixin->getSignPackage();
		$this->assign("signPackage", $signPackage);
		$this->assign("paysign", $paysign);
		$this->display();
	}
	public function pay()
	{
		if (F('config_info', '', DATA_ROOT)) {
			$config_info = F('config_info', '', DATA_ROOT);
		} else {
			$config_info = M('config')->select();
			F('config_info', $config_info, DATA_ROOT);
		}
		$appid = $config_info[0]['appid'];
		$mch_id = $config_info[0]['machid'];
		$key = $config_info[0]['mkey'];
		$order_id = $_GET['order_id'];
		$prepay_id = $_GET['prepay_id'];
		$order_info = M('agent_orders')->getByOrder_id($order_id);
		if (!$order_info || !$prepay_id) {
			echo '<div style="font-size:66px;text-align:center;margin-top:40%;">待支付订单已过期或不存在！</div>';
			exit;
		}
		$paysign = $this->paysign($prepay_id, $appid, $key);
		$weixin = A("Wxapi/Weixin");
		$signPackage = $weixin->getSignPackage();
		$this->assign("signPackage", $signPackage);
		$this->assign("paysign", $paysign);
		$this->display('buy');
	}
	public function signjiami($appid, $openid, $mch_id, $nonce_str, $total_fee, $out_trade_no, $notify_url, $order_id, $key)
	{
		$string1 = "appid=" . $appid . "&attach=" . $order_id . "&body=代理费&mch_id=" . $mch_id . "&nonce_str=" . $nonce_str . "&notify_url=" . $notify_url . "&openid=" . $openid . "&out_trade_no=" . $out_trade_no . "&spbill_create_ip=14.23.150.211&total_fee=" . $total_fee . "&trade_type=JSAPI";
		$result = md5($string1 . "&key=" . $key);
		return strtoupper($result);
	}
	public function paysign($prepay_id, $appid, $key)
	{
		$timeStamp = time();
		$nonceStr = $this->createNonceStr();
		$string = "appId=" . $appid . "&nonceStr=" . $nonceStr . "&package=prepay_id=" . $prepay_id . "&signType=MD5&timeStamp=" . $timeStamp;
		$res = md5($string . "&key=" . $key);
		$arr = array("timeStamp" => $timeStamp, "appid" => $appid, "nonceStr" => $nonceStr, "prepay_id" => $prepay_id, "paySign" => strtoupper($res));
		return $arr;
	}
	private function createNonceStr($length = 16)
	{
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return "z" . $str;
	}
	function http_request($url, $data = null)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (!empty($data)) {
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}
}
