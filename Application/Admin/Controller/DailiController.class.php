<?php
namespace Admin\Controller; @eval("//Encode by  phpjiami.com,Free user."); 
use Think\Controller;

class DailiController extends ActionController{
	function __construct(){
		parent::__construct();
		//echo session('admin_id');
		if(!session('admin_id')){
			$this->error('登录已超时，请重新登录',U('User/index'));
		}
		
		
	}
    function index(){
		$daili_info = M('daili_info');$daili_banner = M('daili_banner');
		if($_POST){
			$data=$_POST;
			$upload = new \Think\Upload();// 实例化上传类  
			$upload->rootPath = './Uploads/';  
			$upload->maxSize   =     3145728 ;// 设置附件上传大小 
			$upload->autoSub = false;   
			$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
			$upload->savePath  =      ''; // 设置附件上传目录    // 上传文件  
			$upload->autoSub = true;
			$upload->subName = date("Ymd");
			$imginfo   =   $upload->upload();
			//dump($imginfo);exit;
			if($imginfo){
				foreach($imginfo as $k=>$v){
					$arr = array();
					$arr['pic_url'] = "Uploads/".$v["savepath"].$v["savename"];
					$res = $daili_banner -> add($arr);
				}
			}
			if($_POST['daili_id'] == null){
				$res = $daili_info -> add($data);
			}else{
				$res = $daili_info -> where(" id = '$_POST[daili_id]' ") -> save($data);
			}
			
			$this->success("保存成功");
		}else{
			$info = $daili_info -> select();F('daili_info',$info,DATA_ROOT);
			$qrset = M('qrset') -> select();//F('daili_info',$info,DATA_ROOT);
			$banner = $daili_banner -> select();
			$this->assign("daili_info",$info);
			$this->assign("qrset",$qrset);
			$this->assign("banner",$banner);
			$this->display();
		}
        
    }
	function delbanner(){
		$res = M('daili_banner') -> where(" id = '$_POST[id]' ") -> delete();
		if($res){
			$arr['success'] = 1;
		}else{
			$arr['success'] = 0;
		}
		
		echo json_encode($arr);
	}
	
	function qr(){
		if(!$_POST){exit;}
		$upload = new \Think\Upload();// 实例化上传类  
		$upload->rootPath = './Uploads/';  
		$upload->maxSize   =     3145728 ;// 设置附件上传大小 
		$upload->autoSub = false;   
		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
		$upload->savePath  =      ''; // 设置附件上传目录    // 上传文件  
		$upload->autoSub = true;
		$upload->subName = date("Ymd");
		$imginfo   =   $upload->upload();
		if($imginfo){
			$_POST['pic_url'] = "Uploads/".$imginfo['image1']["savepath"].$imginfo['image1']["savename"];
		}
		if($_POST[qr] == null){
			$res = M('qrset') -> add($_POST);
		}else{
			$res = M('qrset') -> where(" id = '$_POST[qr]' ") -> save($_POST);
		}
		M('qrcode') -> where(" update_time > 0 ") -> setField('update_time','0');
		$this->success("保存成功");
	}
	
	function imgtest(){
		$info = M('qrset')->select();
		if(!$info){
			$text = "发现您还没有设置推广二维码相关参数，请先行设置后再查看";
			$this->assign("text",$text);
		}else{
			$weixin=A("Wxapi/Qrimg");
			$res = $weixin->index(0,0,"未知用户");
			$url = __ROOT__."/".$res;
			$this->assign("url",$url);
			
		}
		$this->display();
	}
	
	function users(){
		$pagecount = 10;
		$count = M('users') -> count();
		$Page = new \Think\Page($count,$pagecount);
		$info=M('users')->order("user_id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		foreach($info as $key=>$value){
			$info[$key]['subscribe_time'] = date("Y-m-d H:i:s",$value['subscribe_time']);
		}
		$info = $this -> infoto($info);
		// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();
		$this->assign('count',$count);
		$this->assign('page',$show);
		$this->assign("info",$info);
		$this->display();
	}
	function users_ajax(){
		if($_GET['nickname']){
			$where['nickname'] =  array('like','%'.$_GET['nickname'].'%');
		}elseif($_GET['user_id']){
			$where = array('user_id'=>$_GET['user_id']);
		}elseif($_GET['subscribe'] != null ){
			$where = array('subscribe'=>$_GET['subscribe']);
		}else{
			$where = array();
		}
		$pagecount = 10;
		$count = M('users') ->where($where)-> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$info=M('users')->where($where)->order("user_id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		foreach($info as $key=>$value){
			$info[$key]['subscribe_time'] = date("Y-m-d H:i:s",$value['subscribe_time']);
		}
		$info = $this -> infoto($info);
		// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();
		$this->assign('count',$count);
		$this->assign('page',$show);
		$this->assign("info",$info);
		$this->display();
	}
	function contact(){
		$user_id = $_GET['user_id'];
		if(!$user_id){exit;}
		$users = M('users');$user_contact = M('user_contact');
		$user_info = $users -> field("nickname,headimgurl") ->where(" user_id = $user_id ") -> find();
		//上级信息
		$pid = $user_contact ->where(" level = 1 ")-> getFieldByChildren_id($user_id,'user_id');
		//第1层
		$first_id = $user_contact -> field("children_id") -> where(" user_id = '$user_id' and level = 1 ") -> select();
		//第2层
		$second_id = $user_contact -> field("children_id") -> where(" user_id = '$user_id' and level = 2 ") -> select();
		//第3层
		$third_id = $user_contact -> field("children_id") -> where(" user_id = '$user_id' and level = 3 ") -> select();
		//第4层
		$four_id = $user_contact -> field("children_id") -> where(" user_id = '$user_id' and level = 4 ") -> select();
		//第5层
		$five_id = $user_contact -> field("children_id") -> where(" user_id = '$user_id' and level = 5 ") -> select();
		//第6层
		$six_id = $user_contact -> field("children_id") -> where(" user_id = '$user_id' and level = 6 ") -> select();
		//第7层
		$seven_id = $user_contact -> field("children_id") -> where(" user_id = '$user_id' and level = 7 ") -> select();
		//第8层
		$eight_id = $user_contact -> field("children_id") -> where(" user_id = '$user_id' and level = 8 ") -> select();
		//第9层
		$nine_id = $user_contact -> field("children_id") -> where(" user_id = '$user_id' and level = 9") -> select();
		$this -> assign("user_info",$user_info);
		$pid_info = $users->field("user_id,nickname,headimgurl") -> where(" user_id = '$pid' ") -> select();
		$this -> assign("pid_info",$pid_info);
		$this -> assign("first_info",$this->contact_info($first_id));
		$this -> assign("second_info",$this->contact_info($second_id));
		$this -> assign("third_info",$this->contact_info($third_id));
		$this -> assign("four_info",$this->contact_info($four_id));
		$this -> assign("five_info",$this->contact_info($five_id));
		$this -> assign("six_info",$this->contact_info($six_id));
		$this -> assign("seven_info",$this->contact_info($seven_id));
		$this -> assign("eight_info",$this->contact_info($eight_id));
		$this -> assign("nine_info",$this->contact_info($nine_id));
		$this -> display();
	}
	function contact_info($arr){
		$users = M('users');
		$result = array();
		foreach($arr as $k=>$v){
			$result[$k] = $users -> field("user_id,nickname,headimgurl") -> getByUser_id($v['children_id']);
		}
		return $result;
	}
	function daili(){
		$pagecount = 10;
		$count = M('users')->where("agent >0 and daili = 1") -> count();
		$Page = new \Think\Page($count,$pagecount);
		$info=M('users')->where("agent >0 and daili = 1")->order("user_id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		foreach($info as $key=>$value){
			$info[$key]['subscribe_time'] = date("Y-m-d H:i:s",$value['subscribe_time']);
		}
		$info = $this -> infoto($info);
		// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();
		$this->assign('count',$count);
		$this->assign('page',$show);
		$this->assign("info",$info);
		$this->display();
	}
	function daijin(){
		$this -> display();
	}
	function daijin_ajax(){
		$daijin = M('daijin');$users = M('users');
		$pagecount = 10;
		$count = $daijin -> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$info=$daijin->order("time desc")->limit($Page->firstRow.','.$Page->listRows)->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		foreach($info as $k=>$v){
			$del_user_id = $v['del_user_id'];
			$user_id = $v['user_id'];
			$info[$k]['del_user'] = $users -> field("nickname,headimgurl") -> where(" user_id = '$del_user_id' ") -> find();
			$info[$k]['user'] = $users -> field("nickname,headimgurl") -> where(" user_id = '$user_id' ") -> find();
			$info[$k]['time'] = date("Y-m-d H:i:s",$v['time']);
		}
		// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();
		$this->assign('count',$count);
		$this->assign('page',$show);
		$this->assign("info",$info);
		$this->display();
	}
	function dailichange(){
		$user_id = $_POST['id'];
		if(intval($_POST['type'])>1){exit;}
		M('users')-> where(" user_id = '$user_id' ") -> setField('daili',intval($_POST['type']));
		echo json_encode($arr);
	}
	function daili_ajax(){
		if($_GET['nickname']){
			$where['nickname'] =  array('like','%'.$_GET['nickname'].'%');
		}elseif($_GET['user_id']){
			$where = array('user_id'=>$_GET['user_id']);
		}else{
			$where = array();
		}
		$pagecount = 10;
		$count = M('users') ->where($where)-> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$info=M('users')->where($where)->order("user_id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		foreach($info as $key=>$value){
			$info[$key]['subscribe_time'] = date("Y-m-d H:i:s",$value['subscribe_time']);
		}
		$info = $this -> infoto($info);
		// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();
		$this->assign('count',$count);
		$this->assign('page',$show);
		$this->assign("info",$info);
		$this->display();
	}
	function get_address(){
		$user_id = $_POST['id'];
		$info = M('user_address') -> getByUser_id($user_id);
		$arr['info'] = '<h5>姓名：'.$info['username'].'</h5><h5>电话：'.$info['telphone'].'</h5><h5>地址：'.$info['address'].'</h5>';
		echo json_encode($arr);
	}
	function orders(){
		$pagecount = 10;
		if($_POST['order_sn']){
			$where = array('order_sn'=>$_POST['order_sn']);
		}elseif($_POST['user_id']){
			$where = array('user_id'=>$_POST['user_id']);
		}elseif($_POST['is_true'] != null ){
			$where = array('is_true'=>$_POST['is_true']);
		}else{
			$where = array();
		}
		$count = M('agent_orders') -> where($where) -> count();
		$Page = new \Think\Page($count,$pagecount);
		$info=M('agent_orders')-> where($where)->order("time desc")->limit($Page->firstRow.','.$Page->listRows)->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		$users = M('users');
		foreach($info as $key=>$value){
			$info[$key]['time'] = date("Y-m-d H:i:s",$value['time']);
			$info[$key]['nickname'] = $users -> getFieldByUser_id($value['user_id'],"nickname");
			if(empty($info[$key]['nickname'])){$info[$key]['nickname'] = "未知用户";}
			switch($value['type']){
				case 1:
				$info[$key]['desc'] = "套餐1";
				break;
				case 2:
				if($value['bucha'] == 0){
					$info[$key]['desc'] = "套餐2";
				}else{
					$info[$key]['desc'] = "套餐2从套餐1补差价";
				}
				break;
				case 3:
				if($value['bucha'] == 0){
					$info[$key]['desc'] = "套餐3";
				}elseif($value['bucha'] == 1){
					$info[$key]['desc'] = "套餐3从套餐2补差价";
				}else{
					$info[$key]['desc'] = "套餐3从套餐1补差价";
				}
				break;
				default:
				$info[$key]['desc'] = "未知";
				break;
			}
		}
		$info = $this -> infoto($info);
		// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();
		$this->assign('count',$count);
		$this->assign('page',$show);
		$this->assign("info",$info);
		$this->display();
	}
	function orders_ajax(){
		$pagecount = 10;
		if($_GET['sn']){
			$where = array('order_sn'=>$_GET['sn']);
		}elseif($_GET['user_id']){
			$where = array('user_id'=>$_GET['user_id']);
		}elseif($_GET['is_true'] != null ){
			$where = array('is_true'=>$_GET['is_true']);
		}else{
			$where = array();
		}
		$count = M('agent_orders') -> where($where) -> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$info=M('agent_orders')-> where($where)->order("time desc")->limit($Page->firstRow.','.$Page->listRows)->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		$users = M('users');
		foreach($info as $key=>$value){
			$info[$key]['time'] = date("Y-m-d H:i:s",$value['time']);
			$info[$key]['nickname'] = $users -> getFieldByUser_id($value['user_id'],"nickname");
			if(empty($info[$key]['nickname'])){$info[$key]['nickname'] = "未知用户";}
			switch($value['type']){
				case 1:
				$info[$key]['desc'] = "套餐1";
				break;
				case 2:
				if($value['bucha'] == 0){
					$info[$key]['desc'] = "套餐2";
				}else{
					$info[$key]['desc'] = "套餐2从套餐1补差价";
				}
				break;
				case 3:
				if($value['bucha'] == 0){
					$info[$key]['desc'] = "套餐3";
				}elseif($value['bucha'] == 1){
					$info[$key]['desc'] = "套餐3从套餐2补差价";
				}else{
					$info[$key]['desc'] = "套餐3从套餐1补差价";
				}
				break;
				default:
				$info[$key]['desc'] = "未知";
				break;
			}
		}
		$info = $this -> infoto($info);
		// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();
		$this->assign('count',$count);
		$this->assign('page',$show);
		$this->assign("info",$info);
		$this->display();
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
	
	
	function excel_down(){
		
		//dump($info);exit;
		$excel = A('Admin/Excel');
		$excel ->index($info,$username);
	}
    function hongbao(){
		$this->display();
	}
	function hongbao_ajax(){
		$pagecount = 10;
		if($_GET['from_user_id']){
			$where = array('from_user_id'=>$_GET['from_user_id']);
		}elseif($_GET['user_id']){
			$where = array('user_id'=>$_GET['user_id']);
		}else{
			$where = array();
		}
		$agent_orders = M('agent_orders');$hbrecord = M('hbrecord');$users = M('users');
		$count = $hbrecord -> where($where) -> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$info=$hbrecord-> where($where)->order("time desc")->limit($Page->firstRow.','.$Page->listRows)->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		foreach($info as $key=>$value){
			$info[$key]['time'] = date("Y-m-d H:i:s",$value['time']);
			$info[$key]['tonickname'] = $users -> getFieldByUser_id($value['user_id'],"nickname");
			$info[$key]['fromnickname'] = $users -> getFieldByUser_id($value['from_user_id'],"nickname");
			$info[$key]['order_sn'] = $agent_orders -> getFieldByOrder_id($value['order_id'],"order_sn");
			if(empty($info[$key]['tonickname'])){$info[$key]['nickname'] = "未知用户";}
			if(empty($info[$key]['fromnickname'])){$info[$key]['nickname'] = "未知用户";}
			
		}
		$info = $this -> infoto($info);
		// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign("info",$info);
		$this->display();
	}
	
	function del(){
		if($_POST['id']){
			$user_id = $_POST['id'];
			M('users')->where(" user_id = '$user_id' ")->delete();
			$arr = array();
			echo json_encode($arr);
		}
	}

}

?>