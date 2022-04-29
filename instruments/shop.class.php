<?php
	/* McrShop
	Авторство принадлежит "Toster_tpl"
	Все права защищены.
	Полное или частичное распространение
	кода запрещено правообладателем и
	влечет за собой ответственность по статье 146 УК РФ
	
	Контактная информация предоставляется
	по почте "support@qexy.org" */
class Shop
{
	private static function buyid(){return '9b111d7825cb49dbee68e91d6b113b58';}
	private static function MFA($result){return mysql_fetch_array($result);}
	private static function MNR($result){return mysql_num_rows($result);}
	private static function MRES($result){return trim(mysql_real_escape_string($result));}
	private static function ST($result){return trim(strip_tags($result));}
	private static function HSC($result){return htmlspecialchars($result, ENT_NOQUOTES, 'UTF-8');}
	private static function MAR(){return mysql_affected_rows();}
	private static function goToS($url){header('Location: '.S_URL_ROOT.$url); exit;}
	private static function go404(){header('Location: '.BASE_URL.'404'); exit;}
	
	public function Access()
	{
		global $cfgShop;
		if($cfgShop['install']!==false && !isset($_GET['set']))
		{$result = false;}else{$result = true;}
		return $result;
	}
	
	private static function Install()
	{
		global $cfgShop,$config,$bd_names,$user;
		if(empty($user)){return false;}
		if($cfgShop['install']){include(MCR_ROOT."shop_install/install.php");}
		return true;
	}
	
	private static function InfoMessage($mes,$col)
	{
		global $cfgShop;
		ob_start();
		include(S_STYLE.$cfgShop['style']."InfoMessage.html");
		return ob_get_clean();
	}
	
	public function ItemTitle($sid) // Получение название предмета (для названия страницы) Start
	{
		global $cfgShop;
		$sid = intval($sid);
		$result = S_QUERY("SELECT title FROM `{$cfgShop['tbl_cont']}` WHERE id='$sid'");
		if($result && self::MNR($result)>0){$array = self::MFA($result); $title = self::HSC($array['title']);}else{$title = 'Default';}
		return $title;
	} // Получение название предмета (для названия страницы) End
	
	private static function PaginItems($from, $rop, $page) // Постраничная навигация
	{
		global $cfgShop;
		ob_start();
		if(isset($_GET['cid'])){$cidID = intval($_GET['cid']);}else{$cidID = false;}
		if($cidID){$cid="WHERE cid='$cidID'";}else{$cid="";}
		if($page!=''){$where = " WHERE username='".S_P_NAME."'"; $page = $page.'/';}else{$where="";}

		$resultPgs = S_QUERY("SELECT id FROM `".$from."` $cid ".$where."");
		
		if($resultPgs && self::MNR($resultPgs)<=0){$resultPgs = S_QUERY("SELECT id FROM `".$from."` ".$where."");}

		$TotalPgs = intval(ceil(self::MNR($resultPgs) / $rop));

		if(isset($_GET['pid']) && $_GET['pid']>0){$pageID = intval($_GET['pid']);}else{$pageID = 1;}
		if($cidID){$catID = 'c.'.$cidID.'/';}else{$catID='';}
		if($TotalPgs>1)
		{
			if($pageID<=0 || $pageID>$TotalPgs){$pageID=1;}
			
			if($pageID==1){$FirstPge='<li class="disabled"><a href="javascript://"><<</a></li>';}else{$FirstPge='<li><a href="'.S_URL_ROOT.$page.'"><<</a></li>';}
			if($pageID-2>0){$Prev2Pge='<li><a href="'.S_URL_ROOT.$page.$catID.'p.'.($pageID-2).'">'.($pageID-2).'</a></li>';}else{$Prev2Pge='';}
			if($pageID-1>0){$PrevPge='<li><a href="'.S_URL_ROOT.$page.$catID.'p.'.($pageID-1).'">'.($pageID-1).'</a></li>';}else{$PrevPge='';}
			$SelectPge = '<li class="disabled"><a href="javascript://">'.$pageID.'</a></li>';
			if($pageID+1<=$TotalPgs){$NextPge='<li><a href="'.S_URL_ROOT.$page.$catID.'p.'.($pageID+1).'">'.($pageID+1).'</a></li>';}else{$NextPge='';}
			if($pageID+2<=$TotalPgs){$Next2Pge='<li><a href="'.S_URL_ROOT.$page.$catID.'p.'.($pageID+2).'">'.($pageID+2).'</a></li>';}else{$Next2Pge='';}
			if($pageID==$TotalPgs){$LastPge='<li class="disabled"><a href="javascript://">>></a></li>';}else{$LastPge='<li><a href="'.S_URL_ROOT.$page.$catID.'p.'.$TotalPgs.'">>></a></li>';}
			
			include(S_STYLE.$cfgShop['style']."PaginItems.html");
		}
		return ob_get_clean();
	} // Постраничная навигация
	
	private static function BtnHead()
	{
		global $cfgShop;
		ob_start();
		if(S_P_LVL>=15)
		{
			include_once(S_STYLE.$cfgShop['style']."BtnHead.html");
		}else{
			include_once(S_STYLE.$cfgShop['style']."BtnHeadUser.html");
		}
		return ob_get_clean();
	}
	
	private static function BtnEdit($id)
	{
		global $cfgShop;
		ob_start();
		$id = intval($id);
		if(S_P_LVL>=15){include(S_STYLE.$cfgShop['style']."BtnEdit.html");}
		return ob_get_clean();
	}
	
	private static function Items()
	{
		global $cfgShop;
		
		if(isset($_GET['pid']) && $_GET['pid']>0){$pageID = intval($_GET['pid']);}else{$pageID = 1;}
		if(isset($_GET['cid'])){$cidID = intval($_GET['cid']);}else{$cidID = false;}
		if($cidID){$cid="WHERE cid='$cidID'";}else{$cid="";}
		
		ob_start();
		
		$start = $pageID * $cfgShop['r_op_items'] - $cfgShop['r_op_items'];
		if($cfgShop['s_or_items']===1){$orderby = "DESC";}else{$orderby="ASC";}
		$result = S_QUERY("SELECT id,title,img,price,discount,realprice
							FROM `{$cfgShop['tbl_cont']}` $cid
							ORDER BY {$cfgShop['s_by_items']} $orderby
							LIMIT $start, {$cfgShop['r_op_items']}");
		
		if($result && self::MNR($result)>0)
		{
			while($Shop = self::MFA($result))
			{
				$id			= intval($Shop['id']);
				$title		= self::HSC($Shop['title']);
				$img		= self::HSC($Shop['img']);
				$price		= floatval($Shop['price']);
				$disc		= floatval($Shop['discount']);
				$curr		= intval($Shop['realprice']);
				$currency	= ($curr==1) ? $cfgShop['currency2'] : $cfgShop['currency'];
				if(!empty($disc)){$price = '<s style="position:absolute; padding:12px 0 0 0;">'.$price.'</s> <font color="#CC0000">'.($price-$disc).'</font>';}
				include(S_STYLE.$cfgShop['style']."Item.html");
			}
			echo self::PaginItems($cfgShop['tbl_cont'], $cfgShop['r_op_items'], '');
		}else{
			echo "<center>Нет предметов</center>";
		}
		
		return ob_get_clean();
	}
	
	private static function ItemAddCatList($cid = 1)
	{
		global $cfgShop;
		ob_start();
		$result		= S_QUERY("SELECT * FROM {$cfgShop['tbl_cat']}");
		$CatList	= "";
		while($Item = self::MFA($result))
		{
			$id = intval($Item['id']);
			$title = self::HSC($Item['title']);
			$value = self::HSC($Item['title']);
			if($id==$cid){$selected='selected';}else{$selected = '';}
			$CatList .= '<option '.$selected.' value="'.$id.'">'.$title.'</option>';
		}
		
		return $CatList.ob_get_clean();
	}
	
	private static function ItemAddGroupList($gid = 1)
	{
		global $bd_names;
		ob_start();
		$result		= S_QUERY("SELECT id,name FROM {$bd_names['groups']}");
		$GroupList	= "";
		
		while($Group= self::MFA($result))
		{
			$id		= intval($Group['id'])		;
			$title	= self::HSC($Group['name']);
			if($id==$gid){$selected='selected';}else{$selected = '';}
			$GroupList .= '<option '.$selected.' value="'.$id.'">'.$title.'</option>';
		}
		
		return $GroupList.ob_get_clean();
	}
	
	public function ArrayItems()
	{
		global $cfgShop;
		ob_start();
		
		$ArrayItems = self::Items();
		include_once(S_STYLE.$cfgShop['style'].'Shop.html');
		
		if(isset($_GET['cid'])){$cid = intval($_GET['cid']);}else{$cid = 1;}
		$CatList = self::ItemAddCatList($cid);
		include_once(S_STYLE.$cfgShop['style'].'FootCats.html');
		
		return ob_get_clean();
	}
	
	public function ItemAdd() // Добавление предметов Start
	{
		global $cfgShop,$bd_users;
		ob_start();
		$func	= 'add';
		$btn	= 'Добавить';
		$idsid	= '';
		
		$cid = $iid = $title = $img = $date = $desc = $price = $disc = $extra = $num = $real = '';
		// Обязательные поля
		if(isset($_POST['ItemTitle'],
				$_POST['ItemImg'],
				$_POST['ItemDesc'],
				$_POST['ItemPrice'],
				$_POST['ItemCid'],
				$_POST['ItemNum'],
				$_POST['ItemDisc']))
		{
			if(empty($_POST['ItemTitle'])	||
				empty($_POST['ItemCid'])	||
				empty($_POST['ItemPrice'])	||
				empty($_POST['ItemNum'])	||
				$_POST['ItemPrice']<0		||
				$_POST['ItemNum']<=0)
			{
				$_SESSION['infoadd'] = self::InfoMessage('Неверно заполнено одно из полей', 'error');
				header("Location: ".S_URL_ROOT."add");exit;
			}

			if(isset($_POST['ItemDate']) && !empty($_POST['ItemDate']))
			{
				$date = intval($_POST['ItemDate']);
				if($date < 0)
				{
					$_SESSION['infoadd'] = self::InfoMessage('Ошибка! "Оповещение" должно быть больше или равняться нулю', 'error');
					header("Location: ".S_URL_ROOT."add");exit;
				}
			}else{$date = 0;}

			if(isset($_POST['ItemReal']) && intval($_POST['ItemReal'])==1){$real = 1;}else{$real = 0;}

			$cid	= intval($_POST['ItemCid']);
			$title	= self::MRES($_POST['ItemTitle']);
			$img	= trim($_POST['ItemImg']);
			$imgar	= @get_headers($_POST['ItemImg']);
			if(empty($img) || $imgar[0]!='HTTP/1.1 200 OK'){
				$img= BASE_URL.'style/Default/img/shop/none.png';
			}
			$desc	= self::MRES($_POST['ItemDesc']);
			$price	= floatval($_POST['ItemPrice']);
			$disc	= floatval($_POST['ItemDisc']);
			$num	= intval($_POST['ItemNum']);
			

			if(!empty($disc) && ($disc>$price || $disc<0))
			{
				$_SESSION['infoadd'] = self::InfoMessage('Скидка не может быть больше цены или меньше нуля!', 'error');
				header("Location: ".S_URL_ROOT."add");exit;
			}elseif($num<0){
				$_SESSION['infoadd'] = self::InfoMessage('Кол-во должно быть больше или равняться нулю!', 'error');
				header("Location: ".S_URL_ROOT."add");exit;
			}
			
			if(isset($_POST['SubmitServer']) && isset($_POST['ItemIid']))
			{
				if(empty($_POST['ItemIid'])	|| $_POST['ItemIid']<0)
				{
					$_SESSION['infoadd'] = self::InfoMessage('Неверно заполнено одно из полей', 'error');
					header("Location: ".S_URL_ROOT."add");exit;
				}
				$iid	= self::MRES($_POST['ItemIid']);
				$extra	= self::MRES($_POST['ItemExtra']);
					if($extra == '-666' || $iid == '-666')
					{
						$_SESSION['infoadd'] = self::InfoMessage('Поле "Особенности" не может быть -666. Нам очень жаль =(', 'error');
						header("Location: ".S_URL_ROOT."add");exit;
					}
				
			}elseif(isset($_POST['SubmitSite']))
			{
				$iid	= 'Группа пользователей на сайте';
				$extra	= '-666';
				
			}else{
				$_SESSION['infoadd'] = self::InfoMessage('Обнаружен перехват запроса!', 'error');
				header("Location: ".S_URL_ROOT."add");exit;
			}
				
				$result = S_QUERY("INSERT INTO `{$cfgShop['tbl_cont']}`
					(`enddate`,`cid`,`iid`,`title`,`img`,`description`,`price`,`discount`,`extra`,`num`,`realprice`)
				VALUES
					('$date','$cid','$iid','$title','$img','$desc','$price','$disc','$extra','$num','$real')");

				if($result){
					$_SESSION['infoadd'] = self::InfoMessage('Успешно добавлено!', 'success');
					header("Location: ".S_URL_ROOT."add");exit;
				}else{
					$_SESSION['infoadd'] = self::InfoMessage('Ошибка базы!'.mysql_error(), 'error');
					header("Location: ".S_URL_ROOT."add");exit;
				}
			
		}
		
		$CatList	= self::ItemAddCatList()	;
		$GroupList	= self::ItemAddGroupList()	;
		if(isset($_SESSION['infoadd'])){$info=$_SESSION['infoadd'];}else{$info='';}
		include(S_STYLE.$cfgShop['style']."ItemAdd.html");
		if(isset($_SESSION['infoadd'])){unset($_SESSION['infoadd']);}
		
		return ob_get_clean();
	} // Добавление предметов End
	
	public function ItemEdit($sid) // Редактирование предметов Start
	{
		global $cfgShop;
		ob_start();
		$sid	= intval($sid);
		$func	= 'edt';
		$btn	= 'Изменить';
		$idsid	= $sid.'/';
		
		$result = S_QUERY("SELECT * FROM {$cfgShop['tbl_cont']} WHERE id='$sid'");
		if($result && self::MNR($result)>0)
		{
			$array = self::MFA($result);
			
			$cid	= intval($array['cid'])				;
			$iid	= self::HSC($array['iid'])			;
			$title	= self::HSC($array['title'])		;
			$img	= self::HSC($array['img'])			;
			$desc	= self::HSC($array['description'])	;
			$price	= floatval($array['price'])			;
			$disc	= floatval($array['discount'])		;
			$extra	= self::HSC($array['extra'])		;
			$date	= self::HSC($array['enddate'])		;
			$num	= intval($array['num'])				;
			$real	= intval($array['realprice'])		;
			$real	= ($real==1) ? 'selected' : ''		;

			if($extra == '-666'){$extra = ''; $iid = '';}
			
			
		 if(isset($_POST['ItemTitle'],
		 		$_POST['ItemImg'],
		 		$_POST['ItemCid'],
		 		$_POST['ItemDesc'],
		 		$_POST['ItemPrice'],
		 		$_POST['ItemNum'],
		 		$_POST['ItemDisc']))
			{
				if(	empty($_POST['ItemTitle'])	||
					empty($_POST['ItemCid'])	||
					empty($_POST['ItemPrice'])	||
					empty($_POST['ItemNum'])	||
					$_POST['ItemNum']<0		||
					$_POST['ItemPrice']<0		)
				{
					$_SESSION['infoadd'] = self::InfoMessage('Неверно заполнено одно из полей', 'error');
					header("Location: ".S_URL_ROOT."add");exit;
				}
				
				$date	= intval($_POST['ItemDate'])		;
				$cid	= intval($_POST['ItemCid'])			;
				$title	= self::MRES($_POST['ItemTitle'])	;
				if(trim($_POST['ItemImg'])!=''){$img = self::MRES($_POST['ItemImg']);}else{$img = BASE_URL.'style/Default/img/shop/none.png';}
				$desc	= self::MRES($_POST['ItemDesc'])	;
				$price	= floatval($_POST['ItemPrice'])		;
				$disc	= floatval($_POST['ItemDisc'])		;
				$num	= intval($_POST['ItemNum'])			;
				$real	= intval($_POST['ItemReal'])		;
				$real	= ($real==1) ? 1 : 0				;
				
				if(!empty($disc) && ($disc>$price || $disc<0))
				{
					$_SESSION['infoadd'] = self::InfoMessage('Скидка не может быть больше цены или меньше нуля!', 'error');
					header("Location: ".S_URL_ROOT."add");exit;
				}elseif($num<0){
					$_SESSION['infoadd'] = self::InfoMessage('Кол-во товара не может быть меньше нуля!', 'error');
					header("Location: ".S_URL_ROOT."add");exit;
				}
				
				if(isset($_POST['SubmitServer']) && isset($_POST['ItemIid']))
				{
					if(empty($_POST['ItemIid'])	|| $_POST['ItemIid']<0)
					{
						$_SESSION['infoadd'] = self::InfoMessage('Неверно заполнено одно из полей', 'error');
						header("Location: ".S_URL_ROOT."add");exit;
					}
					$iid	= self::MRES($_POST['ItemIid']);
					$extra	= self::MRES($_POST['ItemExtra']);
					if($extra == '-666' || $iid == '-666')
					{
						$_SESSION['infoadd'] = self::InfoMessage('Поле "Особенности" не может быть -666. Нам очень жаль =(', 'error');
						header("Location: ".S_URL_ROOT."add");exit;
					}
					
				}elseif(isset($_POST['SubmitSite'])){
					$iid	= 'Группа пользователей на сайте';
					$extra	= '-666';
				}else{
					$_SESSION['infoadd'] = self::InfoMessage('Обнаружен перехват запроса!', 'error');
					header("Location: ".S_URL_ROOT."add");exit;
				}
				
					$result=S_QUERY("UPDATE {$cfgShop['tbl_cont']} SET
					`enddate`='$date',
					`cid`='$cid',
					`iid`='$iid',
					`title`='$title',
					`img`='$img',
					`description`='$desc',
					`price`='$price',
					`discount`='$disc',
					`extra`='$extra',
					`num`='$num',
					`realprice`='$real'
						WHERE id=$sid");
					
					if($result)
					{
						$_SESSION['infoedt'] = self::InfoMessage('Успешно обновлено!', 'success');
						header("Location: ".S_URL_ROOT.$sid."/edt");
						exit;
					}else{
						$_SESSION['infoedt'] = self::InfoMessage('Ошибка базы!', 'error');
						header("Location: ".S_URL_ROOT.$sid."/edt");
						exit;
					}
			}
			
			$CatList	= self::ItemAddCatList($cid)	;
			$GroupList	= self::ItemAddGroupList($cid)	;
			if(isset($_SESSION['infoedt'])){$info=$_SESSION['infoedt'];}else{$info='';}
			include(S_STYLE.$cfgShop['style']."ItemAdd.html");
			if(isset($_SESSION['infoedt'])){unset($_SESSION['infoedt']);}
		}else{
			self::go404();
		}
		
		return ob_get_clean();
	} // Редактирование предметов End
	
	public function ItemDelete($id) // Удаление предметов Start
	{
		global $cfgShop;
		ob_start();
		$id		= intval($id);
		$result	= S_QUERY("SELECT title FROM {$cfgShop['tbl_cont']} WHERE id='$id'");
		if($result && self::MNR($result)>0)
		{
			$array = self::MFA($result);
			$title = self::HSC($array['title']);
			
			if(	isset($_POST['ItemCheck'],$_POST['ItemSubmit'])	&&
				$_POST['ItemCheck']=='on'	&&
				!empty($_POST['ItemSubmit']) )
			{
				$result1 = S_QUERY("DELETE FROM {$cfgShop['tbl_cont']} WHERE id=$id");
				$result2 = S_QUERY("ALTER TABLE {$cfgShop['tbl_cont']} AUTO_INCREMENT=0");
			
				if($result1 && $result2){
					header("Location: ".S_URL_ROOT);exit;
				}else{
					$_SESSION['infodel'] = self::InfoMessage('Ошибка!', 'error');
					header("Location: ".S_URL_ROOT);exit;
				}
			}
			if(isset($_SESSION['infodel'])){$info=$_SESSION['infodel'];}else{$info='';}
			include(S_STYLE.$cfgShop['style']."ItemDelete.html");
			if(isset($_SESSION['infodel'])){unset($_SESSION['infodel']);}
			
		}else{
			self::go404();
		}
		
		return ob_get_clean();
	} // Удаление предметов End
	
	private static function ItemFullCat($cid,$server) // Возвращает название категории Start
	{
		global $bd_names,$cfgShop;
		if($server==1){
			$result = S_QUERY("SELECT * FROM `{$cfgShop['tbl_cat']}` WHERE id='$cid'");
			$t = 'title';
		}else{
			$result = S_QUERY("SELECT id,name FROM `{$bd_names['groups']}` WHERE id='$cid'");
			$t = 'name';
		}
		
		if($result && self::MNR($result)>0){$array = self::MFA($result); $title = self::HSC($array[$t]);}else{$title = 'Default';}
		return $title;
	} // Возвращает название категории End
	
	private static function ItemTypeCat($cid) // Возвращает тип категории Start
	{
		global $cfgShop;
		$result = S_QUERY("SELECT * FROM `{$cfgShop['tbl_cat']}` WHERE id='$cid'");
		if($result && self::MNR($result)>0){$array = self::MFA($result); $value = self::HSC($array['value']);}else{$value = 'item';}
		return $value;
	} // Возвращает тип категории End
	
	public function ItemFull($sid) // Получение полной новости Start
	{
		global $bd_money,$bd_names,$bd_users,$config,$cfgShop;
		ob_start();
		$sid = intval($sid);
		$result = S_QUERY("SELECT * FROM `{$cfgShop['tbl_cont']}` WHERE id='$sid'");
		
		if(!$result || self::MNR($result)<=0){self::go404();}

		$array	= self::MFA($result)				;
		$id		= intval($array['id'])				;
		$cid	= intval($array['cid'])				;
		$iid	= self::HSC($array['iid'])			;
		$num	= intval($array['num'])				;
		$title	= self::HSC($array['title'])		;
		$img	= self::HSC($array['img'])			;
		$desc	= self::HSC($array['description'])	;
		$price	= floatval($array['price'])			;
		$disc	= floatval($array['discount'])		;
		$days	= intval($array['enddate'])*24*60*60+time()	;
		$extra	= self::HSC($array['extra'])		;
		$Amax	= 64								;
		$Aval	= 32								;
		$typename= 'Категория'						;
		$deactive = false							;
		$curr	= intval($array['realprice'])		;
		$currency = ($curr==1) ? $cfgShop['currency2'] : $cfgShop['currency'];

		$ITC	= self::ItemTypeCat($cid);

		if($extra=='-666')
		{
			$ctitle=self::ItemFullCat($cid,0); $Amax=1; $Aval=1; $typename='Группа'; $deactive = true;
		}elseif($ITC == 'permgroup'){
			$ctitle=self::ItemFullCat($cid,0); $Amax=1; $Aval=1; $typename='Привилегии'; $deactive = true;
		}elseif($ITC == 'money'){
			$ctitle=self::ItemFullCat($cid,0); $typename='Деньги'; $deactive = false;
		}elseif($ITC=='rgown' || $ITC=='rgmem'){
			$ctitle=self::ItemFullCat($cid,0); $Amax=1; $Aval=1; $typename='Регион'; $deactive = true;
		}else{
			$ctitle	= self::ItemFullCat($cid,1);
		}
			
		if(!empty($disc)){$pricez = $price-$disc;}else{$pricez = $price;}
		$isMoney = ($curr==1) ? S_P_MONEYR : S_P_MONEY;
		if($isMoney<$pricez)
		{
			$disable='disabled';$Isubmit='Недостаточно средств';
		}elseif(S_P_GROUPID==$cid && $extra=='-666'){
			$disable='disabled';$Isubmit='Игрок уже состоит в этой группе';
		}elseif($num<=0){
			$disable='disabled';$Isubmit='Товар закончился';
		}elseif(S_P_GROUPID!=$cid && $extra=='-666'){
			$disable='';$Isubmit='Купленная группа будет сразу же активирована';
		}else{
			$disable='';$Isubmit='Купленный предмет появится в корзине';
		}
			
		if(isset($_SESSION['info'])){$info=$_SESSION['info'];}else{$info='';}
		include_once(S_STYLE.$cfgShop['style']."ItemFull.html");
		if(isset($_SESSION['info'])){unset($_SESSION['info']);}
			
		if(isset($_POST['ItemNum']) || isset($_POST['ItemNum2']))
		{

			if(isset($_POST['bignumcheck']) && mb_strtolower($_POST['bignumcheck'])==='on' && $cfgShop['use_over64']==1)
			{
				$amount2 = intval($_POST['ItemNum2']);
				if($amount2 > 0 && $amount2 < 100000000)
				{
					$amount = $amount2;
				}else{
					$_SESSION['info'] = self::InfoMessage('Ошибка! Кол-во предметов должно быть целым числом и входить в промежуток от 1 до 99999999', 'error');
					header("Location: ".S_URL_ROOT.$sid);
					exit;
				}
			}else{
				$amount = intval($_POST['ItemNum']);
			}

			if($amount<=0 || $amount>=100000000)
			{
				$_SESSION['info'] = self::InfoMessage('Ошибка кол-ва предметов!', 'error');
				header("Location: ".S_URL_ROOT.$sid);
				exit;
			}elseif($amount>$num){
				$_SESSION['info'] = self::InfoMessage('Недостаточно товара!', 'error');
				header("Location: ".S_URL_ROOT.$sid);
				exit;
			}
				
			$priced = $amount*($price-$disc);
			
			
			if($isMoney>=$priced)
			{
				if($extra=='-666')
				{
					if(S_P_GROUPID==$cid)
					{
						$_SESSION['info'] = self::InfoMessage('Вы уже состоите в этой группе!', 'error');
						header("Location: ".S_URL_ROOT.$sid);
						exit;
					}
						
					if(isset($_POST['giftcheck']) && mb_strtolower($_POST['giftcheck'])==='on' && $cfgShop['use_gift']==1)
					{
						if(isset($_POST['gift']) && !empty($_POST['gift']) && preg_match('/^[a-z0-9_-]{3,30}$/i', $_POST['gift']))
						{
							$thisGift = self::MRES($_POST['gift']);
							$result1Check = S_QUERY("SELECT COUNT(*)
												FROM `{$bd_names['users']}`
												WHERE `{$bd_users['login']}`='$thisGift'
													AND `{$bd_users['group']}`!='".$cid."'");
							$resCheckAr = self::MFA($result1Check);
							if(!$result1Check || $resCheckAr[0]<=0)
							{
								$_SESSION['info'] = self::InfoMessage('Пользователь не существует или уже имеет данную группу пользователей', 'error');
								header("Location: ".S_URL_ROOT.$sid);
								exit;
							}
							$result1 = S_QUERY("UPDATE `{$bd_names['users']}` SET `{$bd_users['group']}`='$cid' WHERE `{$bd_users['login']}`='$thisGift'");
						}else{
							$_SESSION['info'] = self::InfoMessage('Неверно указан ник игрока', 'error');
							header("Location: ".S_URL_ROOT.$sid);
							exit;
						}
					}else{
						$result1 = S_QUERY("UPDATE `{$bd_names['users']}` SET `{$bd_users['group']}`='$cid' WHERE `{$bd_users['login']}`='".S_P_NAME."'");
					}
					$isBalanse = ($curr==1) ? "realmoney" : $bd_money['money'];
					$result2 = S_QUERY("UPDATE `{$bd_names['iconomy']}` SET `$isBalanse`=`$isBalanse`-$priced WHERE username='".S_P_NAME."'");
					
					$result3 = S_QUERY("INSERT INTO `{$cfgShop['tbl_hist']}`
					(`username`,`title`,`sid`,`iid`,`price`,`amount`) VALUES
					('".S_P_NAME."','".$title."','".$id."','".$iid."','".$priced."','".$amount."')");

					$result4 = S_QUERY("INSERT INTO `{$cfgSho['tbl_cart']}`
					(`sid`,`username`,`iid`,`title`,`type`,`extra`,`amount`) VALUES
					('".$id."','".S_P_NAME."','".$iid."','".$title."','".$type."',".$extra.",'".$amount."')");
					$updNum = S_QUERY("UPDATE `{$cfgShop['tbl_cont']}` SET `num`=`num`-$amount WHERE id='$sid'");
					
					if($result1 && $result2 && $result3 && $result4 && $updNum)
					{
						$_SESSION['info'] = self::InfoMessage('Вы успешно купили предмет(ы). Получено: '.$title.' | Кол-во: '.$amount.'шт.', 'success');
						header("Location: ".S_URL_ROOT.$sid);
						exit;
					}else{
						$_SESSION['info'] = self::InfoMessage('Ошибка покупки группы!', 'error');
						header("Location: ".S_URL_ROOT.$sid);
						exit;
					}
				}
					
				$type = self::ItemTypeCat($cid);
				if($extra===''){$extra='NULL';}else{$extra="'".$extra."'";} if($type===''){$type='item';}
				if(isset($_POST['giftcheck']) && $_POST['giftcheck'])
				{
					if(isset($_POST['gift']) && !empty($_POST['gift']) && preg_match('/^[a-z0-9_-]{3,30}$/i', $_POST['gift']))
					{
						$thisGift = self::MRES($_POST['gift']);
						$result2 = S_QUERY("INSERT INTO `{$cfgShop['tbl_cart']}`
						(`sid`,`username`,`iid`,`title`,`type`,`extra`,`amount`) VALUES
						('".$id."','$thisGift','".$iid."','".$title."','".$type."',".$extra.",'".$amount."')");
							
						$result2Check = S_QUERY("SELECT COUNT(*) FROM `{$bd_names['users']}` WHERE `{$bd_users['login']}`='$thisGift'");
						$resCheckAr2 = self::MFA($result2Check);
						if(!$result2Check || $resCheckAr2[0]<=0)
						{
							$_SESSION['info'] = self::InfoMessage('Пользователь не существует', 'error');
							header("Location: ".S_URL_ROOT.$sid);
							exit;
						}
							
						$result3 = S_QUERY("INSERT INTO `{$cfgShop['tbl_hist']}`
						(`username`,`title`,`sid`,`iid`,`price`,`amount`) VALUES
						('".S_P_NAME."','".$title."','".$id."','".$iid."','".$priced."','".$amount."')");

						if(self::ItemTypeCat($cid) == 'permgroup' && $cfgShop['use_permlist']==1)
						{
							$result4 = S_QUERY("INSERT INTO `{$cfgShop['tbl_temp']}`
								(`username`,`buytime`,`endtime`,`tempgroup`) VALUES
								('$thisGift','".time()."','".$days."','".$iid."')");
						}else{
							$result4 = true;
						}
					}else{
						$_SESSION['info'] = self::InfoMessage('Неверно указан ник игрока', 'error');
						header("Location: ".S_URL_ROOT.$sid);
						exit;
					}
				}else{
					$result2 = S_QUERY("INSERT INTO `{$cfgShop['tbl_cart']}`
					(`sid`,`username`,`iid`,`title`,`type`,`extra`,`amount`) VALUES
					('".$id."','".S_P_NAME."','".$iid."','".$title."','".$type."',".$extra.",'".$amount."')");
					
					$result3 = S_QUERY("INSERT INTO `{$cfgShop['tbl_hist']}`
					(`username`,`title`,`sid`,`iid`,`price`,`amount`) VALUES
					('".S_P_NAME."','".$title."','".$id."','".$iid."','".$priced."','".$amount."')");

					if(self::ItemTypeCat($cid) == 'permgroup' && $cfgShop['use_permlist']==1)
					{
						$result4 = S_QUERY("INSERT INTO `{$cfgShop['tbl_temp']}`
							(`username`,`buytime`,`endtime`,`tempgroup`) VALUES
							('".S_P_NAME."','".time()."','".$days."','".$iid."')");
					}else{
						$result4 = true;
					}
				}
				$isBalanse = ($curr==1) ? "realmoney" : $bd_money['money'];
				$result1 = S_QUERY("UPDATE `{$bd_names['iconomy']}` SET `$isBalanse`=`$isBalanse`-$priced WHERE username='".S_P_NAME."'");
				$updNum = S_QUERY("UPDATE `{$cfgShop['tbl_cont']}` SET `num`=`num`-$amount WHERE id='$sid'");

				if($result1 && $result2 && $result3 && $result4 && $updNum)
				{
					$_SESSION['info'] = self::InfoMessage('Вы успешно купили предмет(ы). Получено: '.$title.' | Кол-во: '.$amount.'шт.', 'success');
					header("Location: ".S_URL_ROOT.$sid);
					exit;
				}else{
					$_SESSION['info'] = self::InfoMessage('Ошибка покупки предмета!', 'error');
					header("Location: ".S_URL_ROOT.$sid);
					exit;
				}
			}else{
				$_SESSION['info'] = self::InfoMessage('У вас недостаточно средств!', 'error');
				header("Location: ".S_URL_ROOT.$sid);
				exit;
			}
		}

		return ob_get_clean();
	} // Получение полной новости Start
	
	private static function ItemCartID() // Получение одного товара Start
	{
		global $cfgShop;
		ob_start();

		if (isset($_GET['pid']) && $_GET['pid']>0){$pageID=intval($_GET['pid']);}else{$pageID=1;}
		$start = $pageID * $cfgShop['r_op_cart'] - $cfgShop['r_op_cart'];
		$result = S_QUERY("SELECT * FROM {$cfgShop['tbl_cart']} WHERE username='".S_P_NAME."' LIMIT $start, {$cfgShop['r_op_cart']}");
		
		if($result && self::MNR($result)>0)
		{
			while($array = self::MFA($result))
			{
				$id		= intval($array['id'])			;
				$sid	= intval($array['sid'])			;
				$iid	= self::HSC($array['iid'])		;
				$title	= self::HSC($array['title'])	;
				$amount	= floatval($array['amount'])	;
				include(S_STYLE.$cfgShop['style']."ItemCartID.html");
			}
		}else{
				include_once(S_STYLE.$cfgShop['style']."ItemCartNone.html");
		}
		
		return ob_get_clean();
	} // Получение одного товара End
	
	public function ItemCart() // Получение списка товаров Start
	{
		global $cfgShop;
		ob_start();
		$ItemCartIDs = self::ItemCartID();
		include(S_STYLE.$cfgShop['style']."ItemCart.html");
		return ob_get_clean();
	} // Получение списка товаров End
	
	private static function ItemHistoryID() // Получение одного товара истории Start
	{
		global $config,$cfgShop;
		ob_start();
		if (isset($_GET['pid']) && $_GET['pid']>0){$pageID=intval($_GET['pid']);}else{$pageID=1;}
		$start = $pageID * $cfgShop['r_op_hstry'] - $cfgShop['r_op_hstry'];
		$result = S_QUERY("SELECT * FROM {$cfgShop['tbl_hist']} WHERE username='".S_P_NAME."' LIMIT $start, {$cfgShop['r_op_hstry']}");

		if($result && self::MNR($result)>0)
		{
			while($array = self::MFA($result))
			{
				$id		= intval($array['id'])			;
				$sid	= intval($array['sid'])			;
				$iid	= self::HSC($array['iid'])		;
				$title	= self::HSC($array['title'])	;
				$price	= floatval($array['price'])		;
				$amount	= floatval($array['amount'])	;
				include(S_STYLE.$cfgShop['style']."ItemHistoryID.html");
			}
		}else{
				include(S_STYLE.$cfgShop['style']."ItemHistoryNone.html");
		}
		
		return ob_get_clean();
	} // Получение одного товара истории Start
	
	public function ItemHistory() // Получение списка товаров истории Start
	{
		global $cfgShop;
		ob_start();
		$ItemHistoryIDs = self::ItemHistoryID();
		include(S_STYLE.$cfgShop['style']."ItemHistory.html");
		return ob_get_clean();
	} // Получение списка товаров истории Start
	
	private static function CatID()
	{
		global $cfgShop;
		ob_start();
		$result = S_QUERY("SELECT * FROM {$cfgShop['tbl_cat']} WHERE id!=1");
		
		if($result && self::MNR($result)>0)
		{
			while($array = self::MFA($result))
			{
				$id = intval($array['id']);
				$title = self::HSC($array['title']);
				include(S_STYLE.$cfgShop['style']."CatID.html");
			}
		}else{
			echo "<center>Категории отсутствуют</center>";
		}
		
		return ob_get_clean();
	}
	
	public function CatArray()
	{
		global $cfgShop;
		ob_start();
		$CatArray = self::CatID();
		include(S_STYLE.$cfgShop['style']."CatArray.html");
		return ob_get_clean();
	}
	
	public function CatAdd()
	{
		global $cfgShop;
		ob_start();
		$btn = 'Добавить';
		$idsid = 'c.';
		$func = 'add';
		if(isset($_POST['CatTitle']) && isset($_POST['CatType']))
		{
			if(!empty($_POST['CatTitle']) && !empty($_POST['CatType']))
			{
				$value = self::MRES($_POST['CatType']);
				$title = self::MRES($_POST['CatTitle']);
				$result = S_QUERY("INSERT INTO `{$cfgShop['tbl_cat']}` (`value`,`title`) VALUES ('$value','$title')");
				if($result)
				{
					$_SESSION['infocatadd'] = self::InfoMessage('Категория успешно добавлена.', 'success');
					header("Location: ".S_URL_ROOT."c.add");exit;
				}else{
					$_SESSION['infocatadd'] = self::InfoMessage('Ошибка! Ошибка базы данных.', 'error');
					header("Location: ".S_URL_ROOT."c.add");exit;
				}
			}else{
				$_SESSION['infocatadd'] = self::InfoMessage('Ошибка! Название не заполнено.', 'error');
				header("Location: ".S_URL_ROOT."c.add");exit;
			}
		}
		$title='';$value='';
		if(isset($_SESSION['infocatadd'])){$info=$_SESSION['infocatadd'];}else{$info='';}
		include(S_STYLE.$cfgShop['style']."CatAdd.html");
		if(isset($_SESSION['infocatadd'])){unset($_SESSION['infocatadd']);}
		
		return ob_get_clean();
	}
	
	public function CatEdit($cid)
	{
		global $cfgShop;
		ob_start();
		$cid	= intval($cid)	;
		$btn	= 'Изменить'	;
		$idsid	= 'c.'.$cid.'/'	;
		$func	= 'edt'			;
		
		$result = S_QUERY("SELECT * FROM `{$cfgShop['tbl_cat']}` WHERE id='$cid'");
		if($result && self::MNR($result)>0)
		{
			$array	= self::MFA($result)			;
			$id		= intval($array['id'])			;
			$value	= self::HSC($array['value'])	;
			$title	= self::HSC($array['title'])	;
		}
		
		if(isset($_POST['CatTitle']) && isset($_POST['CatTitle']))
		{
			if(!empty($_POST['CatTitle']) && !empty($_POST['CatTitle']))
			{
				$value = self::MRES($_POST['CatType']);
				$title = self::MRES($_POST['CatTitle']);
				$result = S_QUERY("UPDATE `{$cfgShop['tbl_cat']}` SET `value`='$value', `title`='$title' WHERE id='$id'");
				if($result)
				{
					$_SESSION['infocatedt'] = self::InfoMessage('Категория успешно изменена.', 'success');
					header("Location: ".S_URL_ROOT."c.".$id."/edt");exit;
				}else{
					$_SESSION['infocatedt'] = self::InfoMessage('Ошибка! Ошибка базы данных.', 'error');
					header("Location: ".S_URL_ROOT."c.".$id."/edt");exit;
				}
			}else{
				$_SESSION['infocatedt'] = self::InfoMessage('Ошибка! Название не заполнено.', 'error');
				header("Location: ".S_URL_ROOT."c.".$id."/edt");exit;
			}
		}
		
		if(isset($_SESSION['infocatedt'])){$info=$_SESSION['infocatedt'];}else{$info='';}
		include(S_STYLE.$cfgShop['style']."CatAdd.html");
		if(isset($_SESSION['infocatedt'])){unset($_SESSION['infocatedt']);}
		
		return ob_get_clean();
	}
	
	public function CatDelete($cid)
	{
		global $cfgShop;
		ob_start();
		$cid	= intval($cid)	;
		$btn	= 'Удалить'		;
		$idsid	= 'c.'.$cid.'/'	;
		$func	= 'del'			;
		
		$result = S_QUERY("SELECT * FROM `{$cfgShop['tbl_cat']}` WHERE id='$cid'");
		if($result && self::MNR($result)>0)
		{
			$array = self::MFA($result);
			$id = intval($array['id']);
			$title = self::HSC($array['title']);
		
			if(isset($_POST['CatSubmit']) && !empty($_POST['CatSubmit']))
			{
				if(isset($_POST['CatCheck']) && $_POST['CatCheck']=='on')
				{
					
					$result1 = S_QUERY("DELETE FROM {$cfgShop['tbl_cat']} WHERE id=$id");
					$result2 = S_QUERY("DELETE FROM {$cfgShop['tbl_cont']} WHERE cid=$id");
					$result3 = S_QUERY("ALTER TABLE {$cfgShop['tbl_cat']} AUTO_INCREMENT=0");
					if($result1 && $result2 && $result3)
					{
						header("Location: ".S_URL_ROOT."c.all");exit;
					}else{
						$_SESSION['infocatdel'] = self::InfoMessage('Ошибка! Ошибка базы данных.', 'error');
						header("Location: ".S_URL_ROOT."c.".$id."/del");exit;
					}
				}else{
					$_SESSION['infocatdel'] = self::InfoMessage('Ошибка! Вы не подтвердили удаление.', 'error');
					header("Location: ".S_URL_ROOT."c.".$id."/del");exit;
				}
			}
		
		if(isset($_SESSION['infocatdel'])){$info=$_SESSION['infocatdel'];}else{$info='';}
		include(S_STYLE.$cfgShop['style']."CatDelete.html");
		if(isset($_SESSION['infocatdel'])){unset($_SESSION['infocatdel']);}
		
		}else{
			self::go404();
		}
		
		return ob_get_clean();
	}
	
	private static function SaveOptions()
	{
		global $config,$bd_names,$bd_money,$bd_users,$site_ways;

		$txt  = '<?php'.PHP_EOL;
		$txt .= '$config = '.var_export($config, true).';'.PHP_EOL;
		$txt .= '$bd_names = '.var_export($bd_names, true).';'.PHP_EOL;
		$txt .= '$bd_users = '.var_export($bd_users, true).';'.PHP_EOL;
		$txt .= '$bd_money = '.var_export($bd_money, true).';'.PHP_EOL;
		$txt .= '$site_ways = '.var_export($site_ways, true).';'.PHP_EOL;
		$txt .= '?>';

		$result = file_put_contents("config.php", $txt);

		if (is_bool($result) and $result == false){return false;}

		return true;
	}
	
	private static function SaveShop()
	{
		global $cfgShop;

		$txt  = '<?php'.PHP_EOL;
		$txt .= '$cfgShop = '.var_export($cfgShop, true).';'.PHP_EOL;
		$txt .= '?>';

		$result = file_put_contents(MCR_ROOT."shop.cfg.php", $txt);

		if (is_bool($result) and $result == false){return false;}

		return true;
	}
	
	public function Settings()
	{
		global $config,$bd_names,$bd_money,$bd_users,$cfgShop,$site_ways;
		ob_start();
		
		if(!$bd_names['iconomy']){$bd_names['iconomy'] = 'iconomy';}
		if(!isset($bd_money['bank'])){$bd_money['bank'] = 'bank';}
		if(!isset($cfgShop['style'])){$cfgShop['style'] = 'shop/';}
		
		if(	isset($_POST['SMoney'])		&&
			isset($_POST['SBank'])		)
		{
			$SMoney = self::MRES(self::ST($_POST['SMoney']));
			$SBank = self::MRES(self::ST($_POST['SBank']));
			
			if(	$SMoney==''	||
				$SBank==''	){
				
					$_SESSION['infoset'] = self::InfoMessage('Ошибка! Вы не заполнили одно из полей"', 'error');
					header("Location: ".S_URL_ROOT."settings");exit;
			}else{
					$bd_names['iconomy']	= $SMoney;
					$bd_money['bank']		= $SBank;
					if($cfgShop['install']){
						header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
						header("Cache-Control: no-store, no-cache, must-revalidate");
						header("Cache-Control: post-check=0, pre-check=0",false);
						header("Pragma: no-cache");
						if(self::Install()){$cfgShop['install'] = false;
						}else{$_SESSION['infoset'] = self::InfoMessage('Ошибка доступа.'.self::Install().mysql_error(), 'error');
						header("Location: ".S_URL_ROOT."settings");exit;}
					}
				if(self::SaveOptions() && self::SaveShop())
				{
					$_SESSION['infoset'] = self::InfoMessage('Настройки успешно обновлены.', 'success');
					header("Location: ".S_URL_ROOT."settings");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					header("Cache-Control: no-store, no-cache, must-revalidate");
					header("Cache-Control: post-check=0, pre-check=0",false);
					header("Pragma: no-cache");
					exit;
				}else{
					$_SESSION['infoset'] = self::InfoMessage('Неизвестная ошибка.', 'error');
					header("Location: ".S_URL_ROOT."settings");exit;
				}
			}

		}
		
		if(isset($_SESSION['infoset'])){$info=$_SESSION['infoset'];}else{$info='';}
		include(S_STYLE.$cfgShop['style']."Settings.html");
		if(isset($_SESSION['infoset'])){unset($_SESSION['infoset']);}
		
		return ob_get_clean();
	}

	public function Extra()
	{
		global $cfgShop;
		ob_start();

		if(isset($_POST['submit']))
		{
			$cfgShop['s_or_items']	= intval($_POST['s_or_items']);
			$cfgShop['s_by_items']	= self::HSC($_POST['s_by_items']);
			$cfgShop['currency']	= self::HSC($_POST['currency']);
			$cfgShop['currency2']	= self::HSC($_POST['currency2']);
			$cfgShop['r_op_items']	= intval($_POST['r_op_items']);
			$cfgShop['r_op_hstry']	= intval($_POST['r_op_hstry']);
			$cfgShop['r_op_cart']	= intval($_POST['r_op_cart']);
			$cfgShop['r_op_prmlst']	= intval($_POST['r_op_prmlst']);
			$cfgShop['shop_id']		= self::HSC($_POST['shop_id']);
			$cfgShop['secret_key']	= self::HSC($_POST['secret_key']);
			$cfgShop['cur_multip']	= floatval($_POST['cur_multip']);
			$cfgShop['use_intkas']	= intval($_POST['use_intkas']);
			$cfgShop['use_vaucher']	= intval($_POST['use_vaucher']);
			$cfgShop['shop_close']	= intval($_POST['shop_close']);
			$cfgShop['use_permlist']= intval($_POST['use_permlist']);
			$cfgShop['use_gift']	= intval($_POST['use_gift']);
			$cfgShop['use_over64']	= intval($_POST['use_over64']);

			if(	$cfgShop['s_or_items']<0	|| $cfgShop['s_or_items']>1		||
				$cfgShop['use_intkas']<0	|| $cfgShop['use_intkas']>1		||
				$cfgShop['use_vaucher']<0	|| $cfgShop['use_vaucher']>1	||
				$cfgShop['shop_close']<0	|| $cfgShop['shop_close']>1		||
				$cfgShop['use_permlist']<0	|| $cfgShop['use_permlist']>1	||
				$cfgShop['use_gift']<0		|| $cfgShop['use_gift']>1		||
				$cfgShop['use_over64']<0	|| $cfgShop['use_over64']>1		||
				$cfgShop['r_op_items']<1	||
				$cfgShop['r_op_hstry']<1	||
				$cfgShop['r_op_cart'] <1	||
				$cfgShop['r_op_prmlst']<1	||
				$cfgShop['cur_multip']<0	){

				$_SESSION['infoextra'] = self::InfoMessage('Одно из полей заполнено неверно!', 'error');
				header("Location: ".S_URL_ROOT."extra");exit;
			}elseif(!preg_match('/[a-z]{1,20}/', $cfgShop['s_by_items']) ||
					!preg_match('/[\w\.]{1,20}/i', $cfgShop['currency']) ||
					!preg_match('/[\w\.]{1,20}/i', $cfgShop['currency2']) ){

					$_SESSION['infoextra'] = self::InfoMessage('Ошибка сортировки предметов', 'error');
					header("Location: ".S_URL_ROOT."extra");exit;
			}elseif(!preg_match('/[\w-]+/', $cfgShop['shop_id'])){

					$_SESSION['infoextra'] = self::InfoMessage('Ошибка идентификатора магазина', 'error');
					header("Location: ".S_URL_ROOT."extra");exit;
			}elseif(!preg_match('/[\w]+/i', $cfgShop['secret_key'])){

					$_SESSION['infoextra'] = self::InfoMessage('Ошибка секретного ключа', 'error');
					header("Location: ".S_URL_ROOT."extra");exit;
			}else{
				if(self::SaveShop())
				{
					$_SESSION['infoextra'] = self::InfoMessage('Настройки успешно обновлены.', 'success');
					header("Location: ".S_URL_ROOT."extra");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					header("Cache-Control: no-store, no-cache, must-revalidate");
					header("Cache-Control: post-check=0, pre-check=0",false);
					header("Pragma: no-cache");
					exit;
				}else{
					$_SESSION['infoextra'] = self::InfoMessage('Неизвестная ошибка.', 'error');
					header("Location: ".S_URL_ROOT."extra");exit;
				}
			}
		}

		if(isset($_SESSION['infoextra'])){$info=$_SESSION['infoextra'];}else{$info='';}
		include(S_STYLE.$cfgShop['style']."Extra.html");
		if(isset($_SESSION['infoextra'])){unset($_SESSION['infoextra']);}

		return ob_get_clean();
	}
	
	private static function Keygen()
	{
		$string=substr(preg_replace("/[^0-9A-Z]/","",crypt("")),5,5);
		$string1=substr(preg_replace("/[^0-9A-Z]/","",crypt("")),5,5);
		$string2=substr(preg_replace("/[^0-9A-Z]/","",crypt("")),5,5);
		$string3=substr(preg_replace("/[^0-9A-Z]/","",crypt("")),5,5);
		
		return $string.'-'.$string1.'-'.$string2.'-'.$string3;
	}
	
	private static function KeyChecker($key)
	{
		if(!preg_match("/([A-Z0-9]{5}-){3}[A-Z0-9]{5}/", $key)){return false;}
		return true;
	}
	
	private static function KeyID()
	{
		global $cfgShop;
		ob_start();
		$result = S_QUERY("SELECT * FROM {$cfgShop['tbl_keys']}");
		if(self::MNR($result)>0){
			while($array = self::MFA($result))
			{
				$id		= intval($array['id']);
				$key	= self::HSC($array['key']);
				$sum	= floatval($array['sum']);
				$active	= intval($array['active']);
				$user	= self::HSC($array['username']);
				$link	= self::HSC($array['link']);
				
				if($active==1){$valid="success";$active="Не активирован";}else{$valid="error";$active="Активирован";} // Проверка на валидность
				if($user==''){$user="Нет";} // Проверка на существование активировшего пользователя
				include(S_STYLE.$cfgShop['style']."KeyID.html");
			}
		}else{
			include_once(S_STYLE.$cfgShop['style']."KeyNone.html");
		}
		return ob_get_clean();
	}
	
	public function KeyArray()
	{
		global $cfgShop;
		ob_start();
		$KeyArray = self::KeyID();
		include(S_STYLE.$cfgShop['style']."KeyArray.html");
		return ob_get_clean();
	}
	
	public function KeyAdd()
	{
		global $cfgShop;
		ob_start();
		$btn	= 'Добавить';
		$idsid	= 'k.';
		$func	= 'add';
		
		$key=self::Keygen(); $sum = $real = $link = ''; $valid=1; // Предопределение переменных (remove Undefined)
		
		if(	isset(	$_POST['Key']	,
					$_POST['Sum']	,
					$_POST['Link']	,
					$_POST['Valid']	,
					$_POST['real']	))
		{
			if(	empty($_POST['Key'])	||
				empty($_POST['Sum'])	||
				empty($_POST['Link'])	||
				empty($_POST['Valid'])	)
			{
				$_SESSION['infokeyadd'] = self::InfoMessage('Вы не заполнили одно или несколько полей', 'error');
				header("Location: ".S_URL_ROOT."k.add");exit;
			}else{
				$key	= self::MRES($_POST['Key']);
				$sum	= floatval($_POST['Sum']);
				$link	= self::MRES($_POST['Link']);
				$valid	= intval($_POST['Valid']);
				$real	= intval($_POST['real']);
				$real	= ($real==1) ? 1 : 0;
				if(!self::KeyChecker($key)) // Проверка ключа на валидность
				{
					$_SESSION['infokeyadd'] = self::InfoMessage('Неверный ключ!', 'error');
					header("Location: ".S_URL_ROOT."k.add");exit;
				}
				$result = S_QUERY("INSERT INTO `{$cfgShop['tbl_keys']}`
				(`key`,`sum`,`active`,`link`,`realmoney`) VALUES
				('$key','$sum','$valid','$link','$real')");
			
				if($result){
					$_SESSION['infokeyadd'] = self::InfoMessage('Успешно добавлено!', 'success');
					header("Location: ".S_URL_ROOT."k.add");exit;
				}else{
					$_SESSION['infokeyadd'] = self::InfoMessage('Ошибка базы!', 'error');
					header("Location: ".S_URL_ROOT."k.add");exit;
				}
			}
		}
		if(isset($_SESSION['infokeyadd'])){$info=$_SESSION['infokeyadd'];}else{$info='';}
		include(S_STYLE.$cfgShop['style']."KeyAdd.html");
		if(isset($_SESSION['infokeyadd'])){unset($_SESSION['infokeyadd']);}
		
		return ob_get_clean();
	}
	
	public function KeyEdit($kid)
	{
		global $cfgShop;
		ob_start();
		$kid = intval($kid);
		$btn = 'Изменить';
		$idsid = 'k.'.$kid.'/';
		$func = 'edt';
		
		if(	isset(	$_POST['Key']	,
					$_POST['Sum']	,
					$_POST['Link']	,
					$_POST['Valid']	,
					$_POST['real']	))
		{
			if(	!empty($_POST['Key'])	&&
				!empty($_POST['Sum'])	&&
				!empty($_POST['Link'])	&&
				trim($_POST['Valid'])!='')
			{
				$key	= self::MRES($_POST['Key']);
				$sum	= floatval($_POST['Sum']);
				$link	= self::MRES($_POST['Link']);
				$valid	= intval($_POST['Valid']);
				$real	= intval($_POST['real']);
				$real	= ($real==1) ? 1 : 0;
				
				if(!self::KeyChecker($key)) // Проверка ключа на валидность
				{
					$_SESSION['infokeyedt'] = self::InfoMessage('Неверный ключ!', 'error');
					header("Location: ".S_URL_ROOT."k.".$kid."/edt");exit;
				}
				
				$result = S_QUERY("UPDATE `{$cfgShop['tbl_keys']}`
									SET `key`='$key', `sum`='$sum', `link`='$link', `active`='$valid', `realmoney`='$real'
									WHERE id='$kid'");
				if($result)
				{
					$_SESSION['infokeyedt'] = self::InfoMessage('Ваучер успешно изменен.', 'success');
					header("Location: ".S_URL_ROOT."k.".$kid."/edt");exit;
				}else{
					$_SESSION['infokeyedt'] = self::InfoMessage('Ошибка! Ошибка базы данных.', 'error');
					header("Location: ".S_URL_ROOT."k.".$kid."/edt");exit;
				}
			}else{
				$_SESSION['infokeyedt'] = self::InfoMessage('Ошибка! Одно из полей не заполнено.', 'error');
				header("Location: ".S_URL_ROOT."k.".$kid."/edt");exit;
			}
		}
		
		$result = S_QUERY("SELECT * FROM `{$cfgShop['tbl_keys']}` WHERE id='$kid'");
		
		if($result && self::MNR($result)>0)
		{
			$array = self::MFA($result);
			
			$id		= intval($array['id']);
			$key	= self::HSC($array['key']);
			$sum	= floatval($array['sum']);
			$link	= self::HSC($array['link']);
			$valid	= intval($array['active']);
			$real	= intval($array['realmoney']);
			$real	= ($real==1) ? 'selected' : '';
		}else{
			self::go404();
		}
		
		if(isset($_SESSION['infokeyedt'])){$info=$_SESSION['infokeyedt'];}else{$info='';}
		include(S_STYLE.$cfgShop['style']."KeyAdd.html");
		if(isset($_SESSION['infokeyedt'])){unset($_SESSION['infokeyedt']);}
		
		return ob_get_clean();
	}
	
	public function KeyDelete($kid)
	{
		global $cfgShop;
		ob_start();
		$kid	= intval($kid);
		$btn	= 'Удалить';
		$idsid	= 'k.'.$kid.'/';
		$func	= 'del';
		
		$result = S_QUERY("SELECT * FROM `{$cfgShop['tbl_keys']}` WHERE id='$kid'");
		if($result && self::MNR($result)>0)
		{
			$array = self::MFA($result);
			$key = self::HSC($array['key']);
		
			if(isset($_POST['KeySubmit']) && !empty($_POST['KeySubmit']))
			{
				if(isset($_POST['KeyCheck']) && $_POST['KeyCheck']=='on')
				{
					$result1 = S_QUERY("DELETE FROM {$cfgShop['tbl_keys']} WHERE id=$kid");
					$result2 = S_QUERY("ALTER TABLE {$cfgShop['tbl_keys']} AUTO_INCREMENT=0");
					if($result1 && $result2)
					{
						header("Location: ".S_URL_ROOT."k.all");exit;
					}else{
						$_SESSION['infokeydel'] = self::InfoMessage('Ошибка! Ошибка базы данных.', 'error');
						header("Location: ".S_URL_ROOT."k.".$kid."/del");exit;
					}
				}else{
					$_SESSION['infokeydel'] = self::InfoMessage('Ошибка! Вы не подтвердили удаление.', 'error');
					header("Location: ".S_URL_ROOT."k.".$kid."/del");exit;
				}
			}
		
			if(isset($_SESSION['infokeydel'])){$info=$_SESSION['infokeydel'];}else{$info='';}
			include(S_STYLE.$cfgShop['style']."KeyDelete.html");
			if(isset($_SESSION['infokeydel'])){unset($_SESSION['infokeydel']);}
		
		}else{
			self::go404();
		}
		
		return ob_get_clean();
	}
	
	private static function KeySums()
	{
		global $config,$cfgShop;
		ob_start();
		$result = S_QUERY("SELECT `sum`,`link` FROM `{$cfgShop['tbl_keys']}` WHERE `active`='1' GROUP BY sum");
		if(self::MNR($result)>0)
		{
			echo '<select onchange="if (this.value) window.location.href=this.value">';
			echo '<option selected value="">Выберите сумму</option>';
			while($Buy = self::MFA($result))
			{
				$sum	= floatval($Buy['sum']);
				$link	= self::HSC($Buy['link']);
				echo '<option value="'.$link.'">'.$sum.' '.$cfgShop['currency2'].'</option>';
			}
			echo '</select>';
		}else{
			echo "Нет доступных покупок";
		}

		return ob_get_clean();
	}
	
	public function KeyBuy()
	{
		global $bd_money,$bd_names,$cfgShop;
		ob_start();
		
		if(isset($_POST['Key'],$_POST['BtnCheck']))
		{
			if(!empty($_POST['Key']) && !empty($_POST['BtnCheck']))
			{
				$key = self::MRES($_POST['Key']);
				
				if(!self::KeyChecker($key))
				{
					$_SESSION['infokeybuy'] = self::InfoMessage('Ключ введен неверно!', 'error');
					header("Location: ".S_URL_ROOT."buy");exit;
				}
				
				$result = S_QUERY("SELECT * FROM `{$cfgShop['tbl_keys']}` WHERE `key` LIKE '$key' AND `active` LIKE '1'");
				if(self::MNR($result)>0){
					$array		= self::MFA($result);
					$sum		= floatval($array['sum']);
					$real		= intval($array['realmoney']);
					$user		= S_P_NAME;
					$isBalanse	= "realmoney";
					$result1	= S_QUERY("UPDATE `{$bd_names['iconomy']}` SET `$isBalanse`=`$isBalanse`+$sum WHERE {$bd_money['login']}='$user'");
					$result2	= S_QUERY("UPDATE `{$cfgShop['tbl_keys']}` SET active='0',username='$user' WHERE `key`='$key'");
					
					if(!$result1)
					{
						$_SESSION['infokeybuy'] = self::InfoMessage('Ошибка базы данных1!', 'error');
						header("Location: ".S_URL_ROOT."buy");exit;
					}elseif(!$result2){
						$_SESSION['infokeybuy'] = self::InfoMessage('Ошибка базы данных2!'.mysql_error(), 'error');
						header("Location: ".S_URL_ROOT."buy");exit;
					}else{
						$_SESSION['infokeybuy'] = self::InfoMessage('Счёт успешно пополнен!', 'success');
						header("Location: ".S_URL_ROOT."buy");exit;
					}
					
				}else{
					$_SESSION['infokeybuy'] = self::InfoMessage('Ключ не существует или уже активирован!', 'error');
					header("Location: ".S_URL_ROOT."buy");exit;
				}
			}else{
				
			}
		}
		
		if(isset($_SESSION['infokeybuy'])){$info=$_SESSION['infokeybuy'];}else{$info='';}
		include(S_STYLE.$cfgShop['style']."KeyBuy.html");
		if(isset($_SESSION['infokeybuy'])){unset($_SESSION['infokeybuy']);}
		
		return ob_get_clean();
	}

	public function PermlistDel($id)
	{
		global $cfgShop;
		$id = intval($id);
		ob_start();

		$result = S_QUERY("SELECT id FROM `{$cfgShop['tbl_temp']}` WHERE id='$id'");
		if($result && self::MNR($result)>0)
		{
			
			if(	isset($_POST['ItemCheck'],$_POST['ItemSubmit'])	&&
				strtolower($_POST['ItemCheck'])==='on'	&&
				!empty($_POST['ItemSubmit']) )
			{
				$result1 = S_QUERY("DELETE FROM {$cfgShop['tbl_temp']} WHERE id=$id");
				$result2 = S_QUERY("ALTER TABLE {$cfgShop['tbl_temp']} AUTO_INCREMENT=0");
			
				if($result1 && $result2 && self::MNR($result1)>0){
					header("Location: ".S_URL_ROOT."permlist/");exit;
				}else{
					header("Location: ".S_URL_ROOT."permlist/");exit;
				}
			}
			if(isset($_SESSION['infodelpl'])){$info=$_SESSION['infodelpl'];}else{$info='';}
			include(S_STYLE.$cfgShop['style']."PermlistDel.html");
			if(isset($_SESSION['infodelpl'])){unset($_SESSION['infodelpl']);}
			
		}else{
			self::go404();
		}

		return ob_get_clean();
	}

	private static function PermlistIDs()
	{
		global $cfgShop;
		ob_start();

		if (isset($_GET['pid']) && $_GET['pid']>0){$pageID=intval($_GET['pid']);}else{$pageID=1;}
		$start = $pageID * $cfgShop['r_op_prmlst'] - $cfgShop['r_op_prmlst'];
		$result = S_QUERY("SELECT * FROM {$cfgShop['tbl_temp']} ORDER BY endtime ASC LIMIT $start, ".$cfgShop['r_op_prmlst']."");
		
		if($result && self::MNR($result)>0)
		{
			while($array = self::MFA($result))
			{
				$id			= intval($array['id'])						;
				$username	= self::HSC($array['username'])			;
				$buytime	= date('d.m.Y H:i:s', $array['buytime'])	;
				$endtime	= date('d.m.Y H:i:s', $array['endtime'])	;
				$tempgroup	= self::HSC($array['tempgroup'])			;
				$tid		= $id										; if(time()>$array['endtime']){$tid = '<i class="icon-ban-circle"></i> '.$tid;}
				include(S_STYLE.$cfgShop['style']."PermlistID.html")	;
			}
		}else{
				include(S_STYLE.$cfgShop['style']."PermlistNone.html");
		}
		
		return ob_get_clean();
	}

	public function Permlist()
	{
		global $cfgShop;
		ob_start();
		$PermlistIDs = self::PermlistIDs();
		include(S_STYLE.$cfgShop['style']."Permlist.html");
		return ob_get_clean();
	}

	public static function CheckTempUser()
	{
		global $cfgShop;
		$time = time();
		$query = S_QUERY("SELECT endtime FROM `{$cfgShop['tbl_temp']}` WHERE endtime<'$time'");
		$result = self::MNR($query);

		if($result <= 0){return false;}
		
		return true;
	}

	public function AlertCTU()
	{
		global $cfgShop;

		ob_start();
		if(self::CheckTempUser() && S_P_LVL>=15){include_once(S_STYLE.$cfgShop['style']."Modals/Modal_endOfDate.html");}
		return ob_get_clean();
	}
	
	public function KeyBuySuccess()
	{
		global $cfgShop;
		ob_start();
		include(S_STYLE.$cfgShop['style']."KeyBuySuccess.html");
		return ob_get_clean();
	}
	
	public function KeyBuyFail()
	{
		global $cfgShop;
		ob_start();
		include(S_STYLE.$cfgShop['style']."KeyBuyFail.html");
		return ob_get_clean();
	}

	private static function arrayTrans()
	{
		global $cfgShop;
		ob_start();

		$username = S_P_NAME;
		$result = S_QUERY("SELECT * FROM `{$cfgShop['tbl_trans']}` WHERE username='$username'");

		if($result && self::MNR($result)>0)
		{
			echo '<table width="100%" align="center">';
			echo '<tr> <td>ID транзакции</td> <td>Сумма</td> <td>Статус</td> </tr>';

			while($array = self::MFA($result))
			{
				$id			= intval($array['id']);
				$username	= self::HSC($array['username']);
				$amount		= floatval($array['amount']);
				$paid		= intval($array['paid']); if($paid==0){$paid='<font color="#DD0000">Не оплачен</font>';}else{$paid='<font color="#00DD00">Оплачен</font>';}
				echo '<tr><td>'.$id.'</td><td><a href="'.S_URL_ROOT.'d.'.$id.'">'.$amount.'</a></td><td>'.$paid.'</td></tr>';
			}
			
			echo '</table>';
		}else{
			$_SESSION['trans'] = self::InfoMessage('Примечание! [ Транзакций не существует ]', 'warning');
		}

		return ob_get_clean();
	}

	public function DepositTrans()
	{
		global $cfgShop;
		ob_start();

		$arrayTrans = self::arrayTrans();

		if(isset($_SESSION['trans'])){$info=$_SESSION['trans'];}else{$info='';}
		include(S_STYLE.$cfgShop['style']."DepositTrans.html");
		if(isset($_SESSION['trans'])){unset($_SESSION['trans']);}

		return ob_get_clean();
	}
	
	public function Deposit($id)
	{
		global $cfgShop;
		ob_start();
		$amount		= '';
		$did		= 'add';
		$action		= S_URL_ROOT.'d.'.$did;
		$username	= S_P_NAME;
		$sName		= 'submit';
		$sValue		= 'Отправить';
		$dis		= '';
		$deny		= '';

		if($id==='add'){
			if(isset($_POST['ik_am']))
			{
				$amount = floatval($_POST['ik_am']);

				if($amount<=0){
					$_SESSION['infodepos'] = self::InfoMessage('Ошибка! [ Сумма должна быть больше нуля ]', 'error');
					header("Location: ".S_URL_ROOT."d.add/");exit;
				}

				$result = S_QUERY("INSERT INTO `{$cfgShop['tbl_trans']}`
					(`username`,`amount`) VALUES
					('$username','$amount')");

				if(!$result){
					$_SESSION['infodepos'] = self::InfoMessage('Ошибка! [ Ошибка базы: <font color="#DD0000">'.mysql_error().'</font> ]', 'error');
					header("Location: ".S_URL_ROOT."d.add/");exit;
				}else{
					$result	= S_QUERY("SELECT id,username FROM `{$cfgShop['tbl_trans']}` WHERE username='$username' ORDER BY id DESC LIMIT 1");

					if(!$result || self::MNR($result)<=0){
						$_SESSION['infodepos'] = self::InfoMessage('Ошибка! [ Транзакция не существует ]', 'error');
						header("Location: ".S_URL_ROOT."d.add/");exit;
					}

					$array	= self::MFA($result);
					$did	= intval($array['id']);

					header("Location: ".S_URL_ROOT."d.".$did."/");exit;
				}
			}
		}else{
			$id		= intval($id);

			$result	= S_QUERY("SELECT * FROM `{$cfgShop['tbl_trans']}` WHERE username='$username' AND id='$id'");

			if(!$result || self::MNR($result)<=0){
				$_SESSION['infodepos'] = self::InfoMessage('Ошибка! [ У вас нет такой транзакции ]', 'error');
				header("Location: ".S_URL_ROOT."d.add/");exit;
			}

			$array	= self::MFA($result);
			$paid	= intval($array['paid']);
			if($paid!==0){
				$_SESSION['infodepos'] = self::InfoMessage('Ошибка! [ Задолженность по этой транзакции уже погашена ]', 'error');
				header("Location: ".S_URL_ROOT."d.add/");exit;
			}

			$action	= 'https://sci.interkassa.com';
			$amount	= floatval($array['amount']);
			$did	= $id;
			$sName	= 'process';
			$sValue	= 'Подтвердить';
			$dis	= 'readonly';
			$deny	= '<input type="submit" name="deny" class="btn" value="Удалить текущую транзакцию">';

			if(isset($_POST['deny'])){
				$result = S_QUERY("DELETE FROM `{$cfgShop['tbl_trans']}` WHERE username='$username' AND id='$id'");
				if(!is_bool($result) || !$result || self::MAR()!==1){
					$_SESSION['infodepos'] = self::InfoMessage('Ошибка! [ Произошла ошибка при удалении транзакции ]', 'error');
					header("Location: ".S_URL_ROOT."d.".$id."/");exit;
				}else{
					$_SESSION['infodepos'] = self::InfoMessage('Успешно! [ Транзакция удалена ]', 'success');
					header("Location: ".S_URL_ROOT."d.add/");exit;
				}
			}

		}

		if(isset($_SESSION['infodepos'])){$info=$_SESSION['infodepos'];}else{$info='';}
		include(S_STYLE.$cfgShop['style']."Deposit.html");
		if(isset($_SESSION['infodepos'])){unset($_SESSION['infodepos']);}

		return ob_get_clean();
	}
	
	public function DepositSuccess()
	{
		global $cfgShop;
		ob_start();
		include(S_STYLE.$cfgShop['style']."DepositSuccess.html");
		return ob_get_clean();
	}
	
	public function DepositFail()
	{
		global $cfgShop;
		ob_start();
		include(S_STYLE.$cfgShop['style']."DepositFail.html");
		return ob_get_clean();
	}



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