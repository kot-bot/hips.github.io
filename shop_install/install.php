<?/* McrShop
  Авторство принадлежит "Toster_tpl"
  Все права защищены.
  Полное или частичное распространение
  кода запрещено правообладателем и
  влечет за собой ответственность по статье 146 УК РФ
  
  Контактная информация предоставляется
  по почте "support@qexy.org" */
$result1 = BD("CREATE TABLE IF NOT EXISTS `{$config['db_name']}`.`{$bd_names['iconomy']}` (
`id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`username` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`balance` DOUBLE( 64, 2 ) NOT NULL DEFAULT  '0.00',
`realmoney` DOUBLE( 64, 2 ) NOT NULL DEFAULT  '0.00',
`bank` DOUBLE( 64, 2 ) NOT NULL DEFAULT  '0.00'
) ENGINE = MYISAM ;");

$result2 = BD("CREATE TABLE IF NOT EXISTS `{$config['db_name']}`.`{$cfgShop['tbl_cont']}` (
`id` INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
`enddate` bigint(20) DEFAULT '0', 
`cid` INT(8) NOT NULL DEFAULT '1', 
`iid` VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
`title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
`img` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '/style/img/shop/none.png', 
`description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
`price` DOUBLE(64,2) NOT NULL, 
`realprice` tinyint(1) NOT NULL DEFAULT '0',
`discount` DOUBLE(64,2) NOT NULL DEFAULT '0.00', 
`extra` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
`num` INT(10) NOT NULL DEFAULT '1' 
) ENGINE = MyISAM;");

$result3 = BD("CREATE TABLE `{$config['db_name']}`.`{$cfgShop['tbl_cat']}` (
`id` INT( 8 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`value` VARCHAR( 80 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
`title` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE = MYISAM;");

$result4 = BD("INSERT INTO `{$config['db_name']}`.`{$cfgShop['tbl_cat']}` (`value`, `title`) VALUES ('item', 'Default');");
if(self::MAR()!=1){exit('[Error] '.mysql_error());}

$result5 = BD("CREATE TABLE  IF NOT EXISTS `{$config['db_name']}`.`{$cfgShop['tbl_cart']}` (
`id` INT( 8 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`sid` INT( 8 ) NOT NULL ,
`username` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`iid` VARCHAR( 80 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`title` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`extra` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
`type` VARCHAR( 80 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`amount` INT( 8 ) NOT NULL
) ENGINE = MYISAM ;");

$result6 = BD("CREATE TABLE  IF NOT EXISTS `{$config['db_name']}`.`{$cfgShop['tbl_hist']}` (
`id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`username` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`title` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`sid` INT( 10 ) NOT NULL ,
`iid` VARCHAR( 80 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`price` DOUBLE( 64, 2 ) NOT NULL ,
`amount` INT( 10 ) NOT NULL
) ENGINE = MYISAM ;");

$result7 = BD("CREATE TABLE IF NOT EXISTS `{$config['db_name']}`.`{$cfgShop['tbl_keys']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `key` varchar(30) NOT NULL,
  `sum` double(64,2) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  `username` varchar(40) NOT NULL,
  `link` varchar(255) NOT NULL,
  `realmoney` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;");

$result8 = BD("CREATE TABLE IF NOT EXISTS `{$config['db_name']}`.`{$cfgShop['tbl_temp']}` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `buytime` bigint(20) NOT NULL,
  `endtime` bigint(20) NOT NULL,
  `tempgroup` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;");

$result9 = BD("CREATE TABLE IF NOT EXISTS `{$config['db_name']}`.`{$cfgShop['tbl_trans']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET latin1 NOT NULL,
  `amount` double(64,2) NOT NULL,
  `paid` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=56 ;");

$result10 = BD("CREATE TABLE IF NOT EXISTS `{$config['db_name']}`.`{$cfgShop['tbl_trlog']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `ik_shop_id` varchar(50) NOT NULL DEFAULT 'error',
  `ik_payment_id` int(10) NOT NULL DEFAULT '0',
  `ik_paysystem_alias` varchar(50) NOT NULL DEFAULT 'error',
  `ik_payment_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `ik_currency_exch` varchar(20) NOT NULL DEFAULT 'error',
  `ik_trans_id` varchar(30) NOT NULL DEFAULT 'error',
  `ik_payment_amount` varchar(30) NOT NULL DEFAULT 'error',
  `ik_payment_state` varchar(10) NOT NULL DEFAULT 'fail',
  `ik_sign_hash` varchar(50) NOT NULL DEFAULT 'error',
  `reserve` varchar(255) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

switch (true) {
  case (!$result1):
  case (!$result2):
  case (!$result3):
  case (!$result4):
  case (!$result5):
  case (!$result6):
  case (!$result7):
  case (!$result8):
  case (!$result9):
  case (!$result10):
    return false;
    break;
}

/* McrShop
  Авторство принадлежит "Toster_tpl"
  Все права защищены.
  Полное или частичное распространение
  кода запрещено правообладателем и
  влечет за собой ответственность по статье 146 УК РФ
  
  Контактная информация предоставляется
  по почте "support@qexy.org" */
?>