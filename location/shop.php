<?php
	
	/* McrShop
	Авторство принадлежит "Toster_tpl"
	Все права защищены.
	Полное или частичное распространение
	кода запрещено правообладателем и
	влечет за собой ответственность по статье 146 УК РФ
	
	Контактная информация предоставляется
	по почте "support@qexy.org" */
if (!defined('MCR')) {header('Location: '.BASE_URL.''); exit;}

$_SESSION['shop_queries'] = 0;

require_once(MCR_ROOT.'shop.cfg.php');

if(empty($user)){
	$player = '';
	$player_lvl = -1;
	$player_money = 0;
	$player_group = -1;
	$player_grname = 'Гость';
}else{
	$player_grname = $user->group();
}



define('S_VERSION', '1.4');
define('S_STYLE', STYLE_URL.'Default/');
define('S_URL_ROOT', BASE_URL.'go/shop/');
define('S_P_NAME', $player);
define('S_P_LVL', $player_lvl);
define('S_P_MONEY', $player_money);
define('S_P_GROUP', $player_group);
define('S_P_GROUPID', $player_grname);

function S_QUERY($query){$_SESSION['shop_queries']++; return BD($query);}

$realmoney = S_QUERY("SELECT realmoney FROM `{$bd_names['iconomy']}` WHERE `{$bd_money['login']}`='$player'");
if(!$realmoney || mysql_num_rows($realmoney)<=0){
	define('S_P_MONEYR', 0);
}else{
	$s_rmar = mysql_fetch_array($realmoney);
	$S_P_MONEYR = floatval($s_rmar['realmoney']);
	define('S_P_MONEYR', $S_P_MONEYR);
}

if(S_P_LVL < 15 && $cfgShop['shop_close']==1) { exit('Магазин закрыт на технические работы'); }

require_once(MCR_ROOT.'instruments/upload.class.php');
require_once(MCR_ROOT.'instruments/shop.class.php');


$Shop = new Shop;
$files_manager = new FileManager('other/');

if (!$Shop->Access()) {header("Location: ".S_URL_ROOT."settings/");exit;}


/* Default vars */

$page = 'Магазин';
$menu->SetItemActive('shop');


if(isset($_GET['add']) && S_P_LVL>=15)
{
	$page .= ' - Добавление предмета';
	$content_main = $Shop->ItemAdd();
	$content_main .= $files_manager->ShowAddForm();
}elseif(isset($_GET['sid'])){
	if(isset($_GET['edt']) && S_P_LVL>=15)
	{
		$page			.= ' - Редактирование предмета';
		$content_main	= $Shop->ItemEdit($_GET['sid']);
		$content_main	.= $files_manager->ShowAddForm();
	}elseif(isset($_GET['del']) && S_P_LVL>=15){
		$page .= ' - Удаление предмета';
		$content_main = $Shop->ItemDelete($_GET['sid']);
	}else{
		$page .= ' - '.$Shop->ItemTitle($_GET['sid']);
		$content_main = $Shop->ItemFull($_GET['sid']);	
	}
}elseif(isset($_GET['set']) && S_P_LVL>=15){
	$page .= ' - Настройки';
	$content_main = $Shop->Settings();
}elseif(isset($_GET['extra']) && S_P_LVL>=15){
	$page .= ' - Дополнительные настройки';
	$content_main = $Shop->Extra();
}elseif(isset($_GET['cart']) && !empty($user)){
	$page .= ' - Корзина';
	$content_main = $Shop->ItemCart();
}elseif(isset($_GET['his']) && !empty($user)){
	$page .= ' - История покупок';
	$content_main = $Shop->ItemHistory();
}elseif(isset($_GET['buy']) && !empty($user)){
	if($cfgShop['use_vaucher']!==1){exit('<p>Функция отключена администрацией</p>');}

	if(isset($_GET['fail'])){
		$page .= ' - Ошибка пополнение счета';
		$content_main = $Shop->KeyBuyFail();
	}elseif(isset($_GET['success'])){
		$page .= ' - Успешное пополнение счета';
		$content_main = $Shop->KeyBuySuccess();
	}else{
		$page .= ' - Пополнение счета';
		$content_main = $Shop->KeyBuy();
	}
}elseif(isset($_GET['did']) && !empty($user)){
	$page .= ' - Пополнение счета.';
	if($cfgShop['use_intkas']!==1){exit('<p>Функция отключена администрацией</p>');}

	if($_GET['did']=='add') {
		$content_main = $Shop->Deposit('add');
	}elseif($_GET['did']=='trans'){
		$content_main = $Shop->DepositTrans();
	}elseif($_GET['did']=='success'){
		$content_main = $Shop->DepositSuccess();
	}elseif($_GET['did']=='fail'){
		$content_main = $Shop->DepositFail();
	}else{
		if(isset($_GET['didedt'])){
			$content_main = $Shop->DepositEdt($_GET['did']);
		}else{
			$content_main = $Shop->Deposit($_GET['did']);
		}
	}
}elseif(isset($_GET['cid']) && $_GET['cid']=='all' && S_P_LVL>=15){
	$page .= ' - Управление категориями';
	$content_main = $Shop->CatArray();
}elseif(isset($_GET['cid']) && $_GET['cid']=='add' && S_P_LVL>=15){
	$page .= ' - Добавление категории';
	$content_main = $Shop->CatAdd();
}elseif(isset($_GET['cid']) && isset($_GET['edt']) && S_P_LVL>=15){
	$page .= ' - Изменение категории';
	$content_main = $Shop->CatEdit($_GET['cid']);
}elseif(isset($_GET['cid']) && isset($_GET['del']) && S_P_LVL>=15){
	$page .= ' - Удаление категории';
	$content_main = $Shop->CatDelete($_GET['cid']);
}elseif(isset($_GET['kid']) && S_P_LVL>=15){
	if($_GET['kid']=='all'){
		$page .= ' - Управление ваучерами';
		$content_main = $Shop->KeyArray();
	}elseif($_GET['kid']=='add'){
		$page .= ' - Добавление ваучера';
		$content_main = $Shop->KeyAdd();
	}elseif(isset($_GET['kid']) && isset($_GET['edt'])){
		$page .= ' - Редактирование ваучера';
		$content_main = $Shop->KeyEdit($_GET['kid']);
	}elseif(isset($_GET['kid']) && isset($_GET['del'])){
		$page .= ' - Удаление ваучера';
		$content_main = $Shop->KeyDelete($_GET['kid']);
	}
}elseif (isset($_GET['permlist']) && S_P_LVL>=15){
	if($cfgShop['use_permlist']!==1){exit('<p>Функция отключена администрацией</p>');}
	if(isset($_GET['del']))
	{
		$page .= ' - Удаление оповещения';
		$content_main = $Shop->PermlistDel($_GET['permlist']);
	}else{
		$page .= ' - Cписка привилегированных';
		$content_main = $Shop->Permlist();
	}
}else{
	$content_main = $Shop->ArrayItems().$Shop->AlertCTU();
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