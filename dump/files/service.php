<?
include("mysql.php");
include("defines.php");
include("classes/main.php");
$board->switchActProfile();
$board->getInfo('profile');

$content = '';

// SillexLab edit
if ( ! $board->getAdmin())
{
	if (getUserId()['id'] != getAd($_GET['id'])['user_id'])
	{
		/*header("Location: http://www.i-market.com.ua/profile/");*/
		$content .= '<script type="text/javascript">
		  location="'.HOME.'profile/";
		  document.location.href="'.HOME.'profile/";
		  location.replace("'.HOME.'profile/");
		  window.location.reload("'.HOME.'profile/");
		  document.location.replace("'.HOME.'profile/");
		</script>';
		exit;
	}
}
function getAd($id)
{
	$query = "SELECT A.title as header, A.href as board_id, A.id as b_id, A.user_id,
		B.name as category, C.name as root_category, B.href as category_href,
		B.href as cat_href, C.href as root_href, D.name as city_name,
		A.*, B.*, C.*, D.* FROM
		board as A,
		categories as B,
		categories as C,
		region as D
		WHERE
		B.id = A.id_category and C.id = B.root_id and D.href = A.city
		and A.id = '".$id."' LIMIT 1";
	return mysql_fetch_assoc(mysql_query($query));
}

function getUserId()
{
	return mysql_fetch_assoc(mysql_query("SELECT id FROM users WHERE email='".$_SESSION['email']."' and pass='".$_SESSION['pass']."'"));
}

function applyStatus($id, $act)
{
	if($act=='top')
		$sql="UPDATE board SET top_time='".(14 * 86400 + time())."' WHERE id='{$id}'";
	elseif($act=='color')
		$sql="UPDATE board SET is_color='1' WHERE id='".functions::q($id)."'";
	elseif($act=='important')
		$sql="UPDATE board SET is_important='1' WHERE id='".functions::q($id)."'";

	return mysql_query($sql);
}

$services=array(
	'top'=>array('label'=>'В топ', 'id'=>12295),
	'color'=>array('label'=>'Выделить цветом', 'id'=>12296),
	'important'=>array('label'=>'Срочно', 'id'=>12298),
);

if($board->getAdmin())
{
	if(($service=$services[$_GET['act']])!==null && applyStatus($_GET['id'], $_GET['act']))
	{
		$content .= "<div id='content' align='left' class='additem-content'>
			<div class='green'>
				Услуга \"{$service['label']}\" успешно активирована!!!
				<span class='links'>
				<a class='btn btn-block' href=''>Мой профиль</a>
				<a class='btn btn-block' href=''>Поиск по сайту</a>
				<a class='btn btn-block' href=''>Техподдержка</a>
				</span>
			</div>
		</div>";
	}
}
else
{
	$content .= "<div id='content' align='left' class='additem-content'>";
	//$content .= $_GET['act'];
	if(isset($_GET['id']) && isset($_GET['act']))
	{
		require dirname(__FILE__)."/classes/smsbill.php";

		if(($service=$services[$_GET['act']])!==null)
		{
			//var_dump($_GET);
			//print_r($service);

			$smsbill=new SMSBill_getpassword;
			$smsbill->setServiceId($service['id']);
			$smsbill->useJQuery('no');
			$smsbill->useHeader('no');

			if (isset($_REQUEST['smsbill_password']))
			{
				if (!$smsbill->checkPassword($_REQUEST['smsbill_password']))
				{
					$content .= '<font color=\'red\'>Вы ввели не верный пароль. Пожалуйста, вернитесь назад и попробуйте еще раз</font>';
				}
				else
				{
					if($act=$_GET['act'])
					{
						if(applyStatus($_GET['id'], $act))
						{
							$content .= "<div id='content' align='left' class='additem-content'>
								<div class='green'>
									Услуга \"{$service['label']}\" успешно активирована!
									<span class='links'>
									<a class='btn btn-block' href=''>Мой профиль</a>
									<a class='btn btn-block' href=''>Поиск по сайту</a>
									<a class='btn btn-block' href=''>Техподдержка</a>
									</span>
								</div>
							</div>";
						}
					}
				}
			}
			else
			{
			?>
<?php
if($service['id'] == '12298') {
	$m_amount = number_format(20, 2, '.', '');
}
elseif($service['id'] == '12295') {
	$m_amount = number_format(80, 2, '.', '');
}
elseif($service['id'] == '12296') {
	$m_amount = number_format(40, 2, '.', '');
}

if($service['id'] == '12298') {
	$m_amount2 = '7.00';
	$h1 = '20.00';
	$h2 = '15.00';
}
elseif($service['id'] == '12295') {
	$m_amount2 = '30.00';
	$h1 = '80.00';
	$h2 = '50.00';
}
elseif($service['id'] == '12296') {
	$m_amount2 = '15.00';
	$h1 = '40.00';
	$h2 = '30.00';
}

$content .= '<div class="res-price" style="display:none;"><span class="prs1">'.$h1.'</span> <span class="prs2">'.$h2.'</span> <span class="prs3">'.$m_amount2.'</span> </div>';
$m_shop = '69833518';
$m_orderid = '1';
$m_curr = 'RUB';
$m_desc = base64_encode('Оплата');
$m_key = '448';

$arHash = array(
	$m_shop,
	$m_orderid,
	$m_amount,
	$m_curr,
	$m_desc,
	$m_key
);
$sign = strtoupper(hash('sha256', implode(':', $arHash)));
$content .= "<h1 align='center' style='margin: 0; padding: 0 0 10px 0; font-weight: normal;'>Оплата услуги \"{$service['label']}\" <span class='o-p' style='display:none;'><span class='tip-oplati'></span> стоит <span class='price-hover'>".$h1." гривен</span>  </span></h1>";

$content .= '<p id="service_button"> <a href="" class="smsbutt"></a> <a href="" class="intButt"></a>  <a href="" class="prw24butt"></a> </p>';

$content .= '<div class="toggle2">';
$content .= '<form  style="display:none;" method="GET" action="https://payeer.com/merchant/">';
$content .= '<input type="hidden" name="m_shop" value="'.$m_shop.'">';
$content .= '<input type="hidden" name="m_orderid" value="'.$m_orderid.'">';
$content .= '<input type="hidden" name="m_amount" value="'.$m_amount.'">';
$content .= '<input type="hidden" name="m_curr" value="'.$m_curr.'">';
$content .= '<input type="hidden" name="m_desc" value="'.$m_desc.'">';
$content .= '<input type="hidden" name="m_sign" value="'.$sign.'">';
$content .= '<input type="hidden" name="ACT"       value="'.$_GET['act'].'"/>';
$content .= '<input type="hidden" name="ID"       value="'.$_GET['id'].'"/>';
$content .= '
<!--
<input type="hidden" name="form[ps]" value="2609">
<input type="hidden" name="form[curr[2609]]" value="USD">
-->
';
$content .= '<input type="submit" name="m_process"  class ="intclick" value="Оплата через интернет" />
</form>
</div>';
$uniq =  uniqid();
$content .= '<div class="privat">
<form method="GET" action="https://api.privatbank.ua/p24api/ishop">';
$content .= '<input type="hidden" name="amt" value="'.$m_amount2.'" />';
$content .= '<input type="hidden" name="ccy" value="UAH" />
<input type="hidden" name="merchant" value="109758" />';
$content .= '<input type="hidden" name="order" value="'.$uniq.'" />';
$content .= '<input type="hidden" name="details" value="'.$_GET['act'].'" />';
$content .= '<input type="hidden" name="ext_details" value="'.$_GET['id'].'" />
<input type="hidden" name="pay_way" value="privat24" />
<input type="hidden" name="return_url" value="http://www.i-market.com.ua/" />
<input type="hidden" name="server_url" value="http://www.i-market.com.ua/sucess_privat.php" />
<button type="submit" class="privatForm"><img src="img/buttons/api_logo_1.jpg" border="0" /></button>
</form>
</div>
	<div class="toggle1">';
$content .= $smsbill->getForm();
}
			$content .= '</div>';

		}
		//$text = "Спасибо! Объявление успешно добавлено!";
		//include("success.php");
	}
	$content .= "</div>";
}

$view = View::main();
$view->set('content',$content);
$view->render('service');
