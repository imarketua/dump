<?
include('../mysql.php');
include('../defines.php');
include('../classes/main.php');
if(isset($_GET['q']))
{
$board->getCities($_GET['q']);
}elseif(isset($_GET['reg_id']))
{
	$array = Array();
	$db->query("SELECT * FROM region WHERE root_id='".functions::q($_GET['reg_id'])."' ORDER BY name ASC");
	while($reg = mysql_fetch_array($db->data))
	{
		$array[$reg['href']] = $reg['name'];
	}
	echo json_encode($array);
}
elseif(isset($_GET['cat_id']))
{
	$array = Array();
	$db->query("SELECT * FROM categories WHERE root_id='".functions::q($_GET['cat_id'])."'");
	while($reg = mysql_fetch_array($db->data))
	{
		$array[$reg['id']] = $reg['name'];
	}
	echo json_encode($array);
}
elseif(isset($_GET['op']) && $_GET['op'] == 'regions')
{
	$board->getAjaxRegions();
}
elseif(isset($_GET['op']) && $_GET['op'] == 'phone')
{
	$phone = $db->result("SELECT phone FROM board WHERE id = '".functions::q($_GET['board_id'])."'");
	if(!empty($phone)){
		 echo $phone;
		$res = $db->query('SELECT ip FROM board_hits WHERE type = \'phone\' AND ip = \'' . functions::q($_SERVER['REMOTE_ADDR']) . "' AND board_id = " . functions::q($_GET['board_id']));
	 	$ip = mysql_fetch_array($res);
		if(!$ip['ip']) $db->query("INSERT INTO board_hits(type, board_id, ip) VALUES ('phone', ".functions::q($_GET['board_id']).", '".functions::q($_SERVER['REMOTE_ADDR'])."')");
	}else{
		echo "Нет телефона";
	}
}
elseif(isset($_GET['op']) && $_GET['op'] == 'mailmessage')
{
	$b = $db->returnFirst("SELECT * FROM board WHERE id = '".functions::q($_GET['board_id'])."'");
	$replace = Array(
		"%BOARD%" => $b['title'],
		"%EMAIL%" => $_GET['email'],
		"%TEXT%" => "<div style='padding: 10px; background: #F5F5F5;'>".$_GET['text']."</div>"
	);
	if($board->gomail($b['email'], "mailmessage", $replace))
	echo "ok";
	else echo "Неизвестная ошибка!";
}
?>
