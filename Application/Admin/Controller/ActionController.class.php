<?php

//decode by http://www.yunlu99.com/
namespace Admin\Controller;

use Think\Controller;
class ActionController extends Controller
{
	function __construct()
	{
		parent::__construct();
		// $url = "http://swjd.iweixinyingxiao.com/fanxingplan/";
		// $data = array();
		// $data['url'] = $_SERVER['SERVER_NAME'];
		// $data['ip'] = $_SERVER["SERVER_ADDR"];
		// $res = $this->http_request($url, $data);
		// $result = json_decode($res, true);
		// if ($result == 'error') {
		// 	echo "<div style='color:green;font-size:26px;font-family:Microsoft YaHei;margin-top:10%;text-align:center;'>该域名需要授权，请联系微信13607648696</div>";
		// 	die;
		// }
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