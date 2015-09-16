<?
include("mysql.php");
include("defines.php");
define("COLOR", "#F5F5F5");
define("ADVANCED_ON", true);

include("classes/main.php");
$info = $board->getInfo('search');

$view = View::main();
$view->set('info',$info);
$view->render('search');
