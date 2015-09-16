<?

if (isset($_GET['debug_showerrors'])) {
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);
}

define ("HOST", $_SERVER['HTTP_HOST']);
define ("HOME", "http://".$_SERVER['HTTP_HOST']."/");
define ("URL", "http://".HOST.$_SERVER['REQUEST_URI']);
define ("DIR", dirname (__FILE__));

include_once("classes/functions.php");

if(isset($_GET['r'])) {
	if($db->result("SELECT COUNT(*) FROM region WHERE href = '".functions::q($_GET['r'])."'")) {
	//var_dump($_GET['r']);
		define("REGION", $_GET['r']);
		if(isset($_GET['cat_href'])) {
			define("CATEGORY", $_GET['cat_href']);
			if($db->result("SELECT COUNT(*) FROM categories WHERE href = '".functions::q(CATEGORY)."'") == 0)
			header("Location: ".HOME."error/");
		}else define("CATEGORY", "");
	}elseif($db->result("SELECT COUNT(*) FROM categories WHERE href = '".functions::q($_GET['r'])."'")) {
		define("CATEGORY", $_GET['r']);
		define("REGION", "");
	}else header("Location: ".HOME."error/");
}else{
define("REGION", "");
define("CATEGORY", "");
}
?>
