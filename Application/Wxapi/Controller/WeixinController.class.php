<?php

//decode by http://www.yunlu99.com/
namespace Wxapi\Controller;

use Think\Controller;

class WeixinController extends Controller
{
    function __construct()
    {
        parent::__construct();
        if (F('config_info', '', DATA_ROOT)) {
            $config_info = F('config_info', '', DATA_ROOT);
        } else {
            $config_info = M('config')->select();
            F('config_info', $config_info, DATA_ROOT);
        }
        $this->appid     = $config_info[0]['appid'];
        $this->appsecret = $config_info[0]['appsecret'];
        $this->mkey      = $config_info[0]['mkey'];
        $this->mch_id    = $config_info[0]['machid'];
        //缓存access_token，两个小时过期
        if (S('access_token')) {
            $this->access_token = S('access_token');
        } else {
            $this->access_token = $this->get_access_token();
            S('access_token', $this->access_token, 1800);
        }
    }

    function objectToArray($e)
    {
        $e = (array)$e;
        foreach ($e as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $e[$k] = (array)$this->objectToArray($v);
            }
        }

        return $e;
    }

    //生成推广二维码,返回二维码图片media_id
    function get_qr_path_new($user_id, $scene_id = null)
    {
        //查询是否已有二维码，得到scene_id
        //增加一条信息
        $data                = array();
        $data['user_id']     = $user_id;
        $data['update_time'] = time();
        $this->get_qr($user_id);
        $nickname = $this->save_head_pic($user_id);
        $qrimg    = A("Wxapi/Qrimg");
        $qr_path  = $data['qr_path'] = $qrimg->index($user_id, $nickname);
        if (file_exists($qr_path)) {
            // $media=$this->media_pic($qr_path);
            // $data['created_at'] = $media['created_at'];
            // $data['media_id'] = $media_id = $media['media_id'];
            if ($scene_id == null) {
                M('qrcode')->add($data);
            } else {
                M('qrcode')->where(" scene_id = '{$scene_id}' ")->save($data);
            }
            //return $media['media_id'];
        }
    }

    //更新推广二维码,返回二维码图片media_id
    function get_qr_path($user_id, $token, $info)
    {
        //查询是否已有二维码，得到scene_id
        //增加一条信息
        $data                = array();
        $id                  = $info[0]['id'];
        $scene_id            = $info[0]['scene_id'];
        $data['update_time'] = time();
        $data['qr_img']      = $this->get_qr($scene_id, $token);
        $nickname            = $this->save_head_pic($user_id, $token);
        $qrimg               = A("Wxapi/Qrimg");
        $qr_path             = $data['qr_path'] = $qrimg->index($scene_id, $user_id, $nickname, $token);
        if ($qr_path) {
            M('qr')->where("id = '{$id}' ")->save($data);
        }
        $media = $this->media_pic($qr_path);

        return $media;
    }

    //获取临时二维码
    function get_qr($scene_id)
    {
        $url    = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $this->access_token;
        $data   = '{
			"expire_seconds": 2592000, 
			"action_name": "QR_SCENE", 
			"action_info": {
				"scene": {
					"scene_id": ' . $scene_id . '
				}
			}
		}';
        $res    = $this->http_request($url, $data);
        $result = json_decode($res, true);
        $ticket = $result['ticket'];
        $surl   = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $ticket;
        $ress   = $this->http_request($surl);
        if ($ress['errcode'] == '45009') {
            return '今日创建二维码已超出1w个，超过上限，请明天再试。';
            exit;
        }
        if (!is_dir('Public/qrimg/')) {
            mkdir('Public/qrimg/');
        }
        $file_name = 'Public/qrimg/' . $scene_id . '.jpg';
        file_put_contents('Public/qrimg/' . $scene_id . '.jpg', $ress);

        return $file_name;
    }

    //获取永久二维码
    function get_qr_limit($scene_id, $token)
    {
        $url    = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $this->access_token;
        $data   = '{ 
			"action_name": "QR_LIMIT_SCENE", 
			"action_info": {
				"scene": {
					"scene_id": ' . $scene_id . '
				}
			}
		}';
        $res    = $this->http_request($url, $data);
        $result = json_decode($res, true);
        $ticket = $result['ticket'];
        $surl   = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $ticket;
        $ress   = $this->http_request($surl);
        if (!is_dir('Public/qrimg/' . $token)) {
            mkdir('Public/qrimg/' . $token);
        }
        $file_name = 'Public/qrimg/' . $token . '/' . $scene_id . '.jpg';
        file_put_contents('Public/qrimg/' . $token . '/' . $scene_id . '.jpg', $ress);

        return $file_name;
    }

    //微信支付加密字符串
    public function paysign($prepay_id)
    {
        //timeStamp, nonceStr, package, signType
        $timeStamp = time();
        $nonceStr  = $this->createNonceStr();
        $string    = "appId=" . $this->appid . "&nonceStr=" . $nonceStr . "&package=prepay_id=" . $prepay_id . "&signType=MD5&timeStamp=" . $timeStamp;
        $res       = md5($string . "&key=" . $this->mkey);
        $arr       = array("timeStamp" => $timeStamp, "appid" => $this->appid, "nonceStr" => $nonceStr, "prepay_id" => $prepay_id, "paySign" => strtoupper($res));

        return $arr;
    }

    //获取预支付交易会话标识，有效期两个小时
    function get_prepay_id($openid, $total_fee, $out_trade_no, $notify_url, $pay_id)
    {
        $nonce_str = $this->createNonceStr();
        $sign      = $this->signjiami($openid, $nonce_str, $total_fee, $out_trade_no, $notify_url, $pay_id);
        $url       = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $data      = "<xml>\r\n\t\t   <appid>" . $this->appid . "</appid>\r\n\t\t   <attach>" . $pay_id . "</attach>\r\n\t\t   <body>商城消费</body>\r\n\t\t   <mch_id>" . $this->mch_id . "</mch_id>\r\n\t\t   <nonce_str>" . $nonce_str . "</nonce_str>\r\n\t\t   <notify_url>" . $notify_url . "</notify_url>\r\n\t\t   <openid>" . $openid . "</openid>\r\n\t\t   <out_trade_no>" . $out_trade_no . "</out_trade_no>\r\n\t\t   <spbill_create_ip>14.23.150.211</spbill_create_ip>\r\n\t\t   <total_fee>" . $total_fee . "</total_fee>\r\n\t\t   <trade_type>JSAPI</trade_type>\r\n\t\t   <sign>" . $sign . "</sign>\r\n\t\t</xml>";
        $result    = $this->http_request($url, $data);
        //dump($result);exit;
        $postObj   = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
        $prepay_id = trim($postObj->prepay_id);

        return $prepay_id;
    }

    public function signjiami($openid, $nonce_str, $total_fee, $out_trade_no, $notify_url, $pay_id)
    {
        //$key = "qazwsxedcrfvtgbyhnujmikolpqazwsx";
        $string1 = "appid=" . $this->appid . "&attach=" . $pay_id . "&body=商城消费&mch_id=" . $this->mch_id . "&nonce_str=" . $nonce_str . "&notify_url=" . $notify_url . "&openid=" . $openid . "&out_trade_no=" . $out_trade_no . "&spbill_create_ip=14.23.150.211&total_fee=" . $total_fee . "&trade_type=JSAPI";
        $result  = md5($string1 . "&key=" . $this->mkey);

        return strtoupper($result);
    }

    //创建自定义菜单
    public function send_menu($data)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $this->access_token;
        $res = $this->http_request($url, $data);

        return json_decode($res, true);
    }

    //获取永久素材列表
    public function get_media_list()
    {
        $url  = "https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=" . $this->access_token;
        $data = '{
			"type":"news",
			"offset":0,
			"count":20
		}';
        $res  = $this->http_request($url, $data);

        return json_decode($res, true);
    }

    //获取永久素材
    public function get_media($media_id)
    {
        $url  = "https://api.weixin.qq.com/cgi-bin/material/get_material?access_token=" . $this->access_token;
        $data = '{
			"media_id":"' . $media_id . '"
		}';
        $res  = $this->http_request($url, $data);

        return json_decode($res, true);
    }

    //保存用户头像到本地
    function save_head_pic($user_id)
    {
        $userinfo = M('users')->where(" user_id = '{$user_id}' ")->select();
        $url      = $userinfo[0]['headimgurl'];
        $res      = $this->http_request($url);
        if (!is_dir('Public/head_pic/')) {
            mkdir('Public/head_pic/');
        }
        file_put_contents('Public/head_pic/' . $user_id . '.jpg', $res);

        return $userinfo[0]['nickname'];
    }

    //上传图片素材
    function media_pic($filepath)
    {
        $url      = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=" . $this->access_token . "&type=image";
        $filedata = array("media" => "@" . realpath($filepath));
        $res      = $this->http_request($url, $filedata);
        $result   = json_decode($res, true);

        return $result;
    }

    //客服接口发送图片消息
    function send_pic($openid, $media_id)
    {
        $url      = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $this->access_token;
        $data     = '{
			"touser":"' . $openid . '",
			"msgtype":"image",
			"image":
			{
			  "media_id":"' . $media_id . '"
			}
		}';
        $res_json = $this->curl_grab_page($url, $data);
        $res      = json_decode($res_json);
    }

    //客服接口发送图文消息
    function send_news($data)
    {
        $url      = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $this->access_token;
        $res_json = $this->curl_grab_page($url, $data);
        $res      = json_decode($res_json);
        $result   = $this->objectToArray($res);

        return $result;
    }

    //获取关注用户openid列表
    function get_openid_list($next_openid)
    {
        $url      = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=" . $this->access_token . "&next_openid=" . $next_openid;
        $res_json = $this->http_request($url);
        $res      = json_decode($res_json);

        return $res;
    }

    //获取关注用户分组列表
    function get_group()
    {
        $url      = "https://api.weixin.qq.com/cgi-bin/groups/get?access_token=" . $this->access_token;
        $res_json = $this->http_request($url);
        $res      = json_decode($res_json);

        return $res;
    }

    //查询关注用户所属分组
    function get_user_group($openid)
    {
        $url      = "https://api.weixin.qq.com/cgi-bin/groups/getid?access_token=" . $this->access_token;
        $data     = '{
			"openid":"' . $openid . '"
		}';
        $res_json = $this->http_request($url, $data);
        $res      = json_decode($res_json);

        return $res;
    }

    //客服接口发送文字消息
    function send_word($openid, $text)
    {
        $url      = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $this->access_token;
        $data     = '{
			"touser":"' . $openid . '",
			"msgtype":"text",
			"text":
			{
			  "content":"' . $text . '"
			}
		}';
        $res_json = $this->curl_grab_page($url, $data);
        $res      = json_decode($res_json);
        $result   = $this->objectToArray($res);

        return $result;
    }

    //客服接口发送shipin消息
    function send_vedio($openid)
    {
        $url      = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $this->access_token;
        $data     = '{
			"touser":"' . $openid . '",
			"msgtype":"shortvideo",
			"shortvideo":
			{
			  "media_id":"XBaMrB9WY5aibNMt12xxi32ZLdpliPica1YCuKELkJbY-IddPvHC5800Mzpsqp5g",
			  "thumb_media_id":"",
			  "title":"",
			  "description":""
			}
		}';
        $res_json = $this->curl_grab_page($url, $data);
        $res      = json_decode($res_json);
        $result   = $this->objectToArray($res);

        return $result;
    }

    //通过地理位置信息确定省份市
    public function map_colation($x, $y, $type)
    {
        $url    = "http://api.map.baidu.com/geocoder/v2/?ak=2DSlGaOx3ttU4KXGLnY5uPBt&callback=&location=" . $x . "," . $y . "&output=json&pois=0";
        $result = $this->http_request($url);
        $res    = json_decode($result, true);
        if ($type == 0) {
            $ress = $res['result']["addressComponent"]["province"] . $res['result']["addressComponent"]["city"] . $res['result']["addressComponent"]["district"];
        } else if ($type == 1) {
            $ress = $res['result']["addressComponent"]["province"];
        } else {
            $ress = $res['result']["addressComponent"]["province"] . $res['result']["addressComponent"]["city"];
        }

        return $ress;
    }

    //获取access_token
    function get_access_token()
    {
        $url          = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->appid . "&secret=" . $this->appsecret;
        $res          = $this->http_request($url);
        $result       = json_decode($res, true);
        $access_token = $result['access_token'];

        return $access_token;
    }

    //获取用户基本信息
    public function get_user_info($openid)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $this->access_token . "&openid=" . $openid . "&lang=zh_CN";
        $res = $this->http_request($url);

        return json_decode($res, true);
    }

    //发送模版消息
    public function send_template($openid, $data)
    {
        $url  = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $this->access_token;
        $data = $data;
        $res  = $this->http_request($url, $data);

        return $res;
    }

    //获取卡券加密串
    function getcardsign($card_id)
    {
        $timestamp = time();
        //$card_id="p5NEduIHENjrtqqRlBC7B2ADCQTc";
        //$appid="wx8b3d2b12a1eb513f";
        //$appsecret="4c481e4f00e36ece0c799b794d6aef65";
        $api_ticket = "E0o2-at6NcC2OsJiQTlwlDfrwDFqOHOAl9wKFYh5GPdJmsjot_hP0vE6kxSBMl6uxmVgO4Ek9h8lR80bROv4uQ";
        $nonceStr   = $this->createNonceStr();
        $string     = $timestamp . $this->appsecret . $card_id;
        $signature  = sha1($string);
        $res        = array("timestamp" => $timestamp, "nonceStr" => $nonceStr, "sign" => $signature);

        return $res;
    }

    //获得JS签名包
    public function getSignPackage()
    {
        $jsapiTicket = $this->getJsApi();
        $protocol    = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";
        $url         = "{$protocol}{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        $timestamp   = time();
        $nonceStr    = $this->createNonceStr();
        $string      = "jsapi_ticket={$jsapiTicket}&noncestr={$nonceStr}&timestamp={$timestamp}&url={$url}";
        $signature   = sha1($string);
        $signPackage = array("appId" => $this->appid, "nonceStr" => $nonceStr, "timestamp" => $timestamp, "url" => $url, "signature" => $signature, "rawString" => $string);

        return $signPackage;
    }

    //获得JS API的ticket 微信卡券ticket
    public function getJsApiTicket()
    {
        //$ticket="sM4AOVdWfPE4DxkXGEs8VDl5qjwtv5XbtLzgtl30YSlyQYtVSeLi4sUvDPa_cG9y-I9Y1mlyn29FEv2zvZrABw";
        if (S('getJsApiTicket')) {
            $ticket = S('getJsApiTicket');
        } else {
            $url    = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=" . $this->access_token;
            $res    = $this->http_request($url);
            $result = json_decode($res, true);
            $ticket = $result['ticket'];
            S('getJsApiTicket', $ticket, 7000);
        }

        return $ticket;
    }

    //获得JS API的ticket 普通ticket
    public function getJsApi()
    {
        //$ticket="sM4AOVdWfPE4DxkXGEs8VDl5qjwtv5XbtLzgtl30YSlyQYtVSeLi4sUvDPa_cG9y-I9Y1mlyn29FEv2zvZrABw";
        if (S('getJsApi')) {
            $ticket = S('getJsApi');
        } else {
            $url    = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . $this->access_token . "&type=jsapi";
            $res    = $this->http_request($url);
            $result = json_decode($res, true);
            $ticket = $result['ticket'];
            S('getJsApi', $ticket, 7000);
        }

        return $ticket;
    }

    //生成长度16的随机字符串
    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str   = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        return "z" . $str;
    }

    //https请求(支持GET和POST)
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
        //var_dump(curl_error($curl));
        curl_close($curl);

        return $output;
    }

    function curl_grab_page($url, $data, $proxy = '', $proxystatus = '', $ref_url = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($proxystatus == 'true') {
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($ref_url)) {
            curl_setopt($ch, CURLOPT_HEADER, TRUE);
            curl_setopt($ch, CURLOPT_REFERER, $ref_url);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        ob_start();

        return curl_exec($ch);
        ob_end_clean();
        curl_close($ch);
        unset($ch);
    }
}
