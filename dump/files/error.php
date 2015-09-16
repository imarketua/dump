<?
header("HTTP/1.0 404 Not Found");

include("mysql.php");
include("defines.php");

include("classes/main.php");
$board->switchActProfile();
$board->getInfo('profile');

$view = View::main();
$view->render('error');
