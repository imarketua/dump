<?
include("mysql.php");
include("defines.php");

include("classes/main.php"); 
$board->switchActProfile();
$board->getInfo('profile');

function applyStatus($id, $act)
{
	if($act=='top')
		$sql="UPDATE board SET top_time='".(14 * 86400 + time())."' WHERE id='{$id}'";
	elseif($act=='color')
		$sql="UPDATE board SET is_color='1' WHERE id='{$id}'";
	elseif($act=='important')
		$sql="UPDATE board SET is_important='1' WHERE id='{$id}'";
	
	return mysql_query($sql);
}

include("design/header.php"); 
include("design/head.php");

$services=array(
	'top'=>array('label'=>'В топ', 'id'=>12295),
	'color'=>array('label'=>'Выделить цветом', 'id'=>12296),
	'important'=>array('label'=>'Срочно', 'id'=>12298),
);

if($board->getAdmin())
{
	if(($service=$services[$_GET['act']])!==null && applyStatus($_GET['id'], $_GET['act']))
	{
		echo "<div id='content' align='left' class='additem-content'>
			<div class='green'>
				Услуга \"{$service['label']}\" успешно активирована!!!
				<span class='links'>
				<a href=''>Мой профиль</a>
				<a href=''>Поиск по сайту</a>
				<a href=''>Техподдержка</a>
				</span>
			</div>
		</div>";
	}
}
else
{
	echo "<div id='content' align='left' class='additem-content'>";
	echo $_GET['act'];
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
					echo '<font color=\'red\'>Вы ввели не верный пароль. Пожалуйста, вернитесь назад и попробуйте еще раз</font>';
				}
				else
				{ 
					if($act=$_GET['act'])
					{				
						if(applyStatus($_GET['id'], $act))
						{
							echo "<div id='content' align='left' class='additem-content'>
								<div class='green'>
									Услуга \"{$service['label']}\" успешно активирована!
									<span class='links'>
									<a href=''>Мой профиль</a>
									<a href=''>Поиск по сайту</a>
									<a href=''>Техподдержка</a>
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
$m_shop = '69833518';
$m_orderid = '1';
$m_amount = number_format(1, 2, '.', '');
$m_curr = 'RUB';
$m_desc = base64_encode('Test');
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
?>
<p> <a href="">СМС</a> <a href="">Через интернет</a> </p>
<form class="toggle2" style="display:none;" method="GET" action="https://payeer.com/merchant/">
<input type="hidden" name="m_shop" value="<?=$m_shop?>">
<input type="hidden" name="m_orderid" value="<?=$m_orderid?>">
<input type="hidden" name="m_amount" value="<?=$m_amount?>">
<input type="hidden" name="m_curr" value="<?=$m_curr?>">
<input type="hidden" name="m_desc" value="<?=$m_desc?>">
<input type="hidden" name="m_sign" value="<?=$sign?>">
  			  <input name="ACT"       value="<?php echo $_GET['act']; ?>"/>
  			  <input name="ID"       value="<?php echo $_GET['id']; ?>"/> 
<!--
<input type="hidden" name="form[ps]" value="2609">
<input type="hidden" name="form[curr[2609]]" value="USD">
-->
<input type="submit" name="m_process" value="send" />
</form>						  			
			<?php				
				echo "<h1 align='center' style='margin: 0; padding: 0 0 10px 0; font-weight: normal;'>Оплата услуги \"{$service['label']}\"</h1>";
				echo $smsbill->getForm();
			}
		}
		//$text = "Спасибо! Объявление успешно добавлено!";
		//include("success.php");
	}
	echo "</div>";
}
include("design/bottom.php"); ?>