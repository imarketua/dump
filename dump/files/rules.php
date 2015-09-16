<?
include("mysql.php");
include("defines.php");
include("classes/main.php");
$act = empty($_GET['act']) ? "index" : functions::q($_GET['act']);
$array = $db->returnFirst("SELECT * FROM pages WHERE href = '$act'");
$board->INFO = $array;
$board->getInfo();

$db->query("SELECT * FROM pages");
$pages = [];
while($page = mysql_fetch_array($db->data)) {
	$pages[] = $page;
}

$view = View::main();
$view->set('pages',$pages);
$view->render('rules');

?>
