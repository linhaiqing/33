<?php

$url = "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=v3C8wuDP8gOqBnWCLfHzZs22L1h6yGfR-BpW8IOPxTe5rfAzLYt-z7XlNrkIgjoh1cauSOVoaXC4tVBQrA7RDLrbUNW3t3duYiZQXY7MfAbmoqvq2CzHYd9g_FhuObdPHNJdAFAQWS";
$res = http_request($url);
$ress = json_decode($res);
$a = objectToArray($ress);
echo "<pre>";
foreach($a['ip_list'] as $v){
	echo $v;echo ",<br>";
}
var_dump($a);


//https请求(支持GET和POST)
	 function http_request($url,$data = null){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if(!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		//var_dump(curl_error($curl));
		curl_close($curl);
		return $output;
	}
function objectToArray($e){
		$e=(array)$e;
		foreach($e as $k=>$v){
			if( gettype($v)=='resource' ) return;
			if( gettype($v)=='object' || gettype($v)=='array' )
				$e[$k]=(array)objectToArray($v);
		}
		return $e;
	}