<?php
    include '../mysql.php';
    include '../defines.php';
    include '../classes/main.php';
    define("COLOR", "#F5F5F5");
define("ADVANCED_ON", true);
    if (isset($_POST['ajax'])){
        switch($_POST['type']){
            case 'search' : {
                $_GET = $_POST;
                $info = $board->getInfo('search');
                $board->search->getBread();
                $board->getBanner(0);
                if(isset($_GET['top_all'])){
	//$board->search->getBoard('top');
}else{
	//$board->search->getBoard('top4');
	$board->getBoard();
}
$board->getBanner(2);
$board->search->getPages();
     }break;
     }
    }
?>
