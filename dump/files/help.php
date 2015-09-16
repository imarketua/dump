<?
include("mysql.php");
include("defines.php");

include("classes/main.php");
$board->switchActProfile();
$board->getInfo('profile');

$view = View::main();
$view->render('help');
