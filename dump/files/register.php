<?
include("mysql.php");
include("defines.php");
define("COLOR", "#F9F9F9");

include("classes/main.php");

$view = View::main();
$view->render('register');
