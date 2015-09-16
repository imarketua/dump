<?
include("mysql.php");
include("defines.php");
define("COLOR", "#F5F5F5");
include("classes/main.php");
$view = View::main();
$view->render('board');
?>
