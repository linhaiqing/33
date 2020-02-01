<?php

//decode by http://www.yunlu99.com/
namespace Wxapi\Controller;

use Think\Controller;
class QrimgController extends Controller
{
	function index($user_id, $nickname)
	{
		if ($user_id == 0) {
			$erweima_img = 'Public/2.jpg';
			$head_img = "Public/head.jpg";
		} else {
			$erweima_img = 'Public/qrimg/' . $user_id . '.jpg';
			$head_img = "Public/head_pic/" . $user_id . ".jpg";
		}
		$info = M('qrset')->select();
		$head_height = $head_width = $info[0]['head_size'];
		$erweima_height = $erweima_width = $info[0]['qr_size'];
		$dst_path = $info[0]['pic_url'];
		$str = $nickname;
		$font_size = $info[0]['font_size'];
		$fnt_x = $info[0]['font_x'];
		$fnt_y = $info[0]['font_y'];
		$fnt = "Public/msyh.ttf";
		$src1 = $this->img_suo($head_img, $head_width, $head_height);
		$src = $this->img_suo($erweima_img, $erweima_width, $erweima_height);
		$dst = imagecreatefromstring(file_get_contents($dst_path));
		imagecopymerge($dst, $src1, $info[0]['head_x'], $info[0]['head_y'], 0, 0, $head_width, $head_height, 100);
		imagecopymerge($dst, $src, $info[0]['qr_x'], $info[0]['qr_y'], 0, 0, $erweima_width, $erweima_height, 100);
		$white = imagecolorallocate($dst, 222, 229, 207);
		$black = imagecolorallocate($dst, 50, 50, 50);
		imagettftext($dst, $font_size, 0, $fnt_x + 1, $fnt_y + 1, $black, $fnt, $str);
		imagettftext($dst, $font_size, 0, $fnt_x, $fnt_y, $white, $fnt, $str);
		if (!is_dir('Public/qr_path/')) {
			mkdir('Public/qr_path/');
		}
		ImageJPEG($dst, 'Public/qr_path/' . $user_id . '.jpg');
		ImageDestroy($dst);
		return 'Public/qr_path/' . $user_id . '.jpg';
	}
	function img_suo($img = 'head.jpg', $new_width = 100, $new_height = 100)
	{
		list($width, $height) = getimagesize($img);
		$image_p = imagecreatetruecolor($new_width, $new_height);
		$image = imagecreatefromjpeg($img);
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		return $image_p;
	}
}