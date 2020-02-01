<?php
namespace Wxapi\Controller; @eval("//Encode by  phpjiami.com,Free user."); 
use Think\Controller;

class HongbaoController extends Controller{
	var $parameters;
	public function index($fee,$openid,$wish){
		//设置参数
		if(F('config_info','',DATA_ROOT)){
			$config_info = F('config_info','',DATA_ROOT);
		}else{
			$config_info = M('config') ->select();F('config_info',$config_info,DATA_ROOT);
		}
		$value = $fee*100;
		$appid = $config_info[0][appid];
		$machid = $config_info[0][machid];
		$mkey = $config_info[0][mkey];
		$this->setParameter("nonce_str", $this->great_rand());//随机字符串，丌长于 32 位
        $this->setParameter("mch_billno", $machid.date('YmdHis').rand(1000, 9999));//订单号
        $this->setParameter("mch_id", $machid);//商户号
        $this->setParameter("wxappid", $appid);
        $this->setParameter("nick_name", '西瓜科技');//提供方名称
        $this->setParameter("send_name", $config_info[0]['wxname']);//红包发送者名称
        $this->setParameter("re_openid", $openid);//相对于医脉互通的openid
        $this->setParameter("total_amount", $value);//付款金额，单位分
        $this->setParameter("min_value", $value);//最小红包金额，单位分
        $this->setParameter("max_value", $value);//最大红包金额，单位分
        $this->setParameter("total_num", 1);//红包収放总人数
        $this->setParameter("wishing", $wish);//红包祝福诧
        $this->setParameter("client_ip", '127.0.0.1');//调用接口的机器 Ip 地址
        $this->setParameter("act_name", '参与团购计划');//活劢名称
        $this->setParameter("remark", '赶紧打开吧');//备注信息return_code
        $postXml = $this->create_hongbao_xml($mkey);
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
        $responseXml = $this->curl_post_ssl($url, $postXml);
        $responseObj = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
		return $responseObj;
        
		
	}
	
	
	
	
	//裂变红包
	public function liebian($hongbao,$token,$openid){
		//设置参数
		$config=M('wxconfig')->field("appid,machid,mkey")->where(" token = '$token' ")->select();
		 //return dirname(__FILE__).DIRECTORY_SEPARATOR.'zhengshu'.DIRECTORY_SEPARATOR.'apiclient_cert.pem';
		 // return __ROOT__.'/home/public/uploads/zhengshu/'.$token.'/apiclient_cert.pem';
		 // var_dump($hongbao);
		  // exit;
		// $appid = "wx8b3d2b12a1eb513f";
		// $machid = "10013306";
		// $openid = "o5NEduHfKL9r5JDwtAv_ZJ5lo6m4";mkey="1qaz2wsx3edc4rfv5tgb6yhn7ujm8ik1";
		$appid = $config[0][appid];
		$machid = $config[0][machid];
		$mkey = $config[0][mkey];
		$this->setParameter("nonce_str", $this->great_rand());//随机字符串，丌长于 32 位
        $this->setParameter("mch_billno", $machid.date('YmdHis').rand(1000, 9999));//订单号
        $this->setParameter("mch_id", $machid);//商户号
        $this->setParameter("wxappid", $appid);
        $this->setParameter("send_name", $hongbao[0][send_name]);//红包发送者名称
        $this->setParameter("re_openid", $openid);//相对于医脉互通的openid
        $this->setParameter("total_amount", '300');//付款金额，单位分
        $this->setParameter("amt_type", 'ALL_RAND');//红包发放类型随机金额
        $this->setParameter("total_num", 3);//红包収放总人数
        $this->setParameter("wishing", $hongbao[0][wishing]);//红包祝福诧
        $this->setParameter("act_name", '红包活动');//活劢名称
        $this->setParameter("remark", '快来抢！');//备注信息return_code
        $postXml = $this->create_hongbao_xml($mkey);
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendgroupredpack';
        $responseXml = $this->curl_post_ssl($url, $postXml,$token);
        $responseObj = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
		return $responseObj->err_code;
        
		
	}
	
	//企业红包
	public function companypay($fee,$openid,$wish){
		//设置参数
		if(F('config_info','',DATA_ROOT)){
			$config_info = F('config_info','',DATA_ROOT);
		}else{
			$config_info = M('config') ->select();F('config_info',$config_info,DATA_ROOT);
		}
		$money = $fee * 100;
		$appid = $config_info[0]['appid'];
		$machid = $config_info[0]['machid'];
		$mkey = $config_info[0]['mkey'];
		$this->setParameter("nonce_str", $this->great_rand());//随机字符串，丌长于 32 位
        $this->setParameter("partner_trade_no", $machid.date('YmdHis').rand(1000, 9999));//订单号
        $this->setParameter("mchid", $machid);//商户号
        $this->setParameter("mch_appid", $appid);
        $this->setParameter("check_name", 'NO_CHECK');
        $this->setParameter("openid", $openid);//相对于医脉互通的openid
        $this->setParameter("amount", $money);//付款金额，单位分
        $this->setParameter("desc", $wish);//红包发放类型随机金额
        $this->setParameter("spbill_create_ip", '127.0.0.1');
        $postXml = $this->create_hongbao_xml($mkey);
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $responseXml = $this->curl_post_ssl($url, $postXml);
        $responseObj = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
		return $responseObj->return_code;
        
		
	}
	public function webhongbao($token,$openid,$money,$desc){
		//设置参数
		if(!S('hbconfig')){
			$config=M('wxconfig')->field("appid,machid,mkey")->where(" token = '$token' ")->select();
			S("hbconfig",$config);
		}else{
			$config=S('hbconfig');
		}
		
		$appid = $config[0][appid];
		$machid = $config[0][machid];
		$mkey = $config[0][mkey];
		$this->setParameter("nonce_str", $this->great_rand());//随机字符串，丌长于 32 位
        $this->setParameter("mch_billno", $machid.date('YmdHis').rand(1000, 9999));//订单号
        $this->setParameter("mch_id", $machid);//商户号
        $this->setParameter("wxappid", $appid);
        $this->setParameter("nick_name", '西瓜科技');//提供方名称
        $this->setParameter("send_name", $desc);//红包发送者名称
        $this->setParameter("re_openid", $openid);//相对于医脉互通的openid
        $this->setParameter("total_amount", $money);//付款金额，单位分
        $this->setParameter("min_value", $money);//最小红包金额，单位分
        $this->setParameter("max_value", $money);//最大红包金额，单位分
        $this->setParameter("total_num", 1);//红包収放总人数
        $this->setParameter("wishing", "恭喜发财");//红包祝福诧
        $this->setParameter("client_ip", '127.0.0.1');//调用接口的机器 Ip 地址
        $this->setParameter("act_name", '红包活动');//活劢名称
        $this->setParameter("remark", '快来抢！');//备注信息return_code
        $postXml = $this->create_hongbao_xml($mkey);
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
        $responseXml = $this->curl_post_ssl($url, $postXml,$token);
        $responseObj = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
		return $responseObj->return_code;
        
		
	}
	
	

	public function great_rand(){
        $str = '1234567890abcdefghijklmnopqrstuvwxyz';
		$t1="";
        for($i=0;$i<30;$i++){
            $j=rand(0,35);
            $t1 .= $str[$j];
        }
        return $t1;    
    }

    function setParameter($parameter, $parameterValue) {
		$this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
	}

	static function trimString($value){
		$ret = null;
		if (null != $value) {
			$ret = $value;
			if (strlen($ret) == 0) {
				$ret = null;
			}
		}
		return $ret;
	}


	function check_sign_parameters(){
		if($this->parameters["nonce_str"] == null || 
			$this->parameters["mch_billno"] == null || 
			$this->parameters["mch_id"] == null || 
			$this->parameters["wxappid"] == null || 
			$this->parameters["nick_name"] == null || 
			$this->parameters["send_name"] == null ||
			$this->parameters["re_openid"] == null || 
			$this->parameters["total_amount"] == null || 
			$this->parameters["max_value"] == null || 
			$this->parameters["total_num"] == null || 
			$this->parameters["wishing"] == null || 
			$this->parameters["client_ip"] == null || 
			$this->parameters["act_name"] == null || 
			$this->parameters["remark"] == null || 
			$this->parameters["min_value"] == null ||
			$this->parameters["mchid"] == null 
			)
		{
			return false;
		}
		return true;

	}
	/**
	*  例如：
	* 	appid：    wxd111665abv58f4f
	*	mch_id：    10000100
	*	device_info：  1000
	*	Body：    test
	*	nonce_str：  ibuaiVcKdpRxkhJA
	*	第一步：对参数按照 key=value 的格式，并按照参数名 ASCII 字典序排序如下：
	*	stringA="appid=wxd930ea5d5a258f4f&body=test&device_info=1000&mch_i
	*	d=10000100&nonce_str=ibuaiVcKdpRxkhJA";
	*	第二步：拼接支付密钥：
	*	stringSignTemp="stringA&key=192006250b4c09247ec02edce69f6a2d"
	*	sign=MD5(stringSignTemp).toUpperCase()="9A0A8659F005D6984697E2CA0A
	*	9CF3B7"
	 */
	protected function get_sign($mkey){
		define('PARTNERKEY',$mkey);
		try {
			if (null == PARTNERKEY || "" == PARTNERKEY ) {
				return "密钥不能为空！" . "<br>";
			}
			if($this->check_sign_parameters() == false) {   //检查生成签名参数
			  //echo "生成签名参数缺失！" . "<br>";
		    }
	
			ksort($this->parameters);
			$unSignParaString = $this->formatQueryParaMap($this->parameters, false);

			
			return $this->sign($unSignParaString,$this->trimString(PARTNERKEY));
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}

	}
	
	//生成红包接口XML信息
	function create_hongbao_xml($mkey,$retcode = 0, $reterrmsg = "ok"){
		 try {
		    $this->setParameter('sign', $this->get_sign($mkey));
		   
		    return  $this->arrayToXml($this->parameters);
		   
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}		

	}

	function formatQueryParaMap($paraMap, $urlencode){
		$buff = "";
		ksort($paraMap);
		foreach ($paraMap as $k => $v){
			if (null != $v && "null" != $v && "sign" != $k) {
			    if($urlencode){
				   $v = urlencode($v);
				}
				$buff .= $k . "=" . $v . "&";
			}
		}
		$reqPar;
		if (strlen($buff) > 0) {
			$reqPar = substr($buff, 0, strlen($buff)-1);
		}
		return $reqPar;
	}

	function sign($content, $key) {
	    try {
		    if (null == $key) {
			  return "签名key不能为空！" . "<br>";
		    }
			if (null == $content) {
			   throw new SDKRuntimeException("签名内容不能为空" . "<br>");
		    }
		    $signStr = $content . "&key=" . $key;
		
		    return strtoupper(md5($signStr));
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}


	function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
        	 if (is_numeric($val))
        	 {
        	 	$xml.="<".$key.">".$val."</".$key.">"; 

        	 }
        	 else{
        	 	$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
        	 } 
        }
        $xml.="</xml>";
        return $xml; 
    }



    function curl_post_ssl($url, $vars,$token, $second=30,$aHeader=array())
	{
		$ch = curl_init();
		//超时时间
		curl_setopt($ch,CURLOPT_TIMEOUT,$second);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		//这里设置代理，如果有的话
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);	
		
		//cert 与 key 分别属于两个.pem文件
		// curl_setopt($ch,CURLOPT_SSLCERT,__ROOT__.'/home/public/uploads/zhengshu/'.$token.'/apiclient_cert.pem');
		// curl_setopt($ch,CURLOPT_SSLKEY,__ROOT__.'/home/public/uploads/zhengshu/'.$token.'/apiclient_key.pem');
 	// 	curl_setopt($ch,CURLOPT_CAINFO,__ROOT__.'/home/public/uploads/zhengshu/'.$token.'/rootca.pem');
 		curl_setopt($ch,CURLOPT_SSLCERT,dirname(__FILE__).DIRECTORY_SEPARATOR.'zhengshu'.DIRECTORY_SEPARATOR.'apiclient_cert.pem');
 		curl_setopt($ch,CURLOPT_SSLKEY,dirname(__FILE__).DIRECTORY_SEPARATOR.'zhengshu'.DIRECTORY_SEPARATOR.'apiclient_key.pem');
 		curl_setopt($ch,CURLOPT_CAINFO,dirname(__FILE__).DIRECTORY_SEPARATOR.'zhengshu'.DIRECTORY_SEPARATOR.'rootca.pem');

	 
		if( count($aHeader) >= 1 ){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
		}
	 
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
		$data = curl_exec($ch);
		if($data){
			curl_close($ch);
			return $data;
		}
		else { 
			$error = curl_errno($ch);
			curl_close($ch);
			return false;
		}
	}



} 