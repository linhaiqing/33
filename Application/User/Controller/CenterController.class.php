<?php
namespace User\Controller;
use Think\Controller;
class CenterController extends Controller{
    function __construct(){
        parent::__construct();
        session('user_id',2);
        //session(null);exit;
        $this->user_id = session('user_id');
        $weixin = A("Wxapi/Weixin");
        $signPackage=$weixin->getSignPackage();
        $this->assign("signPackage",$signPackage);
    }
    public function index(){

        // if(!is_dir('Public/daijin/')){
            // mkdir('Public/daijin/');
        // }
        // import("Org.Util.Erweima");
        // $value="http://".$_SERVER['SERVER_NAME'].U('/Home/wap/daijin')."?user_id=".$this->user_id;dump($value);
        // $errorCorrectionLevel = "L";
        // $matrixPointSize = "6";
        // \QRcode::png($value, 'Public/daijin/'.$this->user_id.'.png', $errorCorrectionLevel, $matrixPointSize,1,true);
        // exit;


                        // $wechat = A("User/Wechat");
                        // $openid = 'ou7U9wErjwAu3_219DnfqejFNfrs';
                        // $a = $wechat -> contact_user(1000,$openid);dump($a);exit;
        if(session('user_id')){
            $user_id = session('user_id');
        }else{
            $openid = $this->openid();
            if($openid == ''){
                echo '<div style="font-size:66px;text-align:center;margin-top:40%;">获取身份信息错误，请关闭重新打开</div>';exit;
            }
            $wechat = A("User/Wechat");
            $user_id = $wechat -> get_user_id($openid);
            var_dump($openid);
            var_dump($user_id);die;
            if($user_id == 0){
                echo '<div style="font-size:66px;text-align:center;margin-top:40%;">系统没有您的数据<br />请取消关注后重新关注</div>';exit;
            }
            session("user_id",$user_id);
        }
        $users = M('users');$hbrecord = M('hbrecord');$ztrecord = M('ztrecord');
        $user_info = $users -> getByUser_id($user_id);session('user_info',$user_info);
        $user_info['subscribe_time'] = date("Y-m-d H:i:s",$user_info['subscribe_time']);
        if($user_info['pid'] == 0){
            $user_info['append'] = '平台';
        }else{
            $user_info['append'] = $users -> getFieldByUser_id($user_info['pid'],'nickname');
        }
        if(F('daili_info','',DATA_ROOT)){
            $daili_info = F('daili_info','',DATA_ROOT);
        }else{
            $daili_info = M('daili_info') -> select();F('daili_info',$daili_info,DATA_ROOT);
        }
        switch($user_info['agent']){
            case 0:
            $user_info['agent']="普通会员";
            break;
            case 1:
            $user_info['agent']=$daili_info[0]['first_name'];
            break;
            case 2:
            $user_info['agent']=$daili_info[0]['second_name'];
            break;
            case 3:
            $user_info['agent']=$daili_info[0]['third_name'];
            break;
        }
        //查询会员关系
        $user_contact = M('user_contact');
        $num['frist_num'] = $user_contact -> where(" user_id = '$user_id' and level = 1 ") ->count();
        $num['second_num'] = $user_contact -> where(" user_id = '$user_id' and level = 2 ") ->count();
        $num['thrid_num'] = $user_contact -> where(" user_id = '$user_id' and level = 3 ") ->count();
        $num['four_num'] = $user_contact -> where(" user_id = '$user_id' and level = 4 ") ->count();
        $num['five_num'] = $user_contact -> where(" user_id = '$user_id' and level = 5 ") ->count();
        $num['six_num'] = $user_contact -> where(" user_id = '$user_id' and level = 6 ") ->count();
        $num['seven_num'] = $user_contact -> where(" user_id = '$user_id' and level = 7 ") ->count();
        $num['eight_num'] = $user_contact -> where(" user_id = '$user_id' and level = 8 ") ->count();
        $num['nine_num'] = $user_contact -> where(" user_id = '$user_id' and level = 9 ") ->count();
        $zt_num = $ztrecord -> where(" user_id = '$user_id' and is_true = 0 ") ->count();
        $hb_num = $hbrecord -> where(" user_id = '$user_id' and is_true = 0 ") ->count();
        $money1 = $hbrecord -> where(" user_id = '$user_id' and is_true = 1 ") ->sum('hongbao_fee');if($money1 == null){$money1 = 0;}
        $money2 = $ztrecord -> where(" user_id = '$user_id' and is_true = 1 ") ->sum('money');if($money2 == null){$money2 = 0;}
        $money = $money1*1+$money2*1;
        $this->assign("num",$num);
        $this->assign("money",$money);
        $this->assign("hb_allnum",$hb_allnum);
        $this->assign("hb_num",$hb_num);
        $this->assign("zt_num",$zt_num);
        $this->assign("userinfo",$user_info);
        $this->display();
    }

    public function updtuserinfo(){
        if(empty($this->user_id)){exit;}
        $arr = array();
        $users = M('users');
        $openid = $users -> getFieldByUser_id($this->user_id,'openid');
        $weixin = A("Wxapi/Weixin");
        $info = $weixin -> get_user_info($openid);
        $res = $users -> where(" user_id = '$this->user_id' ") -> save($info);
        if($res){
            $arr['success'] = 1;
            $arr['info'] = "已成功更新基本信息，请刷新页面";
        }else{
            $arr['success'] = 0;
            $arr['info'] = "未检测到您的信息变更，请在信息更改时更新";
        }

        echo json_encode($arr);
    }

    public function resetqr(){
        if(empty($this->user_id)){exit;}
        $arr = array();
        M('qrcode') -> where(" user_id = '$this->user_id' ") -> setField('update_time',0);
        $arr['success'] = 1;
        $arr['info'] = "重置成功！请点击二维码重新生成";
        echo json_encode($arr);
    }

    /**
     * 生成海报
     * @author haiqing.lin
     * @date   2020/1/16 0016
     */
    public function qr(){
        if(empty($this->user_id)){exit;}
        $arr = array();
        $user_info = M('users') -> field("user_id,agent") -> where("user_id = '$this->user_id' ") -> find();
        if($user_info['agent'] == 0){
            $arr['info'] = "您还未成为代理，不能生成推广海报，前去购买代理吧！";
        }else{
            $weixin = A('Wxapi/Weixin');
            $qrcode_info = M('qrcode') -> getByUser_id($this->user_id);
            if(file_exists('Public/qr_path/'.$user_info['user_id'].'.jpg')){
                $media_id = $qrcode_info['media_id'];
                $time2 = time() - $qrcode_info['update_time'];
                if($time2 >= 2592000 || $qrcode_info['update_time'] == 0){
                    //参数二维码超过了30天，需重新生成
                    $weixin -> get_qr_path_new($this->user_id,$qrcode_info['scene_id']);
                }
            }else{

                $weixin -> get_qr_path_new($this->user_id,$qrcode_info['scene_id']);
            }
            $arr['success'] = 1;
        }
        echo '<pre>';
        print_r($arr);
        die();
        echo json_encode($arr);
    }
    public function qr_show(){
        if($_GET['from_id']){
            if(file_exists('Public/qr_path/'.$_GET['from_id'].'.jpg')){
                $this->assign('user_id',$_GET['from_id']);
                $this->display(qr);
            }else{
                echo '<div style="font-size:66px;text-align:center;margin-top:40%;">糟糕！<br />该用户还未生成个人海报,提示他生成个人海报噢！</div>';
            }
            exit;
        }
        $user_info = M('users') -> field("user_id,agent") -> where("user_id = '$this->user_id' ") -> find();
        if($user_info['agent'] == 0){
            $this -> error("您还未成为代理，不能生成推广海报，前去购买代理吧！",U('/Agent/Buy/index'),5);exit;
        }else{
            $this->assign('user_id',$this->user_id);
            $this->display(qr);
        }

    }

    public function question(){
        $question = M('question');
        if($_POST['title'] != null){
            $data = array(
                'title'=>$_POST['title'],
                'time'=>time(),
                'user_id'=>$this->user_id
            );
            $res = $question -> add($data);
            if($res){$this->success('已提交，请等待客服回执');}else{$this->error('提交失败，请重试');}
            exit;
        }
        $this -> display();
    }
    public function question_ajax(){
        $question = M('question');
        $user = M('users');
        $where = array();
        if($_GET['user_id'] != null){$where['user_id'] = $_GET['user_id'];}else{$where['is_true'] = 1;}
        if($_GET['keyword'] != null){$where['title'] = array('like','%'.$_GET['keyword'].'%');}
        $pagecount = 10;
        $count = $question->where($where) -> count();
        $Page = new \Think\Pageajax($count,$pagecount);
        $info = $question->where($where) ->limit($Page->firstRow.','.$Page->listRows)->order("time desc") -> select();
        $show = $Page->show();
        $this->assign('page',$show);
        foreach($info as $k=>$v){
            $info[$k]['time'] = date("Y年m月d日",$v['time']);
            $info[$k]['nickname'] = $user -> getFieldByUser_id($v['user_id'],'nickname');
            if($_GET['keyword'] != null){
                $info[$k]['title'] = str_ireplace($_GET['keyword'],"<span class='span'>".$_GET['keyword']."</span>",$v['title']);
            }

        }
        $this->assign("info",$info);
        $this->assign('empty','<div style="color:#999;font-size:14px;margin:20px 0;text-align:center;">还没有用户提交过问题噢！赶快提交你的问题吧</div>');
        $this -> display();
    }

    public function order(){
        $shop_order = M('shop_order');$shop_order_detail = M('shop_order_detail');
        $pagecount = 5;
        $count = $shop_order ->where(" user_id = '$this->user_id' ")-> count();
        $Page = new \Think\Page($count,$pagecount);
        $order_info = $shop_order->where(" user_id = '$this->user_id' ")->limit($Page->firstRow.','.$Page->listRows)->order("order_id desc") -> select();
        $show = $Page->show();
        $this->assign('page',$show);
        foreach($order_info as $k=>$v){
            $pay_id = $v['pay_id'];
            $order_info[$k]['info'] = $shop_order_detail -> where(" pay_id = '$pay_id' ") -> select();
        }
        $this->assign('order_empty','<div style="text-align:center;margin-top:30%">暂时没有订单数据</div>');
        $this ->assign("order_info",$order_info);
        $this -> display();
    }

    public function group(){
        $state = $_GET['group_id'];
        $this->assign('state',$state);
        $this->display();
    }
    public function group_ajax(){
        $users = M('users');$user_contact = M('user_contact');
        $state = $_GET['state'];
        $info = $user_contact -> field("children_id")-> where(" user_id = '$this->user_id' and level = '$state' ")->order("time asc") -> select();
        foreach($info as $k=>$v){
            $id = $v['children_id'];
            $info[$k] = $users -> where(" user_id = '$id' ") -> find();
        }
        $info = $this->infoto($info);
        $this->assign('first_info',$info);
        $this->assign('type',$_GET['type']);
        $this->assign('state',$state*1+1);
        $this->assign('empty','<div style="text-align:center;color:#888;">该层级没有会员</div>');
        $this->assign('more',count($info));
        $this->display();

    }
    function chat_all(){
        $chat_all = M('chat_all');$user = M('users');$chat = M('chat');
        $info = $chat_all -> where(" user_id = '$this->user_id' ") -> select();
        foreach($info as $k=>$v){
            $from_user_id = $v['from_user_id'];
            $info[$k]['user'] = $user -> field("nickname,headimgurl") -> getByUser_id($from_user_id);
            $get_time = F('chat/'.$this->user_id.'/'.$from_user_id,'',DATA_ROOT);
            if(!$get_time){$get_time = 0;}
            $info[$k]['num'] = $chat -> where(" from_user_id = '$from_user_id' and to_user_id = '$this->user_id' and time > '$get_time' ") -> count();
        }
        //dump($info);
        $this -> assign('info',$info);
        $this->display();
    }
    function chat(){
        //$arr = F('chat','',DATA_ROOT.'/chat/');dump($arr);
        if(!$_GET['to_user_id']){echo '<div style="font-size:66px;text-align:center;margin-top:40%;">您都干了些什么~重来</div>';exit;}
        if(!$this->user_id){echo '<div style="font-size:66px;text-align:center;margin-top:40%;">您都干了些什么~重来</div>';exit;}
        $to_user_id = $_GET['to_user_id'];
        $users = M('users');$chat = M('chat');
        $get_time = F('chat/'.$this->user_id.'/'.$to_user_id,'',DATA_ROOT);
        if(!$get_time){$get_time = 0;}
        $chat_record1 = $chat -> where(" from_user_id = '$to_user_id' and to_user_id = '$this->user_id' and time <= '$get_time' ") -> order("time desc") ->limit("4") -> select();
        $chat_record2 = $chat -> where(" from_user_id = '$this->user_id' and to_user_id = '$to_user_id' ") -> order("time desc") ->limit("4") -> select();
        $chat_record = array_merge_recursive($chat_record1,$chat_record2);
        array_multisort($chat_record);
        foreach($chat_record as $k=>$v){
            $chat_record[$k]['time'] = date("m-d H:i:s",$v['time']);
        }
        $to_userinfo = $users ->field("user_id,nickname,headimgurl") -> getByUser_id($to_user_id);//dump($to_userinfo);
        $headimgurl = $users -> getFieldByUser_id($this->user_id,'headimgurl');
        $this -> assign('chat_record',$chat_record);
        $this -> assign('to_userinfo',$to_userinfo);
        $this -> assign('headimgurl',$headimgurl);
        $this -> display();
    }

    function chat_send(){
        //处理发送过来的数据包,并存储
        $chat = M('chat');$chat_all = M('chat_all');
        //查询是否创建了会话记录
        $to_user_id = $_POST['to_user_id'];
        $res = $chat_all -> where(" from_user_id = '$this->user_id' and user_id = '$to_user_id' ") -> find();
        if($res == null){
            $chat_all -> add(array('from_user_id'=>$this->user_id,'user_id'=>$to_user_id));
        }
        $data = array('from_user_id'=>$this->user_id,'to_user_id'=>$_POST['to_user_id'],'time'=>time(),'text'=>$_POST['text']);
        $chat -> add($data);
        //判断是否是今天发给对方的第一次请求
        $time1 = strtotime(date("Y-m-d",time()));
        $where = array('from_user_id'=>$this->user_id,'to_user_id'=>$_POST['to_user_id']);
        $today_num = $chat -> where($where) -> where(" time > '$time1' ") -> count();
        if($today_num == 1){$this->send_template($_POST['to_user_id'],$_POST['text'],session('user_info')['nickname']);}
        $arr = array();
        echo json_encode($arr);
    }
    function chat_get(){
        //记录当前请求时间，之后查询改时间后的信息
        $arr = array();

        $chat = M('chat');
        $get_time = F('chat/'.$this->user_id.'/'.$_POST['from_user_id'],'',DATA_ROOT);
        if(!$get_time){$get_time = 0;}
        $where = array('to_user_id'=>$this->user_id,'from_user_id'=>$_POST['from_user_id']);
        $chat_get = $chat -> field("text,time")->where($where)->where(" time > '$get_time' ") -> order('time asc') -> find();
        if($chat_get != null){
            $arr['text'] = $chat_get['text'];$arr['time'] = date("H:i:s",$chat_get['time']);$arr['success'] = 1;
            F('chat/'.$this->user_id.'/'.$_POST['from_user_id'],$chat_get['time'],DATA_ROOT);
        }
        echo json_encode($arr);
    }

    function send_template($user_id,$text,$nickname){
        $template_info = F('template_info','',DATA_ROOT);
        $openid = M('users') -> getFieldByUser_id($user_id,'openid');
        $weixin = A("Wxapi/Weixin");
        $tem_data = '{
           "touser":"'.$openid.'",
           "template_id":"'.$template_info['fuwu_done_template_id'].'",
           "url":"http://'.$_SERVER['SERVER_NAME'].U('/User/Center/').'",           
           "data":{
                   "first": {
                       "value":"您的朋友'.$nickname.'给你发来了一条信息\n",
                       "color":"'.$template_info['fuwu_done_top'].'"
                   },
                   "keyword1":{
                       "value":"会员聊天会话邀请",
                       "color":"'.$template_info['fuwu_done_text'].'"
                   },
                   "keyword2": {
                       "value":"'.$text.'",
                       "color":"'.$template_info['fuwu_done_text'].'"
                   },
                   "keyword3": {
                       "value":"'.date("Y-m-d H:i:s",time()).'",
                       "color":"'.$template_info['fuwu_done_text'].'"
                   },
                   "remark":{
                       "value":"\n点击查看详情",
                       "color":"'.$template_info['fuwu_done_top'].'"
                   }
           }
       }';
        $c = $weixin ->send_template($openid,$tem_data);
    }

    public function broke(){
        // $hongbao = A('Wxapi/Hongbao');
        // $res = $hongbao -> index('0.5','oBm-YuEZ4x23soDkaxhgd5C91Qgg','nihaodailifei');$ress = $this->objectToArray($res);dump($ress);
        $broke_record = M('broke_record');
        $pagecount = 5;
        $count = $broke_record ->where(" user_id = '$this->user_id' ")-> count();
        $Page = new \Think\Page($count,$pagecount);
        $broke_info = $broke_record->where(" user_id = '$this->user_id' ")->limit($Page->firstRow.','.$Page->listRows)->order("time desc") -> select();
        $show = $Page->show();
        $this->assign('page',$show);
        foreach($broke_info as $k=>$v){
            $broke_info[$k]['time'] = date("Y-m-d H:i:s",$v['time']);
        }
        $this->assign('broke_record',$broke_info);
        $user_info = M('users') -> getByUser_id($this->user_id);
        $this->assign('user_info',$user_info);
        $empty = '<div style="color:red;text-align:center;">您至今还没有收入哦！</div>';
        $this->assign('empty',$empty);

        $this->display();
    }

    public function broke_tixian(){
        $money = intval($_POST['money']);if($money <= 0 ){exit;}
        //查询用户剩余余额
        $users = M('users');
        $user_info = $users -> getByUser_id($this->user_id);
        $arr = array();
        if($money > $user_info['shop_income']){
            $arr['info'] = '提现金额高出剩余收入余额，请更改金额重试';
        }elseif($money <1){
            $arr['info'] = '提现金额低于1元，请更改金额重试';
        }elseif($money > 200){
            $arr['info'] = '提现金额单次不能高于200元，请更改金额重试';
        }else{
            //处理申请
            //调用红包接口
            $Hongbao = A('Wxapi/Hongbao');
            $res = $Hongbao -> index($money,$user_info['openid'],'商城佣金提现');
            $ress = $this->objectToArray($res);
            if($ress['result_code'] == 'SUCCESS'){
                //发放成功，记录提现操作
                $broke_record = M('broke_record');
                $user_data = array('shop_income'=>intval($user_info['shop_income']-$money),'shop_outcome'=>intval($user_info['shop_outcome']+$money));
                $users -> where(" user_id = '$this->user_id' ") -> save($user_data);
                $record_data = array('user_id'=>$this->user_id,'desc'=>'申请提现成功，扣除收入','fee'=>$money,'type'=>1,'time'=>time());
                $broke_record -> add($record_data);
                $arr['success'] = 1;
            }else{
                $arr['info'] = $ress['return_msg'];
            }
        }

        echo json_encode($arr);
    }
    public function zthongbao(){

        $hbrecord = M('ztrecord');
        $first_info = $hbrecord -> where(" user_id = '$this->user_id' ")->count();
        $this->assign('first_num',$first_info);
        $this->display();
    }
    public function zthongbao_ajax(){
        $hbrecord = M('ztrecord');
        $where = array('user_id'=>$this->user_id);
        $pagecount = 5;
        $count = $hbrecord->where($where) -> count();
        $Page = new \Think\Pageajax($count,$pagecount);
        $first_info = $hbrecord->where($where) ->limit($Page->firstRow.','.$Page->listRows)->order("id desc") -> select();
        $show = $Page->show();
        $this->assign('page',$show);

        $this->assign('count',$count);
        $this->assign('empty','<div style="color:#999;font-size:14px;margin:20px 0;text-align:center;">您还没有得到该级别红包噢~</div>');
        $first_info = $this->hbinfoto($first_info);
        $this->assign('first_info',$first_info);
        $this->display();
    }
    public function hongbao(){
        //$Hongbao = A("Wxapi/Hongbao");
        //$res = $Hongbao -> index(1,'oBm-YuEZ4x23soDkaxhgd5C91Qgg','nihaodailifei');$ress = $this->objectToArray($res);dump($ress);
        //$res = $Hongbao -> companypay(1,'oBm-YuEZ4x23soDkaxhgd5C91Qgg','nihaodailifei');$ress = $this->objectToArray($res);dump($ress);
        //$resss = $Hongbao -> index(1,'oBm-YuEZ4x23soDkaxhgd5C91Qgg','nihaodailifei');$ressa = $this->objectToArray($resss);dump($ressa);
        //$res = $Hongbao -> companypay(1,'oBm-YuEZ4x23soDkaxhgd5C91Qgg','nihaodailifei');$ress = $this->objectToArray($res);dump($ress);

        //if($this->user_id != 1 ){echo '<div style="font-size:66px;text-align:center;margin-top:40%;">正在升级，请稍候再试</div>';exit;}
        $hbrecord = M('hbrecord');
        $first_info = $hbrecord -> where(" user_id = '$this->user_id' and type = 1 ")->count();
        $second_info = $hbrecord -> where(" user_id = '$this->user_id' and type = 2 ")->count();
        $thrid_info = $hbrecord -> where(" user_id = '$this->user_id' and type = 3 ")->count();
        $this->assign('first_num',$first_info);
        $this->assign('second_num',$second_info);
        $this->assign('thrid_num',$thrid_info);
        $this->display();
    }
    public function hongbao_ajax(){
        $hbrecord = M('hbrecord');
        $where = array('user_id'=>$this->user_id,'type'=>$_GET['type']);
        //判断用户代理级别
        $user_info = $this->nameinfoto(session('user_info'));
        if(session('user_info')['agent'] < $_GET['type']){
            echo '<div style="text-align:center;margin:20px;">您当前是'.$user_info['agent_name'].'，不能得到这一级别的红包，请升级代理权限</div>';exit;
        }

        $pagecount = 5;
        $count = $hbrecord->where($where) -> count();
        $Page = new \Think\Pageajax($count,$pagecount);
        $first_info = $hbrecord->where($where) ->limit($Page->firstRow.','.$Page->listRows)->order("hb_id desc") -> select();
        $show = $Page->show();
        $this->assign('page',$show);

        $this->assign('count',$count);
        $this->assign('empty','<div style="color:#999;font-size:14px;margin:20px 0;text-align:center;">您还没有得到该级别红包噢~</div>');
        $first_info = $this->hbinfoto($first_info);
        $this->assign('first_info',$first_info);
        $this->display();
    }
    public function send_zthongbao(){
        $wish = $_POST['wish'];
        $id = $_POST['id'];
        $hbrecord = M('ztrecord');
        $hb_info = $hbrecord -> where(" id = '$id' and user_id = '$this->user_id' ") -> find();
        if($hb_info == null){
            $arr['success'] = 0;$arr['err_info'] = "红包记录不存在，请确认";
        }elseif($hb_info['is_true'] == 1){
            $arr['success'] = 1;$arr['err_info'] = "红包已发放，请确认";
        }else{
            $openid = M('users') -> getFieldByUser_id($this->user_id,'openid');
            $Hongbao = A("Wxapi/Hongbao");
            //红包接口
            $res = $Hongbao -> index($hb_info['money'],$openid,$wish);
            $ress = $this->objectToArray($res);
            if($ress['result_code'] == 'SUCCESS'){
                $hbrecord -> where(" id = '$id' ") -> setField("is_true",1);
                $arr['success'] = 1;
            }else{
                $arr['success'] = 0;$arr['err_info'] = $ress['return_msg'];
            }
        }
        echo json_encode($arr);
    }
    public function send_hongbao(){
        $wish = $_POST['wish'];
        $hb_id = $_POST['id'];
        $hbrecord = M('hbrecord');
        $hb_info = $hbrecord -> where(" hb_id = '$hb_id' and user_id = '$this->user_id' ") -> find();
        if($hb_info == null){
            $arr['success'] = 0;$arr['err_info'] = "红包记录不存在，请确认";
        }elseif($hb_info['is_true'] == 1){
            $arr['success'] = 1;$arr['err_info'] = "红包已发放，请确认";
        }else{
            $openid = M('users') -> getFieldByUser_id($this->user_id,'openid');
            $Hongbao = A("Wxapi/Hongbao");
            //企业付款接口 $ress[0] != 'SUCCESS'
            //$res = $Hongbao -> companypay($hb_info['hongbao_fee'],$openid,$wish);
            //红包接口
            if($hb_info['last_fee'] > 0){
                //第二次发差额
                $res = $Hongbao -> index($hb_info['last_fee'],$openid,$wish);
                $ress = $this->objectToArray($res);
                if($ress['result_code'] == 'SUCCESS'){
                    $hbrecord -> where(" hb_id = '$hb_id' ") -> setField("is_true",1);
                    $arr['success'] = 1;
                }else{
                    $arr['success'] = 0;$arr['err_info'] = $ress['return_msg'];
                }
            }else{
                if($hb_info['hongbao_fee'] > 200){
                //先放放一次200，然后再继续发放
                    $res = $Hongbao -> index(200,$openid,$wish);
                    $ress = $this->objectToArray($res);
                    if($ress['result_code'] == 'SUCCESS'){
                        $hbrecord -> where(" hb_id = '$hb_id' ") -> setField("last_fee",$hb_info['hongbao_fee']-200);
                        $arr['success'] = 2;$arr['info'] = '已发放200元红包，您可以继续领取';
                    }else{
                        $arr['success'] = 0;$arr['err_info'] = $ress['return_msg'];
                    }
                }else{
                    $res = $Hongbao -> index($hb_info['hongbao_fee'],$openid,$wish);
                    $ress = $this->objectToArray($res);
                    if($ress['result_code'] == 'SUCCESS'){
                        $hbrecord -> where(" hb_id = '$hb_id' ") -> setField("is_true",1);
                        $arr['success'] = 1;
                    }else{
                        $arr['success'] = 0;$arr['err_info'] = $ress['return_msg'];
                    }
                }
            }


        }

        echo json_encode($arr);
    }
    private function objectToArray($e){
        $e=(array)$e;
        foreach($e as $k=>$v){
            if( gettype($v)=='resource' ) return;
            if( gettype($v)=='object' || gettype($v)=='array' )
                $e[$k]=(array)$this->objectToArray($v);
        }
        return $e;
    }
    private function infoto($info){
        if(F('daili_info','',DATA_ROOT)){
            $daili_info = F('daili_info','',DATA_ROOT);
        }else{
            $daili_info = M('daili_info') -> select();F('daili_info',$daili_info,DATA_ROOT);
        }
        foreach($info as $k=>$v){
            switch($v['agent']){
                case 0:
                $info[$k]['agent'] = "普通会员";
                break;
                case 1:
                $info[$k]['agent'] = $daili_info[0]['first_name'];
                break;
                case 2:
                $info[$k]['agent'] = $daili_info[0]['second_name'];
                break;
                case 3:
                $info[$k]['agent'] = $daili_info[0]['third_name'];
                break;
            }
        }
        return $info;
    }
    private function hbinfoto($info){
        if(F('daili_info','',DATA_ROOT)){
            $daili_info = F('daili_info','',DATA_ROOT);
        }else{
            $daili_info = M('daili_info') -> select();F('daili_info',$daili_info,DATA_ROOT);
        }
        $users = M('users');
        foreach($info as $k=>$v){
            $info[$k]['nickname'] = $users -> getFieldByUser_id($v[from_user_id],'nickname');
            switch($v['type']){
                case 1:
                $info[$k]['type'] = $daili_info[0]['first_name'];
                break;
                case 2:
                $info[$k]['type'] = $daili_info[0]['second_name'];
                break;
                case 3:
                $info[$k]['type'] = $daili_info[0]['third_name'];
                break;
            }
        }
        return $info;
    }


    function zhitui(){

        if($this->user_id ==null){redirect(index);exit;}
        $agent_nums = M('users') -> where(" pid = '$this->user_id' and agent > 0 ") -> count();
        $nums = M('users') -> where(" pid = '$this->user_id' ") -> count();
        $money = M('ztrecord') -> where(" user_id = '$this->user_id' ") -> sum('money');
        $this->assign('nums',$nums);
        $this->assign('agent_nums',$agent_nums);
        $this->assign('money',$money);
        $this->display();
    }
    function zhitui_ajax(){

        $users = M('users');
        $p = $_GET['p'];
        $zhitui_info = $users -> field("nickname,headimgurl,agent,subscribe,subscribe_time") -> where(" pid = '$this->user_id' ") -> order('user_id desc') -> limit($p,8) ->select();
        if($zhitui_info == null){
            $this->assign('more',0);
        }else{
            $zhitui_info = $this -> infoto($zhitui_info);
            foreach($zhitui_info as $K=>$v){
                $zhitui_info[$K]['subscribe_time'] = date("m月d日H时i分",$v['subscribe_time']);
            }
            $this->assign('zhitui_info',$zhitui_info);
            $this->assign('p',$p+8);$this->assign('more',1);
        }

        $this->display();
    }

    private function nameinfoto($info){
        if(F('daili_info','',DATA_ROOT)){
            $daili_info = F('daili_info','',DATA_ROOT);
        }else{
            $daili_info = M('daili_info') -> select();F('daili_info',$daili_info,DATA_ROOT);
        }
        switch($info['agent']){
            case 0:
            $info['agent_name'] = '普通会员';
            break;
            case 1:
            $info['agent_name'] = $daili_info[0]['first_name'];
            break;
            case 2:
            $info['agent_name'] = $daili_info[0]['second_name'];
            break;
            case 3:
            $info['agent_name'] = $daili_info[0]['third_name'];
            break;
        }
        return $info;
    }

    //获取用户openid
    private function openid(){
        // $appid = "wxe1a12ef18d3b3914";
        // $appserect="34be1fdd0f7ea53f1f1a93a4e780bc7e";
//        if(F('config_info','',DATA_ROOT)){
//            $config_info = F('config_info','',DATA_ROOT);
//        }else{
            $config_info = M('config')->select();F('config_info',$config_info,DATA_ROOT);
//        }
        $appid=$config_info[0]['appid'];
        $appserect=$config_info[0]['appsecret'];
        if(empty($_GET['code'])){
            $redirect_uri='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            //echo $redirect_uri;exit;
            $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_base&state=333#wechat_redirect";

            header("Location:$url");
        }else{
            $code=$_GET['code'];
            $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appserect."&code=".$code."&grant_type=authorization_code";
            $res=$this->http_request($url);
            $result=json_decode($res,true);
            $access_token=$result['access_token'];
            $openid=$result['openid'];
            return $openid;
        }
    }

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
}
?>
