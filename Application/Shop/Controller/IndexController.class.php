<?php

//decode by http://www.yunlu99.com/
namespace Shop\Controller;

use Think\Controller;
class IndexController extends Controller
{
	function __construct()
	{
        session('user_id',I('user_id'));
		parent::__construct();
        session('user_id',2);
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
			if ($user_id == 0 && !$_GET['from_id']) {
				echo '<div style="font-size:66px;text-align:center;margin-top:40%;">系统没有您的数据<br />请取消关注后重新关注</div>';
				exit;
			}
		}
		if ($user_id != 0) {
			session("user_id", $user_id);
//			if (session('user_info')) {
//				$userinfo = session('user_info');
//			} else {
				$userinfo = M('users')->getByUser_id($user_id);
//			}
			$system = F('system_info', '', DATA_ROOT);
            $system['shop_all'] =0;
			if ($userinfo['agent'] == 0 && $system['shop_all'] != 1) {
				$this->error("请先购买代理，即将前往购买页", U('/Agent/Buy/index'), 5);
				exit;
			}
			if ($userinfo['agent'] > 0 && $system['shop_share'] == 1) {
				session('user_info', $userinfo);
				$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'];
				$this->assign('share_url', $redirect_uri);
			}
			if ($system['shop_share'] != 1) {
				session('user_info', null);
			}
			$this->user_id = $user_id;
		} else {
			$this->assign("share_type", 'ok');
			$this->assign("share_id", $_GET['from_id']);
		}
		$this->assign('app_info', F('config_info', '', DATA_ROOT));
		$weixin = A("Wxapi/Weixin");
		$signPackage = $weixin->getSignPackage();
		$this->assign("signPackage", $signPackage);
	}
	public function index()
	{
		$shop_categrey = M('shop_categrey');
		$shop_goods = M('shop_goods');
		$good_pic = M('good_pic');
		$categrey = $shop_categrey->where(" pid = 0 and is_show = 1 ")->order("code desc")->select();
		foreach ($categrey as $k => $v) {
			$good_list[$k]['name'] = $v['cate_name'];
			$good_list[$k]['info'] = $shop_goods->where(" cate_gid = '{$v['cate_id']}' and is_true = 1 ")->order("code desc")->limit("6")->select();
			foreach ($good_list[$k]['info'] as $key => $val) {
				$good_list[$k]['info'][$key]['pic_url'] = $good_pic->getFieldByGood_id($val['good_id'], "pic_url");
			}
		}
		$bannar = M('shop_bannar')->order("code desc")->select();
		$this->assign('bannar', $bannar);
		$ad = M('shop_ad')->order("code desc")->select();
		$this->assign('ad', $ad);
		$num = M('shop_order_temp')->where(" user_id = '{$this->user_id}' ")->sum('good_num');
		$this->assign("num", $num);
		$this->assign("good_list", $good_list);
		$this->display();
	}
	public function search()
	{
		$shop_categrey = M('shop_categrey');
		$shop_goods = M('shop_goods');
		$good_pic = M('good_pic');
		$where = array();
		if ($_POST) {
			$keyword = $_POST['keyword'];
			$where['good_name'] = array('like', '%' . $keyword . '%');
		}
		if ($_GET['pid'] != null) {
			$where['cate_pid'] = $_GET['pid'];
		}
		if ($_GET['gid'] != null) {
			$where['cate_gid'] = $_GET['gid'];
		}
		$good_list = $shop_goods->where($where)->order("code desc")->select();
		foreach ($good_list as $k => $v) {
			$good_list[$k]['pic_url'] = $good_pic->getFieldByGood_id($v['good_id'], "pic_url");
		}
		$num = M('shop_order_temp')->where(" user_id = '{$this->user_id}' ")->sum('good_num');
		$this->assign("keyword", $keyword);
		$this->assign("num", $num);
		$this->assign("good_list", $good_list);
		$this->display();
	}
	public function good()
	{
		if (!$_GET['good_id']) {
			redirect(index);
			exit;
		}
		$shop_goods = M('shop_goods');
		$good_info = $shop_goods->getByGood_id($_GET['good_id']);
		if ($good_info == null) {
			redirect(index);
			exit;
		}
		$pic_url = M('good_pic')->where(" good_id = '{$_GET['good_id']}' ")->select();
		$num = M('shop_order_temp')->where(" user_id = '{$this->user_id}' ")->sum('good_num');
		$this->assign("num", $num);
		$this->assign("good_info", $good_info);
		$this->assign("pic_url", $pic_url);
		$this->display();
	}
	function addcategrey()
	{
		$arr = array();
		if ($_POST['good_id'] == null) {
			$arr['success'] = 0;
		} else {
			$shop_order_temp = M('shop_order_temp');
			$res = $shop_order_temp->where(array("good_id" => $_POST['good_id'], "user_id" => $this->user_id))->find();
			if ($res == null) {
				$result = $shop_order_temp->add(array("good_id" => $_POST['good_id'], "user_id" => $this->user_id));
			} else {
				$order_id = $res['order_id'];
				$result = $shop_order_temp->where(" order_id = '{$order_id}' ")->setInc('good_num');
			}
			if ($result) {
				$arr['success'] = 1;
			} else {
				$arr['success'] = 0;
			}
		}
		echo json_encode($arr);
	}
	public function save_address()
	{
		$arr = array();
		$data = $_POST;
		$user_address = M('user_address');
		$info = $user_address->getByUser_id($this->user_id);
		if ($info) {
			$res = $user_address->where(" user_id = '{$this->user_id}' ")->save($data);
		} else {
			$data['user_id'] = $this->user_id;
			$res = M('user_address')->add($data);
		}
		echo json_encode($arr);
	}
	public function categrey()
	{
		$address_info = M('user_address')->getByUser_id($this->user_id);
		$temp = M('shop_order_temp')->where(" user_id = '{$this->user_id}' ")->order("order_id desc")->select();
		$shop_goods = M('shop_goods');
		$good_pic = M('good_pic');
		foreach ($temp as $k => $v) {
			$temp[$k]['info'] = $shop_goods->getByGood_id($v['good_id']);
			$temp[$k]['info']['pic_url'] = $good_pic->getFieldByGood_id($v['good_id'], 'pic_url');
		}
		$this->assign("address_info", $address_info);
		$this->assign("temp", $temp);
		$this->display();
	}
	function del_categrey()
	{
		$arr = array();
		if ($_POST['order_id'] == null) {
			$arr['success'] = 0;
		} else {
			$order_id = $_POST['order_id'];
			$result = M('shop_order_temp')->where(" order_id = '{$order_id}' ")->delete();
			if ($result) {
				$arr['success'] = 1;
			} else {
				$arr['success'] = 0;
			}
		}
		echo json_encode($arr);
	}
	function change_categrey()
	{
		if ($_POST['order_id'] == null) {
			$arr['success'] = 0;
		} else {
			$order_id = $_POST['order_id'];
			$shop_order_temp = M('shop_order_temp');
			switch ($_POST['type']) {
				case 'min':
					$result = $shop_order_temp->where(" order_id = '{$order_id}' ")->setDec('good_num');
					break;
				case 'max':
					$result = $shop_order_temp->where(" order_id = '{$order_id}' ")->setInc('good_num');
					break;
				default:
					break;
			}
			if ($result) {
				$arr['success'] = 1;
				$arr['info'] = $result;
			} else {
				$arr['success'] = 0;
			}
		}
		echo json_encode($arr);
	}
	function order_sure()
	{
		$shop_order_temp = M('shop_order_temp');
		$shop_order = M('shop_order');
		$shop_order_detail = M('shop_order_detail');
		$good_pic = M('good_pic');
		$shop_goods = M('shop_goods');
		$order_temp = $shop_order_temp->where(" user_id = '{$this->user_id}' ")->select();
		if ($order_temp == null) {
			redirect(index);
		}
		if (S('order_sn') && S('order_sn') < 9990) {
			$order_rand = S('order_sn') + 1;
			S('order_sn', $order_rand);
		} else {
			$order_rand = 1111;
			S('order_sn', $order_rand);
		}
		if (S('pay_id')) {
			$pay_id = S('pay_id') + 1;
			S('pay_id', $pay_id);
		} else {
			$pay_id = $shop_order->max("pay_id");
			$pay_id++;
			S('pay_id', $pay_id);
		}
		$order_sn = date('Y') . $order_rand . time();
		$order_time = time();
		foreach ($order_temp as $val) {
			$pic_url = $good_pic->getFieldByGood_id($val['good_id'], 'pic_url');
			$good_info = $shop_goods->getByGood_id($val['good_id']);
			$order_data = array();
			$order_data = array("order_sn" => $order_sn, "pay_id" => $pay_id, "user_id" => $this->user_id, "good_id" => $val['good_id'], "good_name" => $good_info['good_name'], "good_price" => $good_info['good_price'], "good_profit" => $good_info['good_profit'], "pic_url" => $pic_url, "good_num" => $val['good_num'], "time" => $order_time);
			$res = $shop_order_detail->add($order_data);
			if ($res) {
				$shop_order_temp->where(" order_id = '{$val['order_id']}' ")->delete();
			}
		}
		$arr = array('pay_id' => $pay_id);
		echo json_encode($arr);
	}
	function order_pay()
	{
		if (!$_GET['pay_id']) {
			redirect(index);
			exit;
		} else {
			$pay_id = $_GET['pay_id'];
		}
		$shop_order = M('shop_order');
		$shop_order_detail = M('shop_order_detail');
		$weixin = A("Wxapi/Weixin");
		$order = $shop_order->getByPay_id($pay_id);
		if ($order == null) {
			$order_info = $shop_order_detail->where(" pay_id = '{$pay_id}' ")->select();
			if ($order_info == null) {
				redirect(index);
				exit;
			}
			$total_fee = 0;
			$good_name = "";
			$good_num = 0;
			$shop_goods = M('shop_goods');
			$good_pic = M('good_pic');
			foreach ($order_info as $v) {
				$total_fee = $total_fee + $v['good_price'] * $v['good_num'];
				$good_name .= $v['good_name'] . ".";
				$good_num = $good_num + $v['good_num'];
			}
			$out_trade_no = $order_info[0]['order_sn'];
			$notify_url = "http://" . $_SERVER['SERVER_NAME'] . U('/Wxapi/Notify/shop');
			$openid = M('users')->getFieldByUser_id($this->user_id, 'openid');
			$order_id = $order_info[0]['order_id'];
			$prepay_id = $weixin->get_prepay_id($openid, $total_fee * 100, $out_trade_no, $notify_url, $pay_id);
			$data = array('pay_id' => $pay_id, 'order_sn' => $out_trade_no, 'total_fee' => $total_fee, 'time' => $order_info[0]['time'], 'user_id' => $order_info[0]['user_id'], 'prepay_id' => $prepay_id);
			$shop_order->add($data);
			$template_info = F('template_info', '', DATA_ROOT);
			$tem_data = '{
			   "touser":"' . $openid . '",
			   "template_id":"' . $template_info['order_create_template_id'] . '",
			   "url":"http://' . $_SERVER['SERVER_NAME'] . U('order_pay') . '?pay_id=' . $pay_id . '",            
			   "data":{
					   "first": {
						   "value":"您的订单已创建成功！请您及时完成付款，如果您已完成付款，请忽略本条通知~",
						   "color":"' . $template_info['order_create_top'] . '"
					   },
					   "orderno":{
						   "value":"' . $out_trade_no . '",
						   "color":"' . $template_info['order_create_text'] . '"
					   },
					   "refundno": {
						   "value":"' . $good_name . '*' . $good_num . '",
						   "color":"' . $template_info['order_create_text'] . '"
					   },
					   "refundproduct": {
						   "value":"' . $total_fee . '元",
						   "color":"' . $template_info['order_create_text'] . '"
					   },
					   "remark":{
						   "value":"订单创建时间：' . date("Y-m-d H:i:s", $order_info[0]['time']) . '",
						   "color":"' . $template_info['order_create_text'] . '"
					   }
			   }
		   }';
			$c = $weixin->send_template($openid, $tem_data);
		} else {
			$prepay_id = $order['prepay_id'];
			$order_info = $shop_order_detail->where(" pay_id = '{$pay_id}' ")->select();
			$total_fee = $order['total_fee'];
		}
		$paysign = $weixin->paysign($prepay_id);
		$signPackage = $weixin->getSignPackage();
		$this->assign("signPackage", $signPackage);
		$this->assign("paysign", $paysign);
		$this->assign("order_info", $order_info);
		$this->assign("total_fee", $total_fee);
		$this->display();
	}
	public function catelist()
	{
		$shop_categrey = M('shop_categrey');
		$categrey = $shop_categrey->where(" pid = 0 and hidden = 0 ")->order("code desc")->select();
		foreach ($categrey as $k => $v) {
			$categrey[$k]['arr'] = $shop_categrey->where(" pid = '{$v['cate_id']}' and hidden = 0 ")->order("code desc")->select();
		}
		$num = M('shop_order_temp')->where(" user_id = '{$this->user_id}' ")->sum('good_num');
		$this->assign("num", $num);
		$this->assign("categrey", $categrey);
		$this->display();
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
