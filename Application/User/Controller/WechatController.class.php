<?php
namespace User\Controller;
use Think\Controller;
class WechatController extends Controller{

	//判断微信用户user_id
	public function get_user_id($openid){
		$users = M('users');
		$info = $users -> getFieldByOpenid($openid,'user_id');
		echo '<pre>';
		print_r($info);
		die();
		//dump($info);
		if($info == null){
			return 0;
		}else{
			return $info;
		}
	}

	public function add_user_id($scene_id,$openid){
		//为该用户创建身份信息
		$weixin = A("Wxapi/Weixin");
		$add_data = $weixin -> get_user_info($openid)
;
echo '<pre>';
print_r($add_data);
die();
		$add_data['pid'] = $scene_id;
		$add_user_id = M('users') -> add($add_data);
		if(!$add_user_id){return "创建用户信息失败！请重新尝试！";exit;}
		$pidinfo = M('users') ->getFieldByUser_id($scene_id,'openid');
//		$weixin -> send_word($pidinfo,'系统通知：\n\n「'.$add_data['nickname'].'」已通过您的二维码成功关注!');
		echo $add_user_id;
		return $add_user_id;
	}



	//绑定新关注用户信息
	public function contact_user($add_user_id){
		//根据参数二维码id找到用户，是由谁引入平台
		$users = M('users');$user_contact = M('user_contact');
		//查询该用户现在排列到了第几层级
		$scene_id = $users -> getFieldByUser_id($add_user_id,'pid');
		//查询是否已经执行过，避免重复执行
		$res = $user_contact -> where(" user_id = '$scene_id' and children_id = '$add_user_id' ") -> find();
		if(!$res){
			$user_level = $users -> getFieldByUser_id($scene_id,'level');
			if(!$user_level){return "系统内该二维码拥有者不存在，请取消关注！";exit;}
			//查询该用户第N层级有几个会员，是否满N*3个会员
			$result = $this -> get_pid_user($scene_id,$user_level,$add_user_id);
		}
	}

	private function get_pid_user($user_id,$level,$children_id){
		/**
		$user_id ----- 当前二维码来源人
		$level ------- 当前二维码来源人排到的层级数
		$children_id ----- 当前关注人的用户id
		**/
		//查询该用户该层级有几个会员，是否满N*3个会员,返回要添加的父层id
		$user_contact = M('user_contact');$users = M('users');
		//确认该层级下线数是否已经足够，因为该层级可能会已经排满
		$now_num = $user_contact -> where(" user_id = '$user_id' and level = '$level' ") -> count();
		if($now_num >= pow(3,$level)){
			$users->where(" user_id = '$user_id' ") -> setInc('level');
			$level = $level*1 + 1*1;
		}
		//如果是排列第一层的话，直接处理
		switch($level){
			case 1:
			//保存用户关系信息
			$data = array('user_id'=>$user_id,'children_id'=>$children_id,'level'=>1,'time'=>time());
			$user_contact -> add($data);
			$add_pid_id = $user_id;
			break;
			default:
			//得到该层的所有下层的信息,根据传入的层级 level ，去查询该层级该排给谁
			$i_level = $level - 1;
			$children_info = $user_contact -> where(" user_id = '$user_id' and level = '$i_level' ") -> order("time asc") -> select();
			$i = 0;
			//找出下一层谁的下三 还不满数,得到id，$i_user_id
			do{
				$i_user_id = $children_info[$i]['children_id'];
				$children_num = $user_contact -> where(" user_id = '$i_user_id' and level = 1 ") ->count();
				if($children_num<3){$save_pid_id = $i_user_id;}else{$save_pid_id = null;}
				$i++;
			}while($children_num==3);
			if($save_pid_id != null){
				//插入该条数据
				$data = array('user_id'=>$save_pid_id,'children_id'=>$children_id,'level'=>1,'time'=>time());
				$user_contact -> add($data);
				$add_pid_id = $save_pid_id;
			}
			break;
		}
		/********************
		*添加各层级关系拓扑*
		*******************/
		if($add_pid_id != null){

			$add_level = 2;
			do{
				$pid_user_id = $user_contact ->field("user_id") -> where(" children_id = '$add_pid_id' and level = 1 ") ->find();
				$add_pid_id = $pid_user_id['user_id'];
				if($add_pid_id != null){
					$add_data = array('user_id'=>$add_pid_id,'children_id'=>$children_id,'level'=>$add_level,'time'=>time());
					$user_contact -> add($add_data);
				}
				$add_level++;
			}while($pid_user_id != null && $add_level <= 9);
		}
	}

	//代理购买发红包处理
	public function deal($user_id,$order_info){
		if($order_info['is_true'] == 1){
			return "该订单已分成，无需重复分成";exit;
		}
		if(F('daili_info','',DATA_ROOT)){
			$daili_info = F('daili_info','',DATA_ROOT);
		}else{
			$daili_info = M('daili_info') -> select();F('daili_info',$daili_info,DATA_ROOT);
		}

		//先判断购买的是哪个级别
		$type = trim($order_info['type']);
		$bucha = trim($order_info['bucha']);
		$user_contact = M('user_contact');$users = M('users');




		//找出上九层
		$user_nickname = $users ->getFieldByUser_id($user_id,'nickname');
		//用户购买了代理，对直推人进行奖励，按订单的total_fee的10%进行奖励
		$this-> zhituideal($user_id,$order_info['total_fee'],$user_nickname);
		$first_pid = $user_contact -> field("user_id") -> where(" children_id = '$user_id' and level = 1 ") -> find();
		$second_pid = $user_contact -> field("user_id") -> where(" children_id = '$user_id' and level = 2 ") -> find();
		$third_pid = $user_contact -> field("user_id") -> where(" children_id = '$user_id' and level = 3 ") -> find();
		if($type >1){
			$four_pid = $user_contact -> field("user_id") -> where(" children_id = '$user_id' and level = 4 ") -> find();
			$five_pid = $user_contact -> field("user_id") -> where(" children_id = '$user_id' and level = 5 ") -> find();
			$six_pid = $user_contact -> field("user_id") -> where(" children_id = '$user_id' and level = 6 ") -> find();
		}
		if($type > 2){
			$seven_pid = $user_contact -> field("user_id") -> where(" children_id = '$user_id' and level = 7 ") -> find();
			$eight_pid = $user_contact -> field("user_id") -> where(" children_id = '$user_id' and level = 8 ") -> find();
			$nine_pid = $user_contact -> field("user_id") -> where(" children_id = '$user_id' and level = 9 ") -> find();
		}


		switch($type){
			case 1:
			//给上一级返10元，上2级返20元，上3级返30元，
			$this -> hbrecord($first_pid['user_id'],$order_info,$daili_info[0]['first_hongbao'],1,$user_nickname,$daili_info[0]['first_name'],1);
			$this -> hbrecord($second_pid['user_id'],$order_info,$daili_info[0]['second_hongbao'],1,$user_nickname,$daili_info[0]['first_name'],2);
			$this -> hbrecord($third_pid['user_id'],$order_info,$daili_info[0]['third_hongbao'],1,$user_nickname,$daili_info[0]['first_name'],3);
			break;
			case 2:
			//返给上一级90和上上级180，且要检查是否只拿三个，有无拿够
			if($bucha == 0){
				//不是补差价，也给上一级返
				//给上一级返10元，上2级返20元，上3级返30元，
				$this -> hbrecord($first_pid['user_id'],$order_info,$daili_info[0]['first_hongbao'],1,$user_nickname,$daili_info[0]['second_name'],1);
				$this -> hbrecord($second_pid['user_id'],$order_info,$daili_info[0]['second_hongbao'],1,$user_nickname,$daili_info[0]['second_name'],2);
				$this -> hbrecord($third_pid['user_id'],$order_info,$daili_info[0]['third_hongbao'],1,$user_nickname,$daili_info[0]['second_name'],3);
				//4-6级
				$this -> hbrecord($four_pid['user_id'],$order_info,$daili_info[0]['four_hongbao'],2,$user_nickname,$daili_info[0]['second_name'],4);
				$this -> hbrecord($five_pid['user_id'],$order_info,$daili_info[0]['five_hongbao'],2,$user_nickname,$daili_info[0]['second_name'],5);
				$this -> hbrecord($six_pid['user_id'],$order_info,$daili_info[0]['six_hongbao'],2,$user_nickname,$daili_info[0]['second_name'],6);
			}
			if($bucha == 1){
				//100-300补差价，只给上上级返
				//4-6级
				$this -> hbrecord($four_pid['user_id'],$order_info,$daili_info[0]['four_hongbao'],2,$user_nickname,$daili_info[0]['second_name'],4);
				$this -> hbrecord($five_pid['user_id'],$order_info,$daili_info[0]['five_hongbao'],2,$user_nickname,$daili_info[0]['second_name'],5);
				$this -> hbrecord($six_pid['user_id'],$order_info,$daili_info[0]['six_hongbao'],2,$user_nickname,$daili_info[0]['second_name'],6);
			}
			break;
			case 3:
			//返给上三级分别90，180，270，且要检查是否有资格拿
			if($bucha == 0){
				//不是补差价，全返
				//给上一级返10元，上2级返20元，上3级返30元，
				$this -> hbrecord($first_pid['user_id'],$order_info,$daili_info[0]['first_hongbao'],1,$user_nickname,$daili_info[0]['third_name'],1);
				$this -> hbrecord($second_pid['user_id'],$order_info,$daili_info[0]['second_hongbao'],1,$user_nickname,$daili_info[0]['third_name'],2);
				$this -> hbrecord($third_pid['user_id'],$order_info,$daili_info[0]['third_hongbao'],1,$user_nickname,$daili_info[0]['third_name'],3);
				//4-6级
				$this -> hbrecord($four_pid['user_id'],$order_info,$daili_info[0]['four_hongbao'],2,$user_nickname,$daili_info[0]['third_name'],4);
				$this -> hbrecord($five_pid['user_id'],$order_info,$daili_info[0]['five_hongbao'],2,$user_nickname,$daili_info[0]['third_name'],5);
				$this -> hbrecord($six_pid['user_id'],$order_info,$daili_info[0]['six_hongbao'],2,$user_nickname,$daili_info[0]['third_name'],6);
				//7-9级
				$this -> hbrecord($seven_pid['user_id'],$order_info,$daili_info[0]['seven_hongbao'],3,$user_nickname,$daili_info[0]['third_name'],7);
				$this -> hbrecord($eight_pid['user_id'],$order_info,$daili_info[0]['eight_hongbao'],3,$user_nickname,$daili_info[0]['third_name'],8);
				$this -> hbrecord($nine_pid['user_id'],$order_info,$daili_info[0]['nine_hongbao'],3,$user_nickname,$daili_info[0]['third_name'],9);
			}
			if($bucha == 1){
				//从300-600补差价，只给上上上级返270，
				//7-9级
				$this -> hbrecord($seven_pid['user_id'],$order_info,$daili_info[0]['seven_hongbao'],3,$user_nickname,$daili_info[0]['third_name'],7);
				$this -> hbrecord($eight_pid['user_id'],$order_info,$daili_info[0]['eight_hongbao'],3,$user_nickname,$daili_info[0]['third_name'],8);
				$this -> hbrecord($nine_pid['user_id'],$order_info,$daili_info[0]['nine_hongbao'],3,$user_nickname,$daili_info[0]['third_name'],9);
			}
			if($bucha == 2){
				//从100-600补差价，给上上级返180，上上上级返270
				//4-6级
				$this -> hbrecord($four_pid['user_id'],$order_info,$daili_info[0]['four_hongbao'],2,$user_nickname,$daili_info[0]['third_name'],4);
				$this -> hbrecord($five_pid['user_id'],$order_info,$daili_info[0]['five_hongbao'],2,$user_nickname,$daili_info[0]['third_name'],5);
				$this -> hbrecord($six_pid['user_id'],$order_info,$daili_info[0]['six_hongbao'],2,$user_nickname,$daili_info[0]['third_name'],6);
				//7-9级
				$this -> hbrecord($seven_pid['user_id'],$order_info,$daili_info[0]['seven_hongbao'],3,$user_nickname,$daili_info[0]['third_name'],7);
				$this -> hbrecord($eight_pid['user_id'],$order_info,$daili_info[0]['eight_hongbao'],3,$user_nickname,$daili_info[0]['third_name'],8);
				$this -> hbrecord($nine_pid['user_id'],$order_info,$daili_info[0]['nine_hongbao'],3,$user_nickname,$daili_info[0]['third_name'],9);
			}
			break;
		}
	}

	function zhituideal($user_id,$money,$nickname){
		//直推奖励10%
		$money = $money*0.1;
		$users = M('users');
		$pid  = $users ->getFieldByUser_id($user_id,'pid');
		$openid  = $users ->getFieldByUser_id($pid,'openid');
		//创建红包数据
		$data = array(
			'from_user_id'=>$user_id,
			'user_id'=>$pid,
			'money'=>$money,
			'time'=>time()
		);
		M('ztrecord') -> add($data);
		//通知有新红包
		$this -> template($openid,"您的直推会员「".$nickname."」参与团购返红包".$money."元");
	}

	private function hbrecord($user_id,$order_info,$fee,$type,$nickname,$name,$jibie){
		$user_info = M('users') ->field("openid,agent")->where(" user_id = '$user_id' ") -> select();
		$hbrecord = M('hbrecord');
		switch($user_info[0]['agent']){
			case 1:
			if($type == 1){
				//该用户是初级代理，下三级购买时可以拿10，20，30，
				$case = 1;//还没有拿够3个红包，可以继续拿
			}else{
				$case = 0; //不能拿除三级以外的红包
			}
			break;
			case 2:
			if($type == 1){
				$case = 1; //购买的是300的代理，这时候直推的90都可以拿到
			}elseif($type == 2){
				//二级的180
				$case = 1; //购买的是300的代理，这时候直推的90都可以拿到
			}else{
				$case = 0; //不能拿三级的红包
			}
			break;
			case 3:
			$case = 1;//所有红包都能拿
			break;
			default:
			$case = 0;
			break;
		}

		if($case == 1){
			$data = array(
				'time'=>time(),
				'order_id'=>$order_info['order_id'],
				'user_id'=>$user_id,
				'hongbao_fee'=>$fee,
				'from_user_id'=>$order_info['user_id'],
				'type'=>$type,
			);
			$hbrecord -> add($data);
			//通知有新红包
			// $weixin = A("Wxapi/Weixin");
			// $weixin -> send_word($user_info[0]['openid'],"系统通知：\n您的".$jibie."度人脉「".$nickname."」已成功购买【".$name."】\n\n已返红包：".$fee."元，请到个人中心查收");
			$this -> template($user_info[0]['openid'],$jibie."度人脉「".$nickname."」购买".$name."返红包".$fee."元");
		}

	}
	function template($openid,$text){
		$template_info = F('template_info','',DATA_ROOT);
		$weixin = A("Wxapi/Weixin");
		$tem_data = '{
		   "touser":"'.$openid.'",
		   "template_id":"'.$template_info['fuwu_done_template_id'].'",
		   "url":"http://'.$_SERVER['SERVER_NAME'].U('/User/Center/').'",           
		   "data":{
				   "first": {
					   "value":"您收到了一个红包，点击到个人中心查收\n",
					   "color":"'.$template_info['fuwu_done_top'].'"
				   },
				   "keyword1":{
					   "value":"参与团购计划",
					   "color":"'.$template_info['fuwu_done_text'].'"
				   },
				   "keyword2": {
					   "value":"'.$text.'",
					   "color":"'.$template_info['fuwu_done_text'].'"
				   },
				   "keyword3": {
					   "value":"'.date("Y-m-d H:i",time()).'",
					   "color":"'.$template_info['fuwu_done_text'].'"
				   },
				   "remark":{
					   "value":"",
					   "color":"'.$template_info['fuwu_done_top'].'"
				   }
		   }
	    }';
		$weixin ->send_template($openid,$tem_data);
	}

	//商城购买成功后利润分成
	public function shop_deal($user_id,$order_info){
		//用户的关系层级
		$users = M('users');$broke_record = M('broke_record');
		$user_info = $users -> field("nickname,pid,gid,oid") -> where(" user_id = '$user_id' ") -> find();
		//判断各层上级是否存在，并且是代理资格，如果是，进行下一步
		$pid_info = $this->check_power($user_info['pid']);
		$gid_info = $this->check_power($user_info['gid']);
		$oid_info = $this->check_power($user_info['oid']);
		$weixin = A("Wxapi/Weixin");
		if($pid_info != false){
			//该上级符合分成条件，go
			$profit = $this -> profit_sum($order_info,1);
			if($profit != 0){
				//给用户加佣金操作
				$pid_user_id = $user_info['pid'];
				$users -> where(" user_id = '$pid_user_id' ") -> setInc('shop_income',$profit);
				//记录佣金明细
				$data = array(
					'time'=>time(),
					'user_id'=>$pid_user_id,
					'desc'=>'一级会员['.$user_info['nickname'].']购买商品返佣金',
					'fee'=>$profit
				);
				$broke_record -> add($data);
				$weixin -> send_word($pid_info['openid'],"系统通知：\n您的".$data['desc']."￥".$profit."\n请到用户中心查收");
			}

		}
		if($gid_info != false){
			//该上级符合分成条件，go
			$profit = $this -> profit_sum($order_info,2);
			if($profit != 0){
				//给用户加佣金操作
				$gid_user_id = $user_info['gid'];
				$users -> where(" user_id = '$gid_user_id' ") -> setInc('shop_income',$profit);
				//记录佣金明细
				$data = array(
					'time'=>time(),
					'user_id'=>$gid_user_id,
					'desc'=>'二级会员['.$user_info['nickname'].']购买商品返佣金',
					'fee'=>$profit
				);
				$broke_record -> add($data);
				$weixin -> send_word($gid_info['openid'],"系统通知：\n您的".$data['desc']."￥".$profit."\n请到用户中心查收");
			}
		}
		if($oid_info != false){
			//该上级符合分成条件，go
			$profit = $this -> profit_sum($order_info,1);
			if($profit != 0){
				//给用户加佣金操作
				$oid_user_id = $user_info['oid'];
				$users -> where(" user_id = '$oid_user_id' ") -> setInc('shop_income',$profit);
				//记录佣金明细
				$data = array(
					'time'=>time(),
					'user_id'=>$oid_user_id,
					'desc'=>'三级会员['.$user_info['nickname'].']购买商品返佣金￥',
					'fee'=>$profit
				);
				$broke_record -> add($data);
				$weixin -> send_word($oid_info['openid'],"系统通知：\n您的".$data['desc']."￥".$profit."\n请到用户中心查收");
			}
		}
	}

	//判断上级权限
	public function check_power($user_id){
		// 如果没哟权限，返回false，如果有返回true
		if( $user_id == 0 ){
			return false;
		}else{
			$users = M('users');
			$user_info = $users -> field("agent,openid") -> where(" user_id = '$user_id' ") -> find();
			if($user_info['agent'] == 0 ){
				return false;
			}else{
				return $user_info;
			}
		}

	}
	// 各层级分成利润
	public function profit_sum($order_info,$type){
		$shop_goods = M('shop_goods');
		$profit = 0;
		foreach($order_info as $k=>$v){
			$good_id = $v['good_id'];
			$good_info = $shop_goods -> field("good_profit,first_per,second_per,third_per") -> where(" good_id = '$good_id' ") -> find();

			$good_profit = $good_info['good_profit']*$v['good_num'];
			switch($type){
				case 1:
				$profit= $profit + $good_profit*($good_info['first_per']/100);
				break;
				case 2:
				$profit= $profit + $good_profit*($good_info['second_per']/100);
				break;
				case 3:
				$profit= $profit + $good_profit*($good_info['third_per']/100);
				break;
				default:
				break;
			}
		}
		return $profit;

	}

}
?>
