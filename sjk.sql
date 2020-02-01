-- phpMyAdmin SQL Dump
-- version phpStudy 2014
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2016 年 08 月 03 日 01:42
-- 服务器版本: 5.5.40
-- PHP 版本: 5.3.29

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `ces`
--

-- --------------------------------------------------------

--
-- 表的结构 `wx_admin`
--

CREATE TABLE IF NOT EXISTS `wx_admin` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(18) NOT NULL DEFAULT '',
  `register_time` int(11) NOT NULL DEFAULT '0',
  `phone` varchar(20) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `last_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `wx_admin`
--

INSERT INTO `wx_admin` (`Id`, `username`, `password`, `ip`, `register_time`, `phone`, `email`, `last_time`) VALUES
(1, 'admin', '6299ac6453f4990d466f8759b7baad54', '', 0, '', '', 1470159596);

-- --------------------------------------------------------

--
-- 表的结构 `wx_agent_orders`
--

CREATE TABLE IF NOT EXISTS `wx_agent_orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `order_sn` varchar(32) NOT NULL DEFAULT '',
  `type` int(1) unsigned zerofill NOT NULL DEFAULT '0',
  `bucha` int(1) unsigned NOT NULL DEFAULT '0',
  `total_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `time` int(11) unsigned zerofill NOT NULL DEFAULT '00000000000',
  `openid` varchar(28) NOT NULL DEFAULT '',
  `is_true` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_broke_record`
--

CREATE TABLE IF NOT EXISTS `wx_broke_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `desc` varchar(255) NOT NULL DEFAULT '',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  `type` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '记录类型',
  `fee` decimal(6,2) unsigned DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='返佣金明细表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_chat`
--

CREATE TABLE IF NOT EXISTS `wx_chat` (
  `chat_id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `to_user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`chat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='聊天记录表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_chat_all`
--

CREATE TABLE IF NOT EXISTS `wx_chat_all` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `from_user_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会话列表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_config`
--

CREATE TABLE IF NOT EXISTS `wx_config` (
  `app_id` int(11) NOT NULL AUTO_INCREMENT,
  `wxname` varchar(255) NOT NULL DEFAULT '',
  `wxid` varchar(40) NOT NULL DEFAULT '',
  `weixin` varchar(30) NOT NULL DEFAULT '',
  `appid` varchar(255) NOT NULL DEFAULT '',
  `appsecret` varchar(255) NOT NULL DEFAULT '',
  `machid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `mkey` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`app_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=3 ;

--
-- 转存表中的数据 `wx_config`
--

INSERT INTO `wx_config` (`app_id`, `wxname`, `wxid`, `weixin`, `appid`, `appsecret`, `machid`, `mkey`) VALUES
(1, '星光九渡', '', '', '', '', 0, '');

-- --------------------------------------------------------

--
-- 表的结构 `wx_custom`
--

CREATE TABLE IF NOT EXISTS `wx_custom` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `switch` int(1) NOT NULL DEFAULT '0',
  `keyword` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `wx_custom`
--

INSERT INTO `wx_custom` (`Id`, `switch`, `keyword`, `content`) VALUES
(1, 2, '', '111');

-- --------------------------------------------------------

--
-- 表的结构 `wx_daijin`
--

CREATE TABLE IF NOT EXISTS `wx_daijin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `del_user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_daili_banner`
--

CREATE TABLE IF NOT EXISTS `wx_daili_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pic_url` varchar(255) NOT NULL DEFAULT '',
  `code` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- 转存表中的数据 `wx_daili_banner`
--

INSERT INTO `wx_daili_banner` (`id`, `pic_url`, `code`) VALUES
(1, 'Uploads/20160506/572bf3ce59c2a.jpg', 0);

-- --------------------------------------------------------

--
-- 表的结构 `wx_daili_info`
--

CREATE TABLE IF NOT EXISTS `wx_daili_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(32) NOT NULL DEFAULT '',
  `first_fee` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `second_name` varchar(32) NOT NULL DEFAULT '',
  `second_fee` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `third_name` varchar(32) NOT NULL DEFAULT '',
  `third_fee` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `first_hongbao` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `second_hongbao` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `third_hongbao` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `four_hongbao` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `five_hongbao` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `six_hongbao` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `seven_hongbao` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `eight_hongbao` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `nine_hongbao` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `daili_url` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- 转存表中的数据 `wx_daili_info`
--

INSERT INTO `wx_daili_info` (`id`, `first_name`, `first_fee`, `second_name`, `second_fee`, `third_name`, `third_fee`, `first_hongbao`, `second_hongbao`, `third_hongbao`, `four_hongbao`, `five_hongbao`, `six_hongbao`, `seven_hongbao`, `eight_hongbao`, `nine_hongbao`, `daili_url`) VALUES
(1, '三渡', '100.00', '六渡', '300.00', '九渡', '600.00', '20.00', '25.00', '35.00', '45.00', '55.00', '60.00', '70.00', '80.00', '90.00', '');

-- --------------------------------------------------------

--
-- 表的结构 `wx_good_pic`
--

CREATE TABLE IF NOT EXISTS `wx_good_pic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `good_id` int(11) unsigned NOT NULL DEFAULT '0',
  `pic_url` varchar(255) NOT NULL DEFAULT '',
  `code` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_hbrecord`
--

CREATE TABLE IF NOT EXISTS `wx_hbrecord` (
  `hb_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `order_id` int(11) unsigned NOT NULL DEFAULT '0',
  `from_user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `type` int(1) unsigned NOT NULL DEFAULT '0',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  `hongbao_fee` decimal(6,2) NOT NULL DEFAULT '0.00',
  `last_fee` decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  `is_true` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`hb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_menu`
--

CREATE TABLE IF NOT EXISTS `wx_menu` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `title` varchar(50) NOT NULL DEFAULT '',
  `keyword` varchar(50) NOT NULL DEFAULT '',
  `url` varchar(220) NOT NULL DEFAULT '',
  `is_show` int(1) NOT NULL DEFAULT '1',
  `type` varchar(26) DEFAULT '0',
  `code` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=21 ;

--
-- 转存表中的数据 `wx_menu`
--

INSERT INTO `wx_menu` (`Id`, `pid`, `title`, `keyword`, `url`, `is_show`, `type`, `code`) VALUES
(1, 0, '参与团购', 'rselfmenu_2_0', '', 1, '', 0),
(2, 0, '会员中心', '', 'http://www.5200buy.com/User/Center/index', 1, 'view', 0),
(12, 1, '代理九渡', '', 'http://www.5200buy.com/Agent/Buy/index', 1, 'view', 0),
(14, 1, '参与方式', '', 'http://mp.weixin.qq.com/s?__biz=MzIwMDc2ODQ5MA==&mid=100000002&idx=1&sn=d6d866fb321aa4146bf7192444a29a2e&scene=0&previewkey=p9zmYnKkmeTg8zgPKLo4GsNS9bJajjJKzz%2F0By7ITJA%3D#wechat_redirect', 1, 'view', 11),
(15, 11, '九渡商城', '', '		http://wap.koudaitong.com/v2/feature/1ubljk5a ', 1, 'view', 0),
(16, 0, '九渡商城', '', '		http://wap.koudaitong.com/v2/feature/1ubljk5a ', 1, '', 0),
(18, 16, '走进九渡', '', 'http://mp.weixin.qq.com/s?__biz=MzIwMDc2ODQ5MA==&mid=100000043&idx=1&sn=e23a901ce81c67ce484594b90b048a0d&scene=0&previewkey=p9zmYnKkmeTg8zgPKLo4GsNS9bJajjJKzz%2F0By7ITJA%3D#wechat_redirect', 1, 'view', 0),
(19, 16, '招商合作', '', 'http://mp.weixin.qq.com/s?__biz=MzIwMDc2ODQ5MA==&mid=100000047&idx=1&sn=25cb8f1d7d0d28eca5a3ec75a983570b&scene=0&previewkey=p9zmYnKkmeTg8zgPKLo4GsNS9bJajjJKzz%2F0By7ITJA%3D#wechat_redirect', 1, 'view', 0),
(20, 16, '进入商城', '', 'http://wap.koudaitong.com/v2/feature/1ubljk5a ', 1, 'view', 0);

-- --------------------------------------------------------

--
-- 表的结构 `wx_news`
--

CREATE TABLE IF NOT EXISTS `wx_news` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(255) NOT NULL DEFAULT '',
  `pic_url` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `desc` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `createtime` int(11) unsigned NOT NULL DEFAULT '0',
  `click` int(11) unsigned NOT NULL DEFAULT '0',
  `code` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_qrcode`
--

CREATE TABLE IF NOT EXISTS `wx_qrcode` (
  `scene_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0',
  `media_id` varchar(255) NOT NULL DEFAULT '',
  `created_at` int(11) unsigned NOT NULL DEFAULT '0',
  `use_num` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`scene_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_qrset`
--

CREATE TABLE IF NOT EXISTS `wx_qrset` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `pic_url` varchar(255) NOT NULL DEFAULT '',
  `font_size` int(3) unsigned NOT NULL DEFAULT '0',
  `font_x` smallint(5) unsigned NOT NULL DEFAULT '0',
  `font_y` smallint(5) unsigned NOT NULL DEFAULT '0',
  `head_size` smallint(5) unsigned NOT NULL DEFAULT '0',
  `head_x` smallint(5) unsigned NOT NULL DEFAULT '0',
  `head_y` smallint(5) unsigned NOT NULL DEFAULT '0',
  `qr_size` smallint(5) unsigned NOT NULL DEFAULT '0',
  `qr_x` smallint(5) unsigned NOT NULL DEFAULT '0',
  `qr_y` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `wx_qrset`
--

INSERT INTO `wx_qrset` (`Id`, `pic_url`, `font_size`, `font_x`, `font_y`, `head_size`, `head_x`, `head_y`, `qr_size`, `qr_x`, `qr_y`) VALUES
(1, 'Uploads/20160525/57451e4a70402.png', 25, 350, 61, 100, 60, 30, 370, 134, 454);

-- --------------------------------------------------------

--
-- 表的结构 `wx_question`
--

CREATE TABLE IF NOT EXISTS `wx_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(200) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  `is_true` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户疑难问题' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_shop_ad`
--

CREATE TABLE IF NOT EXISTS `wx_shop_ad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pic_url` varchar(255) NOT NULL DEFAULT '',
  `link` varchar(255) NOT NULL DEFAULT '',
  `code` int(11) unsigned NOT NULL DEFAULT '50',
  `click` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_shop_bannar`
--

CREATE TABLE IF NOT EXISTS `wx_shop_bannar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pic_url` varchar(255) NOT NULL DEFAULT '',
  `code` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_shop_categrey`
--

CREATE TABLE IF NOT EXISTS `wx_shop_categrey` (
  `cate_id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `cate_name` varchar(255) NOT NULL DEFAULT '',
  `code` int(11) unsigned NOT NULL DEFAULT '0',
  `pic_url` varchar(255) NOT NULL DEFAULT '',
  `is_show` int(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否在首页显示',
  `hidden` int(1) NOT NULL DEFAULT '0' COMMENT '是否隐藏该分类',
  PRIMARY KEY (`cate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_shop_goods`
--

CREATE TABLE IF NOT EXISTS `wx_shop_goods` (
  `good_id` int(11) NOT NULL AUTO_INCREMENT,
  `good_name` varchar(255) NOT NULL DEFAULT '',
  `market_price` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `good_price` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `cate_pid` int(11) unsigned NOT NULL DEFAULT '0',
  `cate_gid` int(11) unsigned NOT NULL DEFAULT '0',
  `good_desc` text NOT NULL,
  `code` int(11) unsigned NOT NULL DEFAULT '0',
  `best` int(1) unsigned NOT NULL DEFAULT '0',
  `hot` int(1) unsigned NOT NULL DEFAULT '0',
  `new` int(1) unsigned NOT NULL DEFAULT '0',
  `number` int(11) unsigned NOT NULL DEFAULT '0',
  `is_true` int(1) unsigned NOT NULL DEFAULT '1',
  `good_profit` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `first_per` int(3) unsigned NOT NULL DEFAULT '0',
  `second_per` int(3) unsigned NOT NULL DEFAULT '0',
  `third_per` int(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`good_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_shop_order`
--

CREATE TABLE IF NOT EXISTS `wx_shop_order` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `pay_id` int(11) unsigned NOT NULL DEFAULT '0',
  `order_sn` varchar(18) NOT NULL DEFAULT '',
  `total_fee` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单提交时间',
  `prepay_id` varchar(64) NOT NULL DEFAULT '',
  `serve_name` varchar(20) NOT NULL DEFAULT '',
  `serve_id` varchar(25) NOT NULL DEFAULT '',
  `is_true` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '付款状态',
  `pay_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '付款时间',
  `state` int(1) unsigned NOT NULL DEFAULT '0',
  `pay_type` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_shop_order_detail`
--

CREATE TABLE IF NOT EXISTS `wx_shop_order_detail` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `pay_id` int(11) unsigned NOT NULL DEFAULT '0',
  `order_sn` varchar(18) NOT NULL DEFAULT '',
  `good_id` int(11) unsigned NOT NULL DEFAULT '0',
  `good_name` varchar(50) NOT NULL DEFAULT '',
  `good_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `pic_url` varchar(255) NOT NULL DEFAULT '',
  `good_profit` decimal(10,2) unsigned DEFAULT '0.00',
  `good_num` int(11) unsigned NOT NULL DEFAULT '0',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单详情表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_shop_order_temp`
--

CREATE TABLE IF NOT EXISTS `wx_shop_order_temp` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `good_id` int(11) unsigned NOT NULL DEFAULT '0',
  `good_num` int(11) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户购物车数据' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_subscribe`
--

CREATE TABLE IF NOT EXISTS `wx_subscribe` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `content` varchar(255) NOT NULL DEFAULT '',
  `keyword` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `wx_subscribe`
--

INSERT INTO `wx_subscribe` (`Id`, `content`, `keyword`) VALUES
(1, '九渡商城致力于打造全国最大的互联网+线上、线下三农产品购物商城！让更多的人购买农产品享受优惠，分享农产品得到实惠！让天下没有难卖的农产品！', '');

-- --------------------------------------------------------

--
-- 表的结构 `wx_system`
--

CREATE TABLE IF NOT EXISTS `wx_system` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `last_time` int(11) NOT NULL DEFAULT '0',
  `url_sure` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_text`
--

CREATE TABLE IF NOT EXISTS `wx_text` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `createtime` int(11) NOT NULL DEFAULT '0',
  `click` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_users`
--

CREATE TABLE IF NOT EXISTS `wx_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(28) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `wxid` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `nickname` varchar(255) NOT NULL DEFAULT '',
  `headimgurl` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `sex` int(1) unsigned zerofill NOT NULL DEFAULT '0',
  `province` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `city` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `country` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `subscribe` int(1) unsigned zerofill NOT NULL DEFAULT '0',
  `subscribe_time` int(11) unsigned NOT NULL DEFAULT '0',
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `level` int(11) unsigned NOT NULL DEFAULT '1',
  `agent` int(1) NOT NULL DEFAULT '0',
  `shop_income` decimal(15,2) unsigned NOT NULL DEFAULT '0.00',
  `shop_outcome` decimal(15,2) unsigned NOT NULL DEFAULT '0.00',
  `daijin` int(1) unsigned NOT NULL DEFAULT '0',
  `daili` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  KEY `openid` (`openid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_user_address`
--

CREATE TABLE IF NOT EXISTS `wx_user_address` (
  `address_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `username` varchar(255) NOT NULL DEFAULT '',
  `telphone` varchar(32) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_user_contact`
--

CREATE TABLE IF NOT EXISTS `wx_user_contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `children_id` int(11) unsigned NOT NULL DEFAULT '0',
  `level` int(11) unsigned NOT NULL DEFAULT '0',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户关系数据表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `wx_ztrecord`
--

CREATE TABLE IF NOT EXISTS `wx_ztrecord` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `from_user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `money` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  `is_true` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
