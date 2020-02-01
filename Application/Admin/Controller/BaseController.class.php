<?php

//decode by http://www.yunlu99.com/
namespace Admin\Controller;

use Think\Controller;
class BaseController extends ActionController
{
	function __construct()
	{
		parent::__construct();
		if (!session('admin_id')) {
			$this->error('请登录', U('User/index'));
		} else {
			$this->admin_id = session('admin_id');
		}
	}
	function config()
	{
		if ($_POST) {
			$upload = new \Think\Upload();
			$upload->rootPath = './Application/Wxapi/Controller/';
			$upload->saveName = '';
			$upload->autoSub = false;
			$upload->maxSize = 3145728;
			$upload->exts = array('pem');
			$upload->savePath = '/zhengshu/';
			$info = $upload->upload();
			$config = M('config');
			$info = $config->select();
			if ($info[0]['app_id']) {
				$app_id = $info[0]['app_id'];
				$res = $config->where(" app_id = '{$app_id}' ")->save($_POST);
			} else {
				$res = $config->add($_POST);
			}
			if ($res) {
				$this->success("保存数据成功");
			} else {
				$this->error("未发现数据更改，保存失败");
			}
		} else {
			$info = M('config')->select();
			F('config_info', $info, DATA_ROOT);
			$this->assign("info", $info[0]);
			$this->display();
		}
	}
	function menu()
	{
		$pinfo = M('menu')->where("pid = 0 ")->order('code desc')->select();
		foreach ($pinfo as $key => $value) {
			$info = M('menu')->where("pid = '{$value['id']}' ")->order('code desc')->select();
			$res[] = array("title" => $value['title'], "type" => $value['type'], "keyword" => $value['keyword'], "is_show" => $value['is_show'], "url" => $value['url'], "code" => $value['code'], "title" => $value['title'], "list" => $info, "id" => $value['id']);
		}
		$result = M('menu')->field("title,id")->where("pid = 0 and is_show = 1 ")->order('code desc')->select();
		foreach ($result as $key => $ccc) {
			$result1 = M('menu')->field("title,id")->where("pid = '{$ccc['id']}' and is_show = 1 ")->order('code desc')->select();
			$ress[] = array("title" => $ccc['title'], "id" => $ccc['id'], "list" => $result1);
		}
		$num = count($ress);
		$this->assign('pinfo', $pinfo);
		$this->assign('num', $num);
		$this->assign('ress', $ress);
		$this->assign('info', $res);
		$this->display();
	}
	function menusave()
	{
		$pinfo = M('menu')->where("pid = 0 and is_show = 1 ")->order('code desc')->select();
		foreach ($pinfo as $key => $value) {
			$arr = "";
			$res = M('menu')->where("pid = {$value['id']} and is_show = 1 ")->order('code desc')->select();
			if ($res) {
				foreach ($res as $kk => $vv) {
					$arr[] = array("type" => $vv['type'], "name" => urlencode($vv['title']), "url" => urlencode($vv[url]), "key" => urlencode($vv['keyword']));
				}
				$arr1[] = array("name" => urlencode($value['title']), "sub_button" => $arr);
			} else {
				$arr1[] = array("type" => $value['type'], "name" => urlencode($value['title']), "url" => urlencode($value['url']), "key" => urlencode($value['keyword']));
			}
		}
		$botton = array("button" => $arr1);
		$aaa = urldecode(json_encode($botton));
		$weixin = A('Wxapi/Weixin');
		$weixin->__construct($this->token);
		$info = $weixin->send_menu($aaa);
		if ($info['errmsg'] == "invalid button name size") {
			$this->error("菜单按钮字数过多");
		} elseif ($info['errmsg'] == "ok") {
			$this->success("成功更新自定义菜单", U('menu'));
		} else {
			$this->error("出现错误，错误原因:" . $info['errmsg'] . $info['errcode']);
		}
	}
	function menuedit()
	{
		if ($_POST) {
			$data = $this->check($_POST);
			$res = M('menu')->where("id = '{$_POST['id']}' ")->save($data);
			$this->redirect('cg');
		} else {
			$menu_id = $_GET['menu_id'];
			$info = M('menu')->where("id = '{$menu_id}' ")->select();
			$pid = $info[0][pid];
			$pidtitle = M('menu')->field("title,id")->where("id = '{$pid}' ")->select();
			$pinfo = M('menu')->field("title,id")->where("pid = 0 ")->select();
			$pinfonum = M('menu')->where("pid = 0 ")->count();
			$ppinfo = "";
			foreach ($pinfo as $key => $value) {
				if ($value['id'] != $pid) {
					$ppinfo[] = $value;
				}
			}
			$this->assign("pidtitle", $pidtitle);
			$this->assign("info", $info);
			$this->assign("pinfo", $ppinfo);
			$this->assign("pinfonum", $pinfonum);
			$this->display();
		}
	}
	function menuadd()
	{
		if ($_POST) {
			if ($_POST['pid'] == 0) {
				$info = M('menu')->where(" pid = 0 and is_show = 1 ")->count();
				if ($info >= 3) {
					$this->error('一级菜单数量已经达到上限');
					exit;
				}
			}
			$data = $this->check($_POST);
			$res = M('menu')->add($data);
			if ($res) {
				$this->success("添加成功", "menu");
			} else {
				$this->error('添加失败');
			}
		}
	}
	function deletemenu()
	{
		$menu_id = $_POST['id'];
		$res = M('menu')->where(" id = '{$menu_id}' ")->delete();
		if ($res) {
			$arr['success'] = 1;
		} else {
			$arr['success'] = 0;
		}
		echo json_encode($arr);
	}
	function check($arr)
	{
		if ($arr['type'] == "click") {
			$arr['url'] = "";
		} elseif ($arr['type'] == "view") {
			$arr['keyword'] = "";
		} elseif ($arr['type'] == "scancode_push") {
			$arr['keyword'] = "rselfmenu_0_1";
			$arr['url'] = "";
		} elseif ($arr['type'] == "scancode_waitmsg") {
			$arr['keyword'] = "rselfmenu_0_0";
			$arr['url'] = "";
		} elseif ($arr['type'] == "pic_sysphoto") {
			$arr['keyword'] = "rselfmenu_1_0";
			$arr['url'] = "";
		} elseif ($arr['type'] == "pic_photo_or_album") {
			$arr['keyword'] = "rselfmenu_1_1";
			$arr['url'] = "";
		} elseif ($arr['type'] == "pic_weixin") {
			$arr['keyword'] = "rselfmenu_1_2";
			$arr['url'] = "";
		} elseif ($arr['type'] == "location_select") {
			$arr['keyword'] = "rselfmenu_2_0";
			$arr['url'] = "";
		}
		return $arr;
	}
	function subscribe()
	{
		if ($_POST) {
			$data = array('content' => $_POST['content']);
			$subscribe = M('subscribe');
			$info = $subscribe->select();
			if ($info != null) {
				$id = $info[0]['id'];
				$res = $subscribe->where(" id = '{$id}' ")->save($data);
			} else {
				$res = $subscribe->add($data);
			}
			if ($res) {
				$this->success("保存成功", subscribe);
			} else {
				$this->error("未发现数据更改，保存失败");
			}
		} else {
			$info = M('subscribe')->select();
			$this->assign("info", $info);
			$this->display();
		}
	}
	function text()
	{
		$text = M('text');
		$count = $text->count();
		$pagecount = 10;
		$Page = new \Think\Page($count, $pagecount);
		$Page->setConfig('first', '首页');
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('last', '尾页');
		$Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 ' . I('p', 1) . ' 页/共 %TOTAL_PAGE% 页 ( ' . $pagecount . ' 条/页 共 %TOTAL_ROW% 条)');
		$info = $text->order("createtime desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$show = $Page->show();
		$this->assign('page', $show);
		foreach ($info as $key => $value) {
			$info[$key]['createtime'] = date("Y-m-d H:i:s", $value['createtime']);
		}
		$this->assign("info", $info);
		$this->display();
	}
	function textadd()
	{
		if ($_POST) {
			if ($_POST['id']) {
				$id = $_POST['id'];
				$data = array('keyword' => $_POST['keyword'], 'content' => $_POST['content']);
				$res = M('text')->where(" id = '{$id}' ")->setField($data);
				if ($res) {
					$this->success("修改成功", text);
				} else {
					$this->error("未发现数据更改，保存失败");
				}
			} else {
				$data = $_POST;
				$data['createtime'] = time();
				$res = M('text')->add($data);
				if ($res) {
					$this->success("添加成功", text);
				} else {
					$this->error("添加失败");
				}
			}
		} else {
			if ($_GET['text_id']) {
				$id = $_GET['text_id'];
				$info = M('text')->where(" id = '{$id}' ")->select();
				$this->assign("info", $info);
			}
			$this->display();
		}
	}
	function deltext()
	{
		$text_id = $_POST['id'];
		$res = M('text')->where(" id = '{$text_id}' ")->delete();
		if ($res) {
			$arr['success'] = 1;
		} else {
			$arr['success'] = 0;
		}
		echo json_encode($arr);
	}
	function news()
	{
		$count = M('news')->count();
		$pagecount = 10;
		$Page = new \Think\Page($count, $pagecount);
		$Page->setConfig('first', '首页');
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('last', '尾页');
		$Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 ' . I('p', 1) . ' 页/共 %TOTAL_PAGE% 页 ( ' . $pagecount . ' 条/页 共 %TOTAL_ROW% 条)');
		$info = M('news')->order("createtime desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$show = $Page->show();
		$this->assign('page', $show);
		foreach ($info as $key => $value) {
			$info[$key]['createtime'] = date("Y-m-d H:i:s", $value['createtime']);
		}
		$this->assign("info", $info);
		$this->display();
	}
	function newsadd()
	{
		if ($_POST) {
			$upload = new \Think\Upload();
			$upload->rootPath = './Uploads/';
			$upload->maxSize = 3145728;
			$upload->autoSub = false;
			$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
			$upload->savePath = '';
			$upload->autoSub = true;
			$upload->subName = session('token');
			$imginfo = $upload->upload();
			$data = $_POST;
			if ($imginfo) {
				$imgres = __ROOT__ . "/Uploads/" . $imginfo["image1"]["savepath"] . $imginfo["image1"]["savename"];
				$data['pic_url'] = $imgres;
			}
			if ($_POST['id']) {
				$id = $_POST['id'];
				$res = M('news')->where(" id = '{$id}' ")->setField($data);
				if ($res) {
					$this->success("修改成功", news);
				} else {
					$this->error("未发现数据更改，保存失败");
				}
			} else {
				$data['createtime'] = time();
				$res = M('news')->add($data);
				if ($res) {
					$this->success("添加成功", news);
				} else {
					$this->error("添加失败");
				}
			}
		} else {
			if ($_GET['news_id']) {
				$id = $_GET['news_id'];
				$info = M('news')->where(" id = '{$id}' ")->select();
				$this->assign("info", $info);
			}
			$this->display();
		}
	}
	function delnews()
	{
		$news_id = $_POST['id'];
		$res = M('news')->where(" id = '{$news_id}' ")->delete();
		if ($res) {
			$arr['success'] = 1;
		} else {
			$arr['success'] = 0;
		}
		echo json_encode($arr);
	}
	function custom()
	{
		if ($_POST) {
			$data = $_POST;
			if ($_POST['id']) {
				$id = $_POST['id'];
				$res = M('custom')->where(" id = '{$id}' ")->setField($data);
			} else {
				$res = M('custom')->add($data);
			}
			if ($res) {
				$this->success("保存成功", custom);
			} else {
				$this->error("未发现数据更改，保存失败");
			}
		} else {
			$info = M('custom')->select();
			$this->assign("info", $info);
			$this->display();
		}
	}
	function cg()
	{
		$this->display();
	}
}