<?php

//decode by http://www.yunlu99.com/
namespace Admin\Controller;

use Think\Controller;
class ShopController extends ActionController
{
	function __construct()
	{
		parent::__construct();
		if (!session('admin_id')) {
			$this->error('请登录', U('User/index'), 5);
		}
	}
	function setting()
	{
		$shop_bannar = M('shop_bannar');
		$shop_ad = M('shop_ad');
		if ($_POST['uplode'] == 1) {
			$upload = new \Think\Upload();
			$upload->rootPath = './Uploads/';
			$upload->maxSize = 3145728;
			$upload->autoSub = false;
			$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
			$upload->savePath = '';
			$upload->autoSub = true;
			$upload->subName = date("Ymd");
			$imginfo = $upload->upload();
			if (!$imginfo) {
				$this->error($upload->getError());
			} else {
				foreach ($imginfo as $val) {
					$pic_url = "Uploads/" . $val['savepath'] . $val['savename'];
					$shop_bannar->add(array('pic_url' => $pic_url, 'code' => 50));
				}
				$this->success("幻灯片图片上传成功");
			}
			exit;
		} elseif ($_POST['ad'] == 1) {
			$upload = new \Think\Upload();
			$upload->rootPath = './Uploads/';
			$upload->maxSize = 3145728;
			$upload->autoSub = false;
			$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
			$upload->savePath = '';
			$upload->autoSub = true;
			$upload->subName = date("Ymd");
			$imginfo = $upload->upload();
			if ($imginfo) {
				$data['pic_url'] = "Uploads/" . $imginfo['image']['savepath'] . $imginfo['image']['savename'];
			}
			if ($_POST['id'] != null) {
				$id = $_POST['id'];
				$data['link'] = $_POST['link'];
				$shop_ad->where(" id = '{$id}' ")->save($data);
			} else {
				if (!$imginfo) {
					$this->error($upload->getError());
					exit;
				}
				$data['link'] = $_POST['link'];
				$shop_ad->add($data);
			}
			$this->success("首页广告保存成功");
			exit;
		}
		$info = $shop_bannar->order("code desc")->select();
		$ad_info = $shop_ad->order("code desc")->select();
		$this->assign("empty", '<div class="text-center">暂无数据</div>');
		$this->assign("bannar", $info);
		$this->assign("ad", $ad_info);
		$this->display();
	}
	function change_shop_bannar()
	{
		if ($_POST['type'] == 'bannar') {
			$model = M('shop_bannar');
		}
		if ($_POST['type'] == 'ad') {
			$model = M('shop_ad');
		}
		$id = $_POST['id'];
		$code = $_POST['code'];
		$model->where(" id = '{$id}' ")->save(array('code' => $code));
		$arr = array();
		echo json_encode($arr);
	}
	function del_shop_bannar()
	{
		if ($_POST['type'] == 'bannar') {
			$model = M('shop_bannar');
		}
		if ($_POST['type'] == 'ad') {
			$model = M('shop_ad');
		}
		$id = $_POST['id'];
		$model->where(" id = '{$id}' ")->delete();
		$arr = array();
		echo json_encode($arr);
	}
	function good()
	{
		$shop_goods = M('shop_goods');
		$shop_categrey = M('shop_categrey');
		$good_list = $shop_goods->order("good_id desc")->select();
		foreach ($good_list as $k => $v) {
			$good_list[$k]['cate_name'] = $shop_categrey->getFieldByCate_id($v['cate_gid'], 'cate_name');
			$gid_name = $shop_categrey->getFieldByCate_id($v['cate_pid'], 'cate_name');
			if ($gid_name != null) {
				$good_list[$k]['cate_name'] .= " -- " . $gid_name;
			}
		}
		$this->assign("good_list", $good_list);
		$this->display();
	}
	function good_ajax()
	{
		$shop_goods = M('shop_goods');
		$shop_categrey = M('shop_categrey');
		$pagecount = 10;
		$count = $shop_goods->where($where)->count();
		$Page = new \Think\Pageajax($count, $pagecount);
		$good_list = $shop_goods->where($where)->order("time desc")->limit($Page->firstRow . ',' . $Page->listRows)->order("good_id desc")->select();
		$Page->setConfig('first', '首页');
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('last', '尾页');
		$Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 ' . I('p', 1) . ' 页/共 %TOTAL_PAGE% 页 ( ' . $pagecount . ' 条/页 共 %TOTAL_ROW% 条)');
		$show = $Page->show();
		$this->assign('page', $show);
		foreach ($good_list as $k => $v) {
			$good_list[$k]['cate_name'] = $shop_categrey->getFieldByCate_id($v['cate_gid'], 'cate_name');
			$gid_name = $shop_categrey->getFieldByCate_id($v['cate_pid'], 'cate_name');
			if ($gid_name != null) {
				$good_list[$k]['cate_name'] .= " -- " . $gid_name;
			}
		}
		$this->assign("good_list", $good_list);
		$this->display();
	}
	function delgood()
	{
		$good_id = $_POST['id'];
		$arr = array();
		$res = M('shop_goods')->where(" good_id = '{$good_id}' ")->delete();
		if ($res) {
			$arr['success'] = 1;
		} else {
			$arr['success'] = 0;
		}
		echo json_encode($arr);
	}
	function changetype()
	{
		$good_id = $_POST['good_id'];
		$type = $_POST['type'];
		$type_id = $_POST['type_id'];
		if ($type_id == 0) {
			$set_id = 1;
		}
		if ($type_id == 1) {
			$set_id = 0;
		}
		$res = M('shop_goods')->where(" good_id = '{$good_id}' ")->setField($type, $set_id);
		if ($res) {
			$arr['success'] = 1;
			$arr['type'] = $set_id;
		} else {
			$arr['success'] = 0;
			$arr['info'] = $type_id;
		}
		echo json_encode($arr);
	}
	function goodadd()
	{
		if ($_POST) {
			dump($_POST);
			$shop_goods = M('shop_goods');
			$good_id = $shop_goods->add($_POST);
			$upload = new \Think\Upload();
			$upload->rootPath = './Uploads/';
			$upload->maxSize = 3145728;
			$upload->autoSub = false;
			$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
			$upload->savePath = '';
			$upload->autoSub = true;
			$upload->subName = date("Ymd");
			$imginfo = $upload->upload();
			if (!$imginfo) {
				$this->error($upload->getError());
			} else {
				$data['good_id'] = $good_id;
				foreach ($imginfo as $file) {
					$data['pic_url'] = "Uploads/" . $file['savepath'] . $file['savename'];
					M('good_pic')->add($data);
				}
			}
			$this->success("添加商品成功", good);
			exit;
		}
		$shop_categrey = M('shop_categrey');
		$categrey = $shop_categrey->where(" pid = 0 ")->select();
		$this->assign("categrey", $categrey);
		foreach ($categrey as $k => $v) {
			$id = $v['cate_id'];
			$jscategrey[$id] = $shop_categrey->where(" pid = {$v['cate_id']} ")->select();
		}
		foreach ($categrey as $k => $v) {
			$categrey[$k]['arr'] = $shop_categrey->where(" pid = '{$v['cate_id']}' ")->order("code desc")->select();
		}
		$this->assign("jscategrey", json_encode($jscategrey));
		$this->display();
	}
	function goodedit()
	{
		$shop_goods = M('shop_goods');
		$good_pic = M('good_pic');
		if ($_POST && $_POST['id']) {
			if (!$_POST['cate_pid']) {
				$_POST['cate_pid'] = 0;
			}
			$good_id = $_POST['id'];
			$shop_goods->where(" good_id = '{$good_id}' ")->save($_POST);
			$upload = new \Think\Upload();
			$upload->rootPath = './Uploads/';
			$upload->maxSize = 3145728;
			$upload->autoSub = false;
			$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
			$upload->savePath = '';
			$upload->autoSub = true;
			$upload->subName = date("Ymd");
			$imginfo = $upload->upload();
			if ($imginfo) {
				foreach ($imginfo as $file) {
					$data['good_id'] = $good_id;
					$data['pic_url'] = "Uploads/" . $file['savepath'] . $file['savename'];
					$good_pic->add($data);
				}
			}
			$this->success("已更新商品信息", good);
			exit;
		}
		$good_id = $_GET['good_id'];
		$shop_categrey = M('shop_categrey');
		$categrey = $shop_categrey->where(" pid = 0 ")->select();
		$good_info = $shop_goods->getByGood_id($good_id);
		$bannar = $good_pic->where(" good_id = '{$good_id}' ")->select();
		$this->assign("categrey", $categrey);
		$this->assign("good_info", $good_info);
		$this->assign("bannar", $bannar);
		foreach ($categrey as $k => $v) {
			$id = $v['cate_id'];
			$jscategrey[$id] = $shop_categrey->where(" pid = {$v['cate_id']} ")->select();
		}
		foreach ($categrey as $k => $v) {
			$categrey[$k]['arr'] = $shop_categrey->where(" pid = '{$v['cate_id']}' ")->order("code desc")->select();
		}
		$this->assign("jscategrey", json_encode($jscategrey));
		$this->display('goodadd');
	}
	function del_good_pic()
	{
		$good_pic = M('good_pic');
		$id = $_POST['id'];
		$good_pic->where(" id = '{$id}' ")->delete();
		$arr = array();
		echo json_encode($arr);
	}
	function change_shop_pic()
	{
		$good_pic = M('good_pic');
		$id = $_POST['id'];
		$good_pic->where(" id = '{$id}' ")->setField("code", $_POST['code']);
		$arr = array();
		echo json_encode($arr);
	}
	function categrey()
	{
		$categrey = M('shop_categrey');
		$pid_info = $categrey->where(" pid = 0 ")->order("code desc")->select();
		foreach ($pid_info as $k => $val) {
			$pid = $val['cate_id'];
			$pid_info[$k]['children'] = $categrey->where(" pid = '{$pid}' ")->order("code desc")->select();
		}
		$this->assign("pid_info", $pid_info);
		$this->display();
	}
	function change_categrey_type()
	{
		$model = M('shop_categrey');
		$cate_id = $_POST['id'];
		$arr = array();
		switch ($_POST['type']) {
			case 'is_show':
				if ($_POST['data'] == '1') {
					$model->where(" cate_id = '{$cate_id}' ")->setField("is_show", 0);
					$arr['state'] = 0;
				} else {
					$model->where(" cate_id = '{$cate_id}' ")->setField("is_show", 1);
					$arr['state'] = 1;
				}
				break;
			case 'hidden':
				if ($_POST['data'] == '1') {
					$model->where(" cate_id = '{$cate_id}' ")->setField("hidden", 0);
					$arr['state'] = 0;
				} else {
					$model->where(" cate_id = '{$cate_id}' ")->setField("hidden", 1);
					$arr['state'] = 1;
				}
				break;
		}
		echo json_encode($arr);
	}
	function categreyadd()
	{
		$shop_categrey = M('shop_categrey');
		if ($_POST) {
			$data = $_POST;
			$upload = new \Think\Upload();
			$upload->rootPath = './Uploads/';
			$upload->maxSize = 3145728;
			$upload->autoSub = false;
			$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
			$upload->savePath = '';
			$upload->autoSub = true;
			$upload->subName = date("Ymd");
			$imginfo = $upload->upload();
			if (!$imginfo && $_POST['type'] == null) {
				$this->error("分类图片不能为空");
				exit;
			}
			if ($imginfo) {
				$data['pic_url'] = "Uploads/" . $imginfo['image']['savepath'] . $imginfo['image']['savename'];
			}
			if ($_POST['type'] == null) {
				$shop_categrey->add($data);
				$this->success("新分类创建成功", categrey);
				exit;
			} else {
				$cate_id = $_POST['type'];
				$shop_categrey->where(" cate_id = '{$cate_id}' ")->save($data);
				$this->success("分类信息保存成功", categrey);
				exit;
			}
		}
		if ($_GET['cate_id']) {
			$cate_info = $shop_categrey->getByCate_id($_GET['cate_id']);
			$this->assign("cate_info", $cate_info);
		}
		$cate_pid = $shop_categrey->where(" pid = 0 ")->select();
		$this->assign("cate_pid", $cate_pid);
		$this->display();
	}
	function del_shop_categrey()
	{
		$cate_id = $_POST['id'];
		M('shop_categrey')->where(" cate_id = '{$cate_id}' ")->delete();
		$arr = array();
		echo json_encode($arr);
	}
	function order()
	{
		$shop_order = M('shop_order');
		$user_address = M('user_address');
		$order_info = $shop_order->select();
		foreach ($order_info as $k => $v) {
			$order_info[$k]['time'] = date("m-d H:i", $v['time']);
			$order_info[$k]['address'] = $user_address->getByUser_id($v['user_id']);
		}
		$this->assign("order_info", $order_info);
		$this->display();
	}
	function order_ajax()
	{
		$shop_order_detail = M('shop_order_detail');
		$shop_order = M('shop_order');
		$user_address = M('user_address');
		if ($_GET['order_sn']) {
			$where = array('order_sn' => $_GET['order_sn']);
		} elseif ($_GET['is_true'] != null) {
			$where = array('is_true' => $_GET['is_true']);
		} elseif ($_GET['state'] != null) {
			$where = array('state' => $_GET['state']);
		} elseif ($_GET['start'] > 0) {
			$where[]['time'] = array('egt', $_GET['start']);
			$where[]['time'] = array('elt', $_GET['end']);
		} else {
			$where = array();
		}
		$pagecount = 10;
		$count = $shop_order->where($where)->count();
		$Page = new \Think\Pageajax($count, $pagecount);
		$order_info = $shop_order->where($where)->order("time desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$Page->setConfig('first', '首页');
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('last', '尾页');
		$Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 ' . I('p', 1) . ' 页/共 %TOTAL_PAGE% 页 ( ' . $pagecount . ' 条/页 共 %TOTAL_ROW% 条)');
		foreach ($order_info as $k => $v) {
			$order_info[$k]['time'] = date("m-d H:i", $v['time']);
			$order_info[$k]['address'] = $user_address->getByUser_id($v['user_id']);
		}
		$show = $Page->show();
		$this->assign('page', $show);
		$this->assign("order_info", $order_info);
		$this->display();
	}
	function order_more()
	{
		$pay_id = $_GET['pay_id'];
		$order = M('shop_order')->getByPay_id($pay_id);
		if ($order['is_true'] == 0) {
			$order['pay_time'] = $order['pay_name'] = $order['state'] = '待付款';
			$order['is_true'] = "待付款";
		} else {
			$order['is_true'] = "已付款";
			$order['pay_time'] = date("Y/m/d H:i:s", $order['pay_time']);
			$order['pay_name'] = "微信支付";
		}
		$order['time'] = date("Y/m/d H:i:s", $order['time']);
		$user_address = M('user_address')->getByUser_id($order['user_id']);
		$order_info = M('shop_order_detail')->where(" pay_id = '{$pay_id}' ")->select();
		foreach ($order_info as $k => $v) {
			$order_info[$k]['good_fee'] = $v['good_price'] * $v['good_num'];
		}
		$this->assign("order", $order);
		$this->assign("user_address", $user_address);
		$this->assign("order_info", $order_info);
		$this->display();
	}
	function order_serve()
	{
		$arr = array();
		$order_id = $_POST['id'];
		$arr['order_id'] = $_POST['id'];
		M('shop_order')->where(" order_id = '{$order_id}' ")->save(array('serve_name' => $_POST['name'], 'serve_id' => $_POST['serve_id'], 'state' => 1));
		echo json_encode($arr);
	}
}