<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (!defined('MCR')) exit;

require_once(MCR_ROOT.'shop.cfg.php');

/* Default vars */

$page = 'Служебная страница';
$menu->SetItemActive('status');
$ikUserIP = $_SERVER['REMOTE_ADDR'];

if($cfgShop['use_intkas']!==1){exit('<p>Функция отключена администрацией</p>');}

if($_SERVER['REQUEST_METHOD']=='POST'){
	if(!isset($_POST['ik_sign'])){ exit('<p>Access denied</p>'); }
	$post = $_POST;
	$ik_sign = mysql_real_escape_string($_POST['ik_sign']);
	unset($post['ik_sign']);
}else{
	exit('<p>Access denied</p>');
}

ksort($post, SORT_STRING);
array_push($post, $cfgShop['secret_key']);
$post_hash = implode(':', $post);

$post_hash = base64_encode(md5($post_hash, true));

if($post_hash !== $ik_sign){
	BD("INSERT INTO `{$cfgShop['tbl_trlog']}` (`reserve`) VALUES ('[Внимание! Перехват данных. IP - $ikUserIP] Ошибка id [STR: 32]')"); exit('Error');
}

$shop_pm_no = intval($post['ik_pm_no']);

$query = BD("SELECT * FROM `{$cfgShop['tbl_trans']}` WHERE id='$shop_pm_no'");

if(!$query || mysql_num_rows($query) <= 0){
	BD("INSERT INTO `{$cfgShop['tbl_trlog']}` (`reserve`) VALUES ('[Внимание! Перехват данных. IP - $ikUserIP] Ошибка id ($shop_pm_no) [STR: 40]')"); exit('Error');
}

$ar = mysql_fetch_array($query);
$shop_am = mysql_real_escape_string($ar['amount']);
$shop_username = mysql_real_escape_string($ar['username']);
$shop_co_id = $cfgShop['shop_id'];

$ik_am = mysql_real_escape_string($post['ik_am']);
$ik_inv_st = mysql_real_escape_string($post['ik_inv_st']);
$ik_co_id = mysql_real_escape_string($post['ik_co_id']);
$ik_cur = mysql_real_escape_string($post['ik_cur']);
$ik_inv_prc = strtotime($post['ik_inv_prc']);
$ik_trn_id = mysql_real_escape_string($post['ik_trn_id']);
$post_hash = mysql_real_escape_string($post_hash);

$shop_hash = array(
	'ik_am'			=> $shop_am,
	'ik_co_id'		=> $shop_co_id,
	'ik_co_prs_id'	=> $post['ik_co_prs_id'],
	'ik_co_rfn'		=> $post['ik_co_rfn'],
	'ik_cur'		=> 'RUB',
	'ik_desc'		=> $post['ik_desc'],
	'ik_inv_crt'	=> $post['ik_inv_crt'],
	'ik_inv_id'		=> $post['ik_inv_id'],
	'ik_inv_prc'	=> $post['ik_inv_prc'],
	'ik_inv_st'		=> 'success',
	'ik_pm_no'		=> $shop_pm_no,
	'ik_ps_price'	=> $post['ik_ps_price'],
	'ik_pw_via'		=> $post['ik_pw_via'],
	'ik_trn_id'		=> $post['ik_trn_id'],
);

ksort($shop_hash, SORT_STRING);
array_push($shop_hash, $cfgShop['secret_key']);
$shop_sign = implode(':', $shop_hash);
$shop_sign = base64_encode(md5($shop_sign, true));
$shop_sign = mysql_real_escape_string($shop_sign);

if($ik_co_id !== $shop_co_id){
	BD("INSERT INTO `{$cfgShop['tbl_trlog']}` (`reserve`) VALUES ('[Внимание! Возможен перехват данных. IP - $ikUserIP] Ошибка идентификатора магазина')"); exit('Error');
}elseif($ik_am < $shop_am){
	BD("INSERT INTO `{$cfgShop['tbl_trlog']}` (`reserve`) VALUES ('[Внимание! Перехват данных. IP - $ikUserIP] Ошибка суммы платежа')"); exit('Error');
}elseif($ik_inv_st !== 'success'){
	BD("INSERT INTO `{$cfgShop['tbl_trlog']}` (`reserve`) VALUES ('[Внимание! Перехват данных. IP - $ikUserIP] Ошибка статуса платежа')"); exit('Error');
}elseif($shop_sign !== $post_hash){
	BD("INSERT INTO `{$cfgShop['tbl_trlog']}` (`reserve`) VALUES ('[Внимание! Перехват данных. IP - $ikUserIP] Ошибка контроля суммы - $post_hash ($shop_sign)')"); exit('Error');
}elseif($shop_sign === $post_hash){
	$EndOfStatus = BD("UPDATE `{$cfgShop['tbl_trans']}` SET `paid`='1' WHERE `id`='$shop_pm_no' AND `username`='$shop_username'");
}

if(is_bool($EndOfStatus) && $EndOfStatus && mysql_affected_rows()==1){
	$ikbalance = 'realmoney'; // $bd_money['money']
	$shop_am = $shop_am*$cfgShop['cur_multip'];
	$updBalance = BD("UPDATE `{$bd_names['iconomy']}` SET `$ikbalance`=$ikbalance+$shop_am WHERE `{$bd_money['login']}`='$shop_username'");
	if(is_bool($updBalance) && $updBalance && mysql_affected_rows()==1)
	{
		$logTrans = BD("INSERT INTO `{$cfgShop['tbl_trlog']}`
			(	`ik_shop_id`		,	`ik_payment_id`			,
				`ik_paysystem_alias`,	`ik_payment_timestamp`	,
				`ik_currency_exch`	,	`ik_trans_id`			,
				`ik_payment_amount`	,	`ik_payment_state`		,
				`ik_sign_hash`		)
			VALUES
			(	'$ik_co_id'	,	'$shop_pm_no',
				'$ik_cur'	,	'$ik_inv_prc',
				'0'			,	'$ik_trn_id' ,
				'$shop_am'	,	'$ik_inv_st' ,
				'$post_hash'	)");

		if(!is_bool($logTrans) || !$logTrans || mysql_affected_rows()!==1){
			BD("INSERT INTO `{$cfgShop['tbl_trlog']}` (`reserve`) VALUES ('Ошибка запроса [STR: 109]')"); exit('Error');
		}
	}else{
		BD("INSERT INTO `{$cfgShop['tbl_trlog']}` (`reserve`) VALUES ('[Внимание! Ошибка!] Неверно указанны данные в запросе [STR: 113]')"); exit('Error');
	}
}else{
	BD("INSERT INTO `{$cfgShop['tbl_trlog']}` (`reserve`) VALUES ('[Внимание! Ошибка!] Неверно указанны данные в запросе [STR: 116]')"); exit('Error');
}

exit('Success');

?>