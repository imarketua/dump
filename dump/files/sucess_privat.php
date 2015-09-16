<?php
include("mysql.php");
include("defines.php");

include("classes/main.php"); 

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
$pieces = explode("&", $_POST['payment']);
//print_r($pieces[2]);
$details = str_replace('details=', '', $pieces[2]);
$ext_details = str_replace('ext_details=', '', $pieces[3]);
echo $details;
echo $ext_details;
if(applyStatus($ext_details, $details))
{
	echo "<div id='content' align='left' class='additem-content'>
		<div class='green'>
			Услуга успешно активирована!
			<span class='links'>
			<a href=''>Мой профиль</a>
			<a href=''>Поиск по сайту</a>
			<a href=''>Техподдержка</a>
			</span>
		</div>
	</div>";
}