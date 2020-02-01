<?php

//decode by http://www.yunlu99.com/
namespace Home\Controller;

use Think\Controller;
class WapController extends Controller
{
	public function index()
	{
		$news_id = $_GET['id'];
		if ($news_id == '') {
			exit;
		}
		$newsinfo = M('news')->where(" id = '{$news_id}' ")->select();
		foreach ($newsinfo as $key => $value) {
			$newsinfo[$key]['createtime'] = date("Y-m-d", $value['createtime']);
		}
		$this->assign("newsinfo", $newsinfo);
		$weixin = A("Wxapi/Weixin");
		$signPackage = $weixin->getSignPackage();
		$this->assign("signPackage", $signPackage);
		$this->display();
	}
	public function daijin()
	{
		if (session('user_id')) {
			$user_id = session('user_id');
		} else {
			$openid = $this->openid();
			if ($openid == '') {
				echo '<div style="font-size:66px;text-align:center;margin-top:40%;">获取代理商身份信息错误，请关闭重新打开</div>';
				exit;
			}
			$wechat = A("User/Wechat");
			$user_id = $wechat->get_user_id($openid);
			if ($user_id == 0) {
				echo '<div style="font-size:66px;text-align:center;margin-top:40%;">Sorry<br />您不是代理商，无权进行核销操作</div>';
				exit;
			}
			session('user_id', $user_id);
		}
		$d_user_id = $_GET['user_id'];
		$users = M('users');
		$userinfo = $users->field("nickname,user_id,daijin")->getByUser_id($d_user_id);
		if ($userinfo['daijin'] == 0) {
			echo '<div style="font-size:66px;text-align:center;margin-top:40%;">晚来一步<br />该代金券已进行过核销操作</div>';
			exit;
		}
		$user_info = $users->field("user_id,daili,nickname")->getByUser_id($user_id);
		if ($user_info['daili'] == 0) {
			echo '<div style="font-size:66px;text-align:center;margin-top:40%;">Sorry<br />您不是代理商，无权进行核销操作</div>';
			exit;
		}
		$weixin = A("Wxapi/Weixin");
		$signPackage = $weixin->getSignPackage();
		$this->assign("signPackage", $signPackage);
		$this->assign('userinfo', $userinfo);
		$this->assign('app_info', F('config_info', '', DATA_ROOT));
		$this->assign('user_info', $user_info);
		$this->display();
	}
	public function daijin_del()
	{
		if ($_POST['user_id'] == null || $_POST['from_user_id'] == null) {
			exit;
		}
		$users = M('users');
		$daijin = M('daijin');
		$arr = array();
		$user_daijin = $users->getFieldByUser_id($_POST['user_id'], 'daijin');
		if ($user_daijin == 0) {
			$arr['success'] = 0;
			$arr['info'] = '系统已处理';
		} else {
			$user_id = $_POST['user_id'];
			$users->where(" user_id = '{$user_id}' ")->setField('daijin', 0);
			$data = array('user_id' => $_POST['from_user_id'], 'del_user_id' => $_POST['user_id'], 'time' => time());
			$daijin->add($data);
		}
		$arr['info'] = '系统已处理';
		echo json_encode($arr);
	}
	private function openid()
	{
		if (F('config_info', '', DATA_ROOT)) {
			$config_info = F('config_info', '', DATA_ROOT);
		} else {
			$config_info = M('config')->select();
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
