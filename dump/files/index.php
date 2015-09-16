<?
include("mysql.php");
include("defines.php");
define("ADVANCED", true);
include("classes/main.php");

$info = $board->getInfo('index');
$array = [];
$db->query("SELECT * FROM region WHERE root_id = '0'");
while($r = mysql_fetch_assoc($db->data)) {
  $array[] = $r;
}

$view = View::main();
$view->set('index',true);
$view->set('area',$array);
$view->render('index');

?>
