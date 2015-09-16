<?
include("mysql.php");
include("defines.php");
define ("COLOR", "#F9F9F9");
$FlagAddAdv = false;
include("classes/image.php");
include("classes/main.php");
$board->getInfo('add-item');
define ("PHOTO_ID", functions::generate_password(20));

$id_category	= false;
$errors			= false;
$title			= false;
$autor			= false;
$phone			= false;
$text			= false;
$price			= false;
$price_t		= false;
$city			= false;
$root_region	= false;
$address		= false;

function set_main($photo, $i){
	$r = new DataBase();
	$r->query("DELETE FROM photo_main WHERE folder = '".$photo['folder']."'");
	$r->query("INSERT INTO photo_main (folder, count) VALUES('".$photo['folder']."', ".$i.")");
}

if(isset($_GET['act']) && $_GET['act'] == 'complete')
{
	$text = "Спасибо! Объявление успешно добавлено!";
	include("success.php");
	exit;
}
elseif(isset($_GET['act']) && $_GET['act'] == 'changed')
{
	$text = "Объявление успешно отредактировано!";
	include("success.php");
	exit;
}
else
{
	if(count($_POST) > 0)
	{
	$errors = "";
	$fields = "";
	$values = "";
	$update = "";

	/**/

	$change = isset($_GET['act']) && $_GET['act'] != 'complete' && $_GET['act'] != 'changed';
	/*if($change) {
		$b = $db->result("SELECT user_id FROM board WHERE href = '".$_GET['act']."'");
		if(!$board->getAdmin() or $board->getUserId() != $b) exit("У Вас нет прав для редкатирования этого объявления!<br />");
	}*/
	$required = Array(
		"title" => "Заголовок",
		"id_category" => "Категория",
		"autor" => "Автор",
		"phone" => "Телефон",
		"email" => "E-mail",
		"text" => "Описание",
		"city" => "Город");

	$title = trim($_POST['title']);
	$text = trim($_POST['text']);
	$price = trim($_POST['price']);
	$price_t = trim($_POST['price_t']);
	$id_category = trim($_POST['id_category']);

    //echo '<pre>';var_dump($_POST);exit();
	$info = $_POST;
	$user_id = $board->checkUser($info['email']);
	$push = Array(
	'user_id' => $user_id,
	'href' => functions::translit($title).'-'.functions::generate_password(10),
	'status' => 'new'
	);
	$info = array_merge($info, $push);
	//$info['time'] = !$change ? time() : $db->query("SELECT time FROM board WHERE href = '".functions::q($_GET['act'])."'");
	if(!$change) { $info['time'] = time(); } else {
		unset($info['time']);
	}

	$db->query("SELECT * FROM photo WHERE folder = '".functions::q($_POST['photos_id'])."' ORDER BY `time` ASC");
		if($db->getNumRows() > 0)
		{
			$image = new SimpleImage();
			$i = 0;
			while($photo = mysql_fetch_array($db->data))
			{
				$url = 'photo/' . $photo['folder'] . '/' . $photo['name'];
				$image->load($url);
				if($i == $_POST['photo_c']) {
					set_main($photo, $i);
					$img = $image->image;
					$image->resizeToWidth(100);
					$image->save('photo/' . $photo['folder'] . '/' . 'avatar.jpg', IMAGETYPE_JPEG);
					$image->image = $img;
					unset($img);
				}
				$image->save($url);
				$i++;
			}
		}else{
			$info['photos_id'] = 0;
			$errors .= "Необходимо загрузить к объявлению хотя бы одну фотографию!<br />";
		}

	unset($info['photo_c']);
	foreach($required as $n => $inf)
	{
		if(!isset($info[$n])) $errors .= "Не заполнено обязательное поле \"".$inf."\"<br />";
	}
	foreach($info as $k => $v)
	{
		if(isset($required[$k]) && empty($v)) $errors .= "Не заполнено обязательное поле \"".$required[$k]."\"<br />";

		switch($k)
		{
			case 'email': if(!filter_var($v, FILTER_VALIDATE_EMAIL)) $errors .= "Неправильный e-mail!<br />";
			break;
			case 'price': if(!empty($v) && !is_numeric($v)) $errors .= "Цена может содержать только цифры!<br />";
			break;
		}

		$fields .= "$k, ";
		$values .= "'".functions::q($v)."', ";
		$update .= "$k = '".functions::q($v)."', ";
	}
	$fields = substr($fields, 0, strlen($fields) - 2);
	$values = substr($values, 0, strlen($values) - 2);
	$update = substr($update, 0, strlen($update) - 2);

	if($change) $query = "UPDATE board SET $update WHERE user_id = $user_id AND href = '".functions::q($_GET['act'])."'";
	else $query = "INSERT board($fields) VALUES ($values)";
	$result = $db->query("SELECT * from users WHERE email = '".$info['email']."'");
	$user = mysql_fetch_array($result);
	if(!empty($user['email']) && empty($user['phone'])){
		$db->query("UPDATE users SET name = '".$info['autor']."', phone = '".$info['phone']."', region = '".$info['city']."' WHERE id = " . $user['id']);
	}
//	echo $query;exit();
	if($errors == "")
	if($db->query($query)) {
	$FlagAddAdv = true;
	$replace = Array(
		"%EMAIL%" => $info['email'],
		"%BOARD%" => $info['title'],
		"%BOARD_LINK%" => 'http://'.$_SERVER['HTTP_HOST'].'/obj/'.$info['href'].'/',
		"%USERNAME%" => $info['autor']
	);
	if($change) $board->gomail($info['email'], "changeboard", $replace);
	else $board->gomail($info['email'], "boardpost", $replace);
	}
}

//var_dump($board->getUserId() , $db->returnFirst());

if(isset($_GET['act']))
{
	$db->query("SELECT * FROM board WHERE href = '".functions::q($_GET['act'])."'");
	$b = $db->returnFirst();
	//if(!$board->getAdmin() || $board->getUserId() != $b['user_id']) exit("У Вас нет прав для редкатирования этого объявления!<br />");
	if($board->getAdmin() || $board->getUserId() == $b['user_id']) {
		$values = Array("title", "city", "autor", "phone", "email", "type", "text", "id_category", "price", "price_t", "address", "photos_id");
		foreach ($values as $v)	$$v = $b[$v];

		$root_category = $db->result("SELECT root_id FROM categories WHERE id = '".functions::q($id_category)."'");
		$root_region   = $db->result("SELECT root_id FROM region WHERE href = '".functions::q($city)."'");
	}
}else{
	//$id_category = 0;
	//$root_category = 0;
	$root_category = $db->result("SELECT root_id FROM categories WHERE id = '".functions::q($id_category)."'");
	$type = 'S';
	$photos_id = PHOTO_ID;
	if($board->getUser()) $email = $board->email;
	else $email = "";
}


$view = View::main();
$view->set('errors',$errors);
$view->set('FlagAddAdv', $FlagAddAdv);
$view->set('photos_id', $photos_id);

$view->set('title', $title);
$view->set('autor', $autor);
$view->set('phone', $phone);
$view->set('text', $text);
$view->set('type', $type);
$view->set('price', $price);
$view->set('price_t', $price_t);
$view->set('city', $city);
$view->set('root_region', $root_region);
$view->set('address', $address);
$view->set('email', $email);
$view->set('root_category', $root_category);
$view->set('id_category', $id_category);

$view->render('additem');

}
?>
