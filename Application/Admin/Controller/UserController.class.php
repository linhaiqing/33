<?php
//decode by http://www.yunlu99.com/
namespace Admin\Controller;
class UserController extends ActionController
{
    function index()
    {
        if ($_POST) {
            $arr      = array();
            $username = $_POST['username'];
            $password = $_POST['password'];
            $info     = M('admin')->where(" username = '$username' ")->select();
            if ($info[0]['password'] == md5($password)) {
                session(null);
                session('admin_id', $info[0]['id']);
                $admin_id       = $info[0]['id'];
                $info           = M('admin')->where(" id = '$admin_id' ")->setField('last_time', time());
                $arr['success'] = 1;
            } else {
                $arr['success'] = 0;
            }
            echo json_encode($arr);
        } else {
            $haojifen = cookie("haojifen");
            $this->assign("haojifen", $haojifen);
            $this->display();
        }
    }

    function test()
    {
        $this->display();
    }
}
