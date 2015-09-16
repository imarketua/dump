<?
@session_start();

if (!isset($_SESSION['detect'])) {
	$useragent=$_SERVER['HTTP_USER_AGENT'];

	if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
		$_SESSION['mobile'] = true;

	$_SESSION['detect'] = true;
}

include("widgets.php");
include_once("functions.php");
include("searchclasses.php");
include("profile_content.php");
include("view.php");

class board extends widgets
{
	var $db;
	var $search;

	function __construct()
	{
		$this->db = new DataBase();
		$this->search = new Search();
		if(isset($_COOKIE['remember']) && !$this->getUser()) {
			$data = explode(":", $_COOKIE['remember']);
			$this->db->query("SELECT * FROM users WHERE MD5(email) = '".$data[0]."' and pass = '".$data[1]."'");
			if($this->db->getNumRows() > 0)
			{
				$arr = $this->db->returnFirst();
				$_SESSION['email'] = $arr['email'];
				$_SESSION['pass']  = $arr['pass'];
			}
		}
		$this->setInf();
	}
	function getBoard()
	{
		$this->search->getBoard();
	}
	function getBoardMobile()
	{
		$this->search->getBoardMobile();
	}
	function check_hit($id){
		$db = new DataBase();
		$res = $db->query('SELECT ip FROM board_hits WHERE type = \'board\' AND ip = \'' .functions::q( $_SERVER['REMOTE_ADDR']) . "' AND board_id = " . functions::q($id));
		$ip = mysql_fetch_array($res);
		if(!$ip['ip']) $db->query("INSERT INTO board_hits (board_id, ip, type) VALUES (".functions::q($id).", '".functions::q($_SERVER['REMOTE_ADDR'])."', 'board')");
	}
	function getAd($ad)
	{
		$query = $this->db->query("SELECT A.title as header, A.href as board_id, A.id as b_id, A.user_id,
			B.name as category, C.name as root_category, B.href as category_href,
			B.href as cat_href, C.href as root_href, D.name as city_name,
			A.*, B.*, C.*, D.* FROM
			board as A,
			categories as B,
			categories as C,
			region as D
			WHERE
			B.id = A.id_category and C.id = B.root_id and D.href = A.city
			and A.href = '".functions::q($ad)."' LIMIT 1");
		while($b = mysql_fetch_assoc($query))
		{
			self::check_hit($b['b_id']);
			$this->INFO['title'] = $b['header']." - ".$b['category']." на %SITE%";
			$this->INFO['description'] = $b['header'].($b['price'] != 0 ? " - ". $b['price'] . ' ' . (($b['price_t'] == 'uah') ? 'грн' : '$' ) ."." : "").", ".$b['category']." на %SITE%";
			$this->search->R = $b['city'];
			$this->search->CAT_HREF = $b['cat_href'];
			$this->search->COUNT_ADS = 5;
			$this->search->setInf();
			$this->getInfo();
			$board = $this;
			echo "<div id='content' align='left' class='mtop0'>";
			$q = $this->db->query("SELECT * FROM photo WHERE folder = '".functions::q($b['photos_id'])."' ORDER BY time ASC");
			$i = 0;
			$photo = Array();
			while($p = mysql_fetch_assoc($q)) {
				$url = HOME.'photo/'.$p['folder'].'/'.$p['name'];
				$url_local = DIR.'/photo/'.$p['folder'].'/'.$p['name'];
				$size = functions::getFullSize($url_local, 553, 373);
				$size_min = functions::getFullSize($url_local, 90, 70);
				$photo[$i] = Array($url, $size['width'], $size['height'], $size_min['width'], $size_min['height']);
				$i++;
			}

			echo "<div class='board-content'>";
			echo "<div class='bread p10px bbottom-eee'>";
			echo "<a href='".HOME."search/'>Все объявления</a>";
			$this->search->getBreadLink(HOME.$b['city']."/", trim($b['city_name']));
			$this->search->getBreadLink(HOME.$b['city'].'/'.$b['root_href']."/", trim($b['root_category']));
			$this->search->getBreadLink(HOME.$b['city'].'/'.$b['cat_href']."/", trim($b['category']));
			echo "</div>
			</div>";
			echo "<div class='mtop11 board-content'>";

			// left
			echo "<div class='w600px left'>";

			$rel="";
			echo "<div class='photos-mini right'>";

			for($i = 0; $i < count($photo); $i++)
			{
				preg_match('/\.([^\.]+)$/',$photo[0][0],$a);
				echo "<div class='m5".($i == 0 ? " mtop0" : ($i == count($photo) - 1 ? " mbottom0" : ""))."'>";
				if ($i > 0) $rel=" rel='gallery'";

				echo "<a class='fullp' href='".str_replace($a[1],'full.'.$a[1],$photo[$i][0])."' ".$rel." class='photo-gallery' style='display:none;' title='" . ($i + 1) . "'>
					<img src='".$photo[$i][0]."' width='".$photo[$i][3]."' height='".$photo[$i][4]."'
						data-width='".$photo[$i][1]."'  data-height='".$photo[$i][2]."' ".($i == 0 ? " class='op1'" : "")."/>
					</a>";

				echo "<a href='".$photo[$i][0]."' class='photo-gallery' title='" . ($i + 1) . "'>
				<img src='".$photo[$i][0]."' width='".$photo[$i][3]."' height='".$photo[$i][4]."'
				data-width='".$photo[$i][1]."'  data-height='".$photo[$i][2]."' ".($i == 0 ? " class='op1'" : "")."/>
				</a>";
				echo "</div>";
			}
			echo "<div class='cboth'></div>";
			echo "</div>";
			echo "<div class='photo left' align='left'>";
			if (isset($a))
			echo "<a class='fullp' rel='gallery' title='1' id='fullp' href='".str_replace($a[1],'full.'.$a[1],$photo[0][0])."'><img src='".$photo[0][0]."' id='main-photo'/></a>";
			/*for($i = 0; $i <= count($photo); $i++)
			{
				echo "<img src='".$photo[]."' width='' height=''/>";
			}*/
			echo "</div>";

			echo "<div class='cboth'></div>";
			echo "</div><!-- /.left -->";

			// right
			echo "<div class='w500px right'>";

			echo "<h1 class='board-h1'>".$b['header']."</h1>";

			// SillexLab edit
            if ($this->getUserId() == $b['user_id'] || $this->getAdmin())
            {
				echo '<div class="service-buttons-wrap">';
				echo '<div class="service-buttons"><a class="button service" href="service/top/'.$b['b_id'].'"><img src="img/button-top.png" class="img-button"></a>';
				echo '<a class="button service" href="/service/color/'.$b['b_id'].'" style=""><img src="img/button-color.png" class="img-button"></a><a class="button service" href="/service/important/'.$b['b_id'].'" style=""><img src="img/button-time.png" class="img-button"></a></div>';
				echo '</div>';
			}
			/*if ($this->getAdmin()) echo "<div class='service-buttons'>
			<a class='button service' href='service/top/".$b['b_id']."'>В топ!</a>
			<a class='button service' href='/service/color/".$b['b_id']."'>Выделить цветом</a>
			<a class='button service' href='/service/important/".$b['b_id']."'>Сделать срочным</a></div>";
			*/
			$price = empty($b['price']) ? "---" : $b['price']." ".(($b['price_t'] == 'uah') ? 'грн' : '$' ).".";
			echo "<div class='board-price'>
				<span class='price-num'>$price</span></div>";
			echo "<div class='board-content w410px mtop11'>";
			echo "<table cellpadding='0' cellspacing='0' class='board-info'>
				<tr><td class='td1'>Автор:</td><td><strong>".$b['autor']."</strong> <a href='".HOME."search/?hash=".md5($b['email'])."' style='font-size: 11px;'>(Все объявления владельца)</a></td></tr>
				<tr><td class='td1'>Статус</td>
				<td><strong>".($b['type'] == 'S' ? "Частное лицо" : "Бизнес")."</strong></td></tr>
				<tr><td class='td1 bbottom-none'>Город:</td>
				<td class='bbottom-none'><strong>".$b['city_name']."</strong></td></tr>";
			if ($b['address'] != '') {
				echo "<tr><td class='td1 bbottom-none'>Адрес:</td>
					<td class='bbottom-none'><strong>".$b['address']."</strong> <span id='board-address' style='display: none'>".$b['city_name'].", ".$b['address']."</span><a href='javascript:void();' id='showAddress'>(Показать на карте)</a></td></tr>";
			}

			echo "
				</table>
				</div>";
			if ($b['phone'] != '') {
				$phone = substr($b['phone'], 0, 3)."XXXXXXXXXX";
				echo "<div class='board-content w410px' align='left'>

				<div class='p10px'><span class='phone-content' data-id='".$b['b_id']."'>".$phone."<br><a href='javascript:void(0);'>Показать номер</a></span></div>
				</div>";
				}
			/*if($b['address'] != '') {
				echo "<div class='board-content w410px mtop21' align='center'>";
				echo "<div class='relative admap'>
				<span class='block'>
				Адрес:
				</span>
				<div id='board-address' class='mtop3 bold'>".$b['city_name'].', '.$b['address']."</div>
				<div class='mtop11 bold'><a href='javascript:void();' class='showAddress' id='showAddress'>Показать на карте</a></div>
				</div>";
				echo "</div>";
			}*/
			echo "<div class='board-content w246px mtop21' align='center'>";
			$this->getBanner(3);

			echo "</div>";
			echo "</div><!-- /.right -->";


			//
			$this->getBanner(4);
			echo "<div class='cboth'></div>";
			echo "</div>";

			echo "<div class='board-content'>";
			echo "<div class='ad-text p10px'>".nl2br(strip_tags($b['text']))."</div>";

			echo '<div class="breee">';
			// left
			echo "<div class='w500px left'>";
			echo "<div class='p10px bright-none'>";
			echo "<form action='' id='mailmessage' data-board-id='".$b['b_id']."'>";
			echo "<span class='mh1 arial c555'>Связаться с автором</span>";
			echo "<div class='pbt5'>
			<input type='text' name='email' class='form-input w410px' data-placeholder='Введите Ваш e-mail для ответа'
			data-validate='v_required,v_email'/></div>";
			echo "<div class='pbt5'>
			<textarea class='additem-input additem-textarea  w410px' name='text' data-placeholder='Введите сообщение'
			data-validate='v_required,v_min_15'/></textarea></div>";
			echo "<div class='pbt5'>";
			echo "<button type='submit' class='button2'>
				<div class='submit'>Отправить</div>
				</button>";
			echo "</form>";
			echo "</div>";
			echo "</div>";
			echo "</div><!-- /.left -->";

			// right
			echo "<div class='w600px right'>";
			$this->search->getBoard("otherboard");
			echo "</div><!-- /.right -->";

			echo "<div class='cboth'></div>";
			echo "</div>";
						?>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Под связью -->
<ins class="adsbygoogle"
     style="display:inline-block;width:660px;height:90px"
     data-ad-client="ca-pub-2847869300143854"
     data-ad-slot="3254849124"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
			<?
		}
	}
	function getAdMobile($ad)
	{
		$query = $this->db->query("SELECT A.title as header, A.href as board_id, A.id as b_id, A.user_id,
			B.name as category, C.name as root_category, B.href as category_href,
			B.href as cat_href, C.href as root_href, D.name as city_name,
			A.*, B.*, C.*, D.* FROM
			board as A,
			categories as B,
			categories as C,
			region as D
			WHERE
			B.id = A.id_category and C.id = B.root_id and D.href = A.city
			and A.href = '".functions::q($ad)."' LIMIT 1");
		while($b = mysql_fetch_assoc($query))
		{
			self::check_hit($b['b_id']);
			$this->INFO['title'] = $b['header']." - ".$b['category']." на %SITE%";
			$this->INFO['description'] = $b['header'].($b['price'] != 0 ? " - ". $b['price'] . ' ' . (($b['price_t'] == 'uah') ? 'грн' : '$' ) ."." : "").", ".$b['category']." на %SITE%";
			$this->search->R = $b['city'];
			$this->search->CAT_HREF = $b['cat_href'];
			$this->search->COUNT_ADS = 5;
			$this->search->setInf();
			$this->getInfo();
			$board = $this;
			$q = $this->db->query("SELECT * FROM photo WHERE folder = '".functions::q($b['photos_id'])."' ORDER BY time ASC");
			$i = 0;
			$photo = Array();
			while($p = mysql_fetch_assoc($q)) {
				$url = HOME.'photo/'.$p['folder'].'/'.$p['name'];
				$url_local = DIR.'/photo/'.$p['folder'].'/'.$p['name'];
				$size = functions::getFullSize($url_local, 553, 373);
				$size_min = functions::getFullSize($url_local, 90, 70);
				$photo[$i] = Array($url, $size['width'], $size['height'], $size_min['width'], $size_min['height']);
				$i++;
			}


			$rel="";
			echo "<div class='row'>";
			echo "<div class='gallery'>";
			echo "<div id='item-gallery' class='owl-carousel owl-theme'>";
			for($i = 0; $i < count($photo); $i++)
			{
				if (isset($a[1]))
				echo "<div class='item'><img src='".str_replace($a[1],'full.'.$a[1],$photo[$i][0])."' alt='' id='main-photo'></div>";
			}
			echo "</div>";
			echo "</div>";
			echo "</div>";


			echo "<section class=\"item\">";
			echo "<div class='row'>";
			echo "<div class='item-data'>";

			echo '<div class="item-header">
						 <h4>'.$b['header'].'</h4>
					 </div>';

			// SillexLab edit
            if ($this->getUserId() == $b['user_id'] || $this->getAdmin())
            {
				echo '<div class="service-buttons-wrap">';
				echo '<div class="service-buttons"><a class="button service" href="service/top/'.$b['b_id'].'"><img src="img/button-top.png" class="img-button"></a>';
				echo '<a class="button service" href="/service/color/'.$b['b_id'].'" style=""><img src="img/button-color.png" class="img-button"></a><a class="button service" href="/service/important/'.$b['b_id'].'" style=""><img src="img/button-time.png" class="img-button"></a></div>';
				echo '</div>';
			}
			/*if ($this->getAdmin()) echo "<div class='service-buttons'>
			<a class='button service' href='service/top/".$b['b_id']."'>В топ!</a>
			<a class='button service' href='/service/color/".$b['b_id']."'>Выделить цветом</a>
			<a class='button service' href='/service/important/".$b['b_id']."'>Сделать срочным</a></div>";
			*/
			$price = empty($b['price']) ? "---" : $b['price']." ".(($b['price_t'] == 'uah') ? 'грн' : '$' ).".";
			echo "<div class='item-price'><span>$price</span></div>";
			echo "<div class='item-author-data'>";
			echo '<div class="item-author-name"><div class="ia-label">Автор:</div><div class="id-data">'.$b['autor'].' <a href="'.HOME.'search/?hash='.md5($b['email']).'">(Все объявления владельца)</a></div></div>';
			echo '<div class="item-author-type"><div class="ia-label">Статус:</div><div class="id-data">'.($b['type'] == 'S' ? "Частное лицо" : "Бизнес").'</div></div>';
			echo '<div class="item-author-city"><div class="ia-label">Город:</div><div class="id-data">'.$b['city_name'].'</div></div>';

			if ($b['address'] != '') {
				echo '<div class="item-author-city"><div class="ia-label">Адрес:</div><div class="id-data">'.$b['city_name'].', '.$b['address'].'</div></div>';
			}

			if ($b['phone'] != '') {
				$phone = substr($b['phone'], 0, 3)."XXXXXXXXXX";
				echo '<div class="item-author-phone"><div class="ia-label">Город:</div><div class="id-data" class="phone-content" data-id="'.$b['b_id'].'">'.$phone.' <a href=\'javascript:void(0);\'>Показать номер</a></div></div>';
				}

			echo "<div class='center'>";
			$this->getBanner(3);

			echo "</div>";


			//
			$this->getBanner(4);
			echo "</div>";

			echo "<div class='row'>";
			echo "<div class='item-description'>".nl2br(strip_tags($b['text']))."</div>";
			echo "</div>";

			echo "</div>";
			echo "</div>";

			echo "</section>";

			echo '<section class="sayhi">
      			<div class="container">';
			?>
			<h4>Связаться с автором</h4>
        <div class="row">
          <div class="sayhi-form">
            <form action='' id="mailmessage" data-board-id="<?=$b['b_id']?>">
              <div class="sh-form-group">
                <input type="text" name="email" data-validate="v_required,v_email" placeholder="Введите ваш e-mail для ответа">
              </div>
              <div class="sh-form-group">
                <textarea name="text" id="plc1" data-validate="v_required,v_min_15" placeholder="Введите сообщение"></textarea>
              </div>
              <div class="sh-submit">
                <button type="submit">Отправить</button>
              </div>
            </form>
          </div>
        </div>
        <hr>
        <div class="modal fade" tabindex="-1" id="successful_send" role="dialog" aria-labelledby="mySmallModalLabel">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">Сообщение успешно отправлено</h4>
              </div>
              <div class="modal-body">
                Сообщение успешно отправлено автору.<br />Ожидайте ответа на e-mail.
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" onClick="$('.sayhi').slideUp(400);$('html, body').animate({ scrollTop: 0 }, 'slow');">Закрыть</button>
              </div>
            </div>
          </div>
        </div>
				<?php

			// right
			echo "<div class='row'>";
			$this->search->getBoardMobile("otherboard");
			echo "</div>";

			echo "</div>";
			echo "</section>";
						?>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Под связью -->
<ins class="adsbygoogle"
     style="display:inline-block;width:660px;height:90px"
     data-ad-client="ca-pub-2847869300143854"
     data-ad-slot="3254849124"></ins> 
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
			<?
		}
	}
	function getProducts($num = 8) {
		$query = $this->db->query("SELECT A.title as header, A.href as board_id, A.id as b_id, A.user_id,
			B.name as category, C.name as root_category, B.href as category_href,
			B.href as cat_href, C.href as root_href, D.name as city_name,
			A.* FROM
			board as A,
			categories as B,
			categories as C,
			region as D
			WHERE
			B.id = A.id_category and C.id = B.root_id and D.href = A.city and A.status = 'ok' and A.top_time > ".time()." ORDER BY RAND()
			LIMIT ".intval($num));

		$products = [];
		$n = 0;

		while($b = mysql_fetch_assoc($query)) {
				if($b['photos_id'] != "") {
				$this->db->query("SELECT * FROM photo WHERE folder = '".functions::q($b['photos_id'])."' LIMIT 1");
				while($p = mysql_fetch_assoc($this->db->data)) {
					$url = HOME.'photo/'.$p['folder'].'/'.$p['name'];
				}
			}

			$products[] = (object) ['product' => $b, 'avatar' => $url];
			$n++;
		}

		return (object) ['all' => $products, 'count' => $n];
	}
	function _mark()
	{
		if(isset($_POST['op']) && $this->getAdmin())
		{
			foreach($_POST['ad'] as $k=>$v)
			{
				if ($_POST['op'] == 'drop') {
					$this->_drop($k);
				} else {
					$this->db->query("UPDATE board SET status = '".functions::q($_POST['op'])."' WHERE id = '".functions::q($k)."'");
					$board = $this->db->returnFirst("SELECT * FROM board WHERE id = '".functions::q($k)."'");
					$replace = Array(
						"%BOARD%" => $board['title'],
						"%BOARD_LINK%" => HOME.'obj/'.$board['href'].'/'
					);
					$this->gomail($board['email'], $_POST['op'] == 'ok' ? "board-ok" : "board-wrong", $replace);
				}
			}
			 
		@header("Location: ".HOME."profile/admin/new/");exit;
		}
	}
	function get_count($id){
            $db = new DataBase();
	    $res = $db->query("select SUM(if(type = 'board',1,0)) as 'bcount', SUM(if(type = 'phone',1,0)) as 'pcount' from board_hits WHERE board_id = " . $id);
            return mysql_fetch_array($res);
	}
	function ___profile()
	{
		echo "<h1 class='mh1 mtop11 bold pleft5 mbottom0'>Мои объявления</h1>";
		$this->search->PAGE = !empty($_GET['page']) ? $_GET['page'] : 1;
		$this->search->NUM = $this->db->result("SELECT COUNT(*) FROM board WHERE user_id = '".$this->getUserId()."'");
		$this->search->COUNT_ADS = 10;
		$this->db->query("SELECT * FROM board WHERE user_id = '".$this->getUserId()."' LIMIT ".$this->search->getPageLimit().", ".$this->search->COUNT_ADS);
		while($board = mysql_fetch_assoc($this->db->data))
		{
			$data = self::get_count($board['id']);
			echo "<table class='board-profile' cellpadding='0' cellspacing='0'>";
			echo "<tr>";
			echo "<td class='bbottom-t'>";
			echo "<table cellpadding='0' cellspacing='0'>";
			echo "<td class='td1'>";
			echo $this->search->getDate($board['time']);
			echo "</td>";
			echo "<td class='td2'>";
			echo "<a href='".HOME."obj/".$board['href']."/'><strong>".$board['title']."</strong></a>";
			echo "</td>";
			echo "<td class='td3'>";
			if($board['status'] == 'new')
			{
				echo "<span class='grey-info'>На модерации</span>";
				echo "<td class='td3' style='text-align: center;'>";
				echo "<a style=\"border: 1px solid rgb(53, 152, 207); padding: 5px 10px; background-color: rgb(84, 182, 254); border-radius: 5px; font-size: 13px; font-weight: 600; color: #fff; line-height: 50px; \"/ class=\"3button\"/ href='".HOME."add-item/".$board['href']."/'>Редактировать</a><br /><a style=\"border: 1px solid rgb(53, 152, 207); padding: 5px 10px; background-color: rgb(84, 182, 254); border-radius: 5px; font-size: 13px; font-weight: 600; color: #fff; \"/class=\"3button\"/ href='".HOME."profile/drop/".$board['id']."/'>Удалить</a><br />";
				echo "</td>";
			}
			elseif($board['status'] == 'wrong')
			{
				echo "<span class='red-info'>Запрещено</span>";
				echo "<td class='td3' style='text-align: center;'>";
				echo "<a style=\"border: 1px solid rgb(53, 152, 207); padding: 5px 10px; background-color: rgb(84, 182, 254); border-radius: 5px; font-size: 13px; font-weight: 600; color: #fff; line-height: 50px; \"/ class=\"3button\"/ href='".HOME."add-item/".$board['href']."/'>Редактировать</a><br /><a style=\"border: 1px solid rgb(53, 152, 207); padding: 5px 10px; background-color: rgb(84, 182, 254); border-radius: 5px; font-size: 13px; font-weight: 600; color: #fff; \"/class=\"3button\"/ href='".HOME."profile/drop/".$board['id']."/'>Удалить</a><br />";
				echo "</td>";
			}
			else {
					echo "Просмотров: ".(($data['bcount']) ? $data['bcount'] : '0' )."<br />Открыто телефонов: ".(($data['pcount']) ? $data['pcount'] : '0' )."<br />";
					echo "<a style=\"border: 1px solid rgb(53, 152, 207); padding: 5px 10px; background-color: rgb(84, 182, 254); border-radius: 5px; font-size: 13px; font-weight: 600; color: #fff; line-height: 50px; \"/ class=\"3button\"/ href='".HOME."add-item/".$board['href']."/'>Редактировать</a><br /><a style=\"border: 1px solid rgb(53, 152, 207); padding: 5px 10px; background-color: rgb(84, 182, 254); border-radius: 5px; font-size: 13px; font-weight: 600; color: #fff; \"/class=\"3button\"/ href='".HOME."profile/drop/".$board['id']."/'>Удалить</a><br />";
					echo "<td class='td3' style='text-align: center;'>";
						echo '<div class="service-buttons"><a class="button service" href="service/top/'.$board['id'].'"><img src="img/button-top.png" class="img-button"></a>';
                        echo '<a class="button service" href="/service/color/'.$board['id'].'" style=""><img src="img/button-color.png" class="img-button"></a><a class="button service" href="/service/important/'.$board['id'].'" style=""><img src="img/button-time.png" class="img-button"></a></div>';
                        echo "</td>";
				}
			echo "</td>";

			echo "</table>";
			echo "</td>";
			echo "</tr>";
			echo "</table>";
		}
		echo "<div class='pleft5'>";
		$this->search->getPages(HOME.'profile/?');
		echo "</div>";
	}
	function ___profileMobile()
	{
		echo "<h1>Мои объявления</h1>";
		$this->search->PAGE = !empty($_GET['page']) ? $_GET['page'] : 1;
		$this->search->NUM = $this->db->result("SELECT COUNT(*) FROM board WHERE user_id = '".$this->getUserId()."'");
		$this->search->COUNT_ADS = 10;
		$this->db->query("SELECT * FROM board WHERE user_id = '".$this->getUserId()."' LIMIT ".$this->search->getPageLimit().", ".$this->search->COUNT_ADS);
		while($board = mysql_fetch_assoc($this->db->data))
		{
			$data = self::get_count($board['id']);
			echo "<table class=\"result-table\">";
			echo "<tr>";
			echo "<td>";
			echo "<table class=\"result-table\">";
			echo '<td class="date"><div class="count"><span class="date">'.$this->search->getDate($board['time']).'</span></div></td>';
			echo "<td class='text'>";
			echo "<a href='".HOME."obj/".$board['href']."/'><strong>".$board['title']."</strong></a><br>";
			if($board['status'] == 'new')
			{
				echo "<span class='grey-info'>На модерации</span>";
				echo '<div class="btn-group btn-group-sm" role="group" aria-label="Small button group">';
				echo "<a class='btn btn-info' href='".HOME."add-item/".$board['href']."/'><i class='glyphicon glyphicon-pencil'></i></a><a class='btn btn-danger' href='".HOME."profile/drop/".$board['id']."/'><i class='glyphicon glyphicon-trash'></i></a>";
				echo '</div> ';
			}
			elseif($board['status'] == 'wrong')
			{
				echo "<span class='red-info'>Запрещено</span>";
				echo '<div class="btn-group btn-group-sm" role="group" aria-label="Small button group">';
				echo "<a class='btn btn-info' href='".HOME."add-item/".$board['href']."/'><i class='glyphicon glyphicon-pencil'></i></a><a class='btn btn-danger' href='".HOME."profile/drop/".$board['id']."/'><i class='glyphicon glyphicon-trash'></i></a>";
				echo '</div> ';
			}
			else {
					echo "Просмотров: ".(($data['bcount']) ? $data['bcount'] : '0' )."<br />Открыто телефонов: ".(($data['pcount']) ? $data['pcount'] : '0' )."<br />";
					echo '<div class="btn-group btn-group-sm" role="group" aria-label="Small button group">';
					echo "<a class='btn btn-info' href='".HOME."add-item/".$board['href']."/'><i class='glyphicon glyphicon-pencil'></i></a><a class='btn btn-danger' href='".HOME."profile/drop/".$board['id']."/'><i class='glyphicon glyphicon-trash'></i></a>";
					echo '</div> ';
					echo '<div class="btn-group btn-group-sm" role="group" aria-label="Small button group"><a class="btn btn-primary" href="service/top/'.$board['id'].'">Поднять в топ</a>';
												echo '<a class="btn btn-success" href="/service/color/'.$board['id'].'" style="">Выделить цветом</a> <a class="btn btn-warning" href="/service/important/'.$board['id'].'" style="">Сделать срочным</a></div>';
												echo "</td>";
				}
			echo "</td>";

			echo "</table>";
			echo "</td>";
			echo "</tr>";
			echo "</table>";
		}
		$this->search->getPagesMobile(HOME.'profile/?');
	}
	function switchActProfile()
	{
		$this->profile_content = new profile_content($this->getUserId());
		if(isset($_POST['email']) && isset($_POST['pass'])){
			$res = $this->db->query("SELECT activated FROM users WHERE email = '".functions::q($_POST['email'])."'");
	 	 	$active = mysql_fetch_array($res);
			if(!$active['activated']){
				header("Location: ".HOME."profile/");
				exit;
			}else{
				$this->enter();
			}
		}
		if (isset($_GET['act'])) {
			switch($_GET['act'])
			{
				case "exit":
				$this->_exit();
				case "drop":
				$this->_drop($_GET['op']);
				header('Location: '.$_SESSION['url']);exit;
				case "mark_messages":
				if($this->getAdmin()) $this->_mark();
				case "settings":
				if(isset($_POST['pass']) && $this->getUser()) $this->changePass();
				elseif(isset($_POST['email']) && $this->getUser()) $this->changeEmail();
			}
		}
	}
	function _exit()
	{
		@setcookie ('remember', "", time()-999999, '/');
		$_SESSION['email'] = "";
		$_SESSION['pass'] = "";
		@header("Location: ".HOME);
	}
	function _drop($ad)
	{
		// Проверка пользователя
		$sql_user_id = "user_id = ".$this->checkUser($_SESSION['email'])." AND";
		if($this->getAdmin()) $sql_user_id = "";

		$photos = $this->db->query("SELECT * FROM photo WHERE folder = (SELECT photos_id FROM board WHERE $sql_user_id id =  '".functions::q($ad)."')");
		if($this->db->query("DELETE FROM board WHERE $sql_user_id id = '".functions::q($ad)."'"))
		{
			$path = $_SERVER['DOCUMENT_ROOT'].'/photo/';
			while($p = mysql_fetch_assoc($photos)) {
				$folder = $path.$p['folder'];
				if(file_exists($path.$p['folder'].'/avatar.jpg')) @unlink($path.$p['folder'].'/avatar.jpg');
				$url = $path.$p['folder'].'/'.$p['name'];
			}
		}
	}
	function getProfile()
	{
		if(isset($_GET['act']) && $_GET['act'] == "remember")
		{
			$this->remember();
		}
		elseif(!$this->getUser())
		{
			$this->getEnterForm();
		}
		else
		{
			$this->getProfileContent();
		}
	}
	function getProfileMobile()
	{
		if(isset($_GET['act']) && $_GET['act'] == "remember")
		{
			$this->rememberMobile();
		}
		elseif(!$this->getUser())
		{
			$this->getEnterFormMobile();
		}
		else
		{
			$this->getProfileContentMobile();
		}
	}
	function getProfileContent()
	{

		echo "<h1 class='mh1 mbottom0'>".$this->INFO['h1']."</h1>";
		echo "<div class='left p-menu'>";
		echo "<div class='mtop11'>";
		if($this->getAdmin())
		{
			echo "<a href='profile/admin/new/' class='block pbt5 left-menu'>Новые объявления (".$this->db->result('SELECT COUNT(*) FROM board WHERE status = "new"').")</a>";
			echo "<a href='profile/admin/all/' class='block pbt5 left-menu'>Все объявления (".$this->db->result('SELECT COUNT(*) FROM board WHERE status = "ok"').")</a>";
			echo "<a href='profile/admin/wrong/' class='block pbt5 left-menu'>Запрещенные (".$this->db->result('SELECT COUNT(*) FROM board WHERE status = "wrong"').")</a>";
			echo "<a href='profile/admin/categories/' class='block pbt5 left-menu'>Категории и SEO</a>";
			echo "<a href='profile/admin/emails/' class='block pbt5 left-menu'>Настройки почты</a>";
			echo "<a href='profile/admin/information/' class='block pbt5 left-menu'>Информация</a>";
			echo "<a href='profile/admin/banners/' class='block pbt5 left-menu'>Баннера</a>";
			echo "<hr />";
		}
		echo "<a href='profile/' class='block pbt5 left-menu'>Мои объявления (".$this->db->result('SELECT COUNT(*) FROM board WHERE user_id = "'.$this->getUserId().'"').")</a>";
		#echo "<a href='profile/service/' class='block pbt5 left-menu'>Платные услуги</a>";
		echo "<a href='profile/personal/' class='block pbt5 left-menu'>Личные данные</a>";
		echo "<a href='profile/settings/' class='block pbt5 left-menu'>Настройки</a>";
		echo "<a href='profile/exit/' class='block pbt5 left-menu'>Выход</a>";
		echo "</div>";
		echo "</div>";
		echo "<div class='left profile-content'>";
		$this-> _content();
		echo "</div>";
		echo "<div class='cboth'></div>";
	}
	function getProfileContentMobile()
	{

		echo "<div class='info-header'><h3>".$this->INFO['h1']."</h3></div>";
		echo "<div class='info-navigation'>";
		echo "<ul>";
		if($this->getAdmin())
		{
			echo "<li><a href='profile/admin/new/'>Новые объявления (".$this->db->result('SELECT COUNT(*) FROM board WHERE status = "new"').")</a></li>";
			echo "<li><a href='profile/admin/all/'>Все объявления (".$this->db->result('SELECT COUNT(*) FROM board WHERE status = "ok"').")</a></li>";
			echo "<li><a href='profile/admin/wrong/'>Запрещенные (".$this->db->result('SELECT COUNT(*) FROM board WHERE status = "wrong"').")</a></li>";
			echo "<li><a href='profile/admin/categories/'>Категории и SEO</a></li>";
			echo "<li><a href='profile/admin/emails/'>Настройки почты</a></li>";
			echo "<li><a href='profile/admin/information/'>Информация</a></li>";
			echo "<li><a href='profile/admin/banners/'>Баннера</a></li>";
		}
		echo "<li><a href='profile/'>Мои объявления (".$this->db->result('SELECT COUNT(*) FROM board WHERE user_id = "'.$this->getUserId().'"').")</a></li>";
		#echo "<li><a href='profile/service/'>Платные услуги</a></li>";
		echo "<li><a href='profile/personal/'>Личные данные</a></li>";
		echo "<li><a href='profile/settings/'>Настройки</a></li>";
		echo "<li><a href='profile/exit/'>Выход</a></li>";
		echo "</ul>";
		echo "</div>";
		$this-> _contentMobile();
	}
	function _content($postfix = '')
	{
		if (isset($_GET['act'])) {
			$function = '___'.str_replace("-", "_", $_GET['act']).$postfix;

			if(method_exists($this->profile_content, $function)) {
				$this->profile_content->$function($this->getUserId());
				return 0;				
			}
		} else {
			$postfix = '___profile'.$postfix;
			$this->{$postfix}();
			return 0;
		}

		echo "Страница не найдена";

	}
	function _contentMobile()
	{
		$this->_content('Mobile');
	}
	function getBanner($id)
	{
		$b = $this->db->query("SELECT * FROM banners WHERE place_id = '".functions::q($id)."' and visible = '1' ORDER BY hits DESC LIMIT 1");
		if($this->db->getNumRows() > 0) {
			$class = "pbt5";
			if($id == 0) $class .= " bgfa bbottom-eee";
			echo "<div class='$class'>";
			$banner = $this->db->returnFirst();
			if($banner['type'] == 'adsense') {
				echo $banner['content'];
			}elseif($banner['type'] == 'sfw'){
			echo "<object type=\"application/x-shockwave-flash\" data=\"".$banner['content']."\"
			width=\"".$banner['width']."\" height=\"".$banner['width']."\">
			<param name=\"movie\" value=\"".$banner['content']."\" />
			<param name=\"wmode\" value=\"opaque\" />
			<param name='quality' value='high'>
			</object>";
			}elseif($banner['type'] == 'image'){
			echo "<a href='".$banner['link']."'><img src='".$banner['content']."' width='".$banner['width']."' height='".$banner['height']."'/></a>";
			}
			echo "</div>";
			$this->db->query("UPDATE banners SET hits = hits + 1 WHERE id = '".functions::q($banner['id'])."'");
		}
	}
	function enter()
	{
		$num = $this->db->result("SELECT COUNT(*) FROM users WHERE email='".functions::q($_POST['email'])."' and pass='".md5($_POST['pass'])."'");
		$errors = "";

		if(empty($_POST['email']))    $errors .= "Не введен e-mail!<br />";
		elseif(empty($_POST['pass'])) $errors .= "Не введен пароль!<br />";
		elseif($num == 0)             $errors .= "Пользователь не найден!<br />";

		if(!empty($errors))	$this->errors = $errors;
		else
		{
			if(isset($_POST['remember']))
			{
				setcookie ('remember', md5($_POST['email']).":".md5($_POST['pass']), time()+9999999, '/');
			}
			$_SESSION['email'] = $_POST['email'];
			$_SESSION['pass'] = md5($_POST['pass']);
			$this->setInf();
			@header("Location: ".HOME."profile/");
		}
	}
	function confirm(){
                $errors = "";
                if(empty($_GET['email'])){ echo "Отсутствует e-mail!"; return true;}
		$res = $this->db->query("SELECT activated FROM users WHERE email = '".functions::q($_GET['email'])."'");
		$active = mysql_fetch_array($res);
		if($active['activated']){ echo "<div class='green'>E-mail уже подтвержден!</div>"; return true;}
                if(empty($_GET['confirm'])){ echo "Отсутствует код подтверждения!"; return true;}
		$res = $this->db->query("SELECT confirmation FROM users WHERE email = '".functions::q($_GET['email'])."'");
		$hash = mysql_fetch_array($res);
                if($_GET['confirm'] != $hash['confirmation']) {echo "Неверный код подтверждения!"; return true;}
		if($this->db->query("UPDATE users SET activated = 1 WHERE email = '".functions::q($_GET['email'])."'")){
			echo "<div class='green'>E-mail успешно подтвержден!</div>";
                }
	}
	function register()
	{
		$errors = "";
		if(empty($_POST['email'])) $errors .= "Не введен e-mail!<br />";
		if(empty($_POST['pass']))  $errors .= "Не введен пароль!<br />";
		if($_POST['pass'] != $_POST['pass2']) $errors .= "Пароли не совпадают!<br />";
		if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))	$errors .= "Неправильный e-mail!";
		$num = $this->db->result("SELECT COUNT(*) FROM users WHERE email='".functions::q($_POST['email'])."'");
		if($num > 0) $errors .= "Пользователь с таким e-mail уже зарегистрирован!<br />";
		$time = time();
		if(!empty($errors))	$this->getRegisterForm($errors);
		else
		{
			if($this->db->query("INSERT users (email, pass, is_admin, confirmation, activated) VALUES ('".functions::q($_POST['email'])."', '".md5($_POST['pass'])."', '0', '".md5($_POST['email'] . $time)."', 0)")) {
				$replace = Array(
					"%EMAIL%" => $_POST['email'],
					"%HASH%"  => md5($_POST['email'] . $time),
					"%PASS%"  => $_POST['pass']
				);
				$this->gomail($_POST['email'], "register", $replace);
				echo "<div class='green'>ПИСЬМО С ССЫЛКОЙ АКТИВАЦИЕЙ ВАШЕГО АККАУНТА ОТПРАВЛЕНО ВАМ НА ПОЧТУ!</div>";
			}
		}
	}
	function registerMobile()
	{
		$errors = "";
		if(empty($_POST['email'])) $errors .= "Не введен e-mail!<br />";
		if(empty($_POST['pass']))  $errors .= "Не введен пароль!<br />";
		if($_POST['pass'] != $_POST['pass2']) $errors .= "Пароли не совпадают!<br />";
		if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))	$errors .= "Неправильный e-mail!";
		$num = $this->db->result("SELECT COUNT(*) FROM users WHERE email='".functions::q($_POST['email'])."'");
		if($num > 0) $errors .= "Пользователь с таким e-mail уже зарегистрирован!<br />";
		$time = time();
		if(!empty($errors))	$this->getRegisterFormMobile($errors);
		else
		{
			if($this->db->query("INSERT users (email, pass, is_admin, confirmation, activated) VALUES ('".functions::q($_POST['email'])."', '".md5($_POST['pass'])."', '0', '".md5($_POST['email'] . $time)."', 0)")) {
				$replace = Array(
					"%EMAIL%" => $_POST['email'],
					"%HASH%"  => md5($_POST['email'] . $time),
					"%PASS%"  => $_POST['pass']
				);
				$this->gomail($_POST['email'], "register", $replace);
				echo "<div class=\"alert alert-success\" role=\"alert\">ПИСЬМО С ССЫЛКОЙ АКТИВАЦИЕЙ ВАШЕГО АККАУНТА ОТПРАВЛЕНО ВАМ НА ПОЧТУ!</div>";
			}
		}
	}
	function checkUser($email)
	{
		$num = $this->db->result("SELECT COUNT(*) FROM users WHERE email = '".functions::q($email)."'");
		if($num > 0) return $this->db->result("SELECT id FROM users WHERE email = '".functions::q($email)."'");
		else {
			$pass = functions::generate_password(10);
			return $this->addUser($email, $pass);
		}
	}
	function addUser($email, $pass)
	{
		$res = $this->db->query("INSERT users (email, pass, is_admin) VALUES ('".functions::q($email)."', '".md5($pass)."', '0')");
		if($res) {
			$replace = Array(
					"%EMAIL%" => $email,
					"%PASS%"  => $pass
				);
			$this->gomail($email, "register_auto", $replace);
			return mysql_insert_id();
		} else die("Произошла неизвестная ошибка :(");
	}
	function rememberPassword($email)
	{
		$pass = functions::generate_password(8);
		$res = $this->db->query("UPDATE users SET pass = '".md5($pass)."' WHERE email = '".functions::q($email)."'");
		if($res) {
			$replace = Array(
					"%EMAIL%" => $email,
					"%PASS%"  => $pass
				);
			$this->gomail($email, "remember", $replace);
			echo "<div class='success'>Новый пароль отправлен на Ваш e-mail.</div>";
		} else die("Произошла неизвестная ошибка :(");
	}
	function gomail($email, $act, $replace)
	{
		$act = $this->db->returnFirst("SELECT * FROM emails WHERE act = '".functions::q($act)."'");
		$replace['%CONTENT%'] = $act['text'];
		$replace['%SITE%']    = $this->db->result("SELECT name FROM options WHERE link = 'index'");
		$replace['%HOST%']    = "http://".$_SERVER['HTTP_HOST']."/";
		$subject = str_replace(array_keys($replace), $replace, $act['subject']);
		$replace['%SUBJECT%'] = $subject;
		$message = str_replace(array_keys($replace), $replace, file_get_contents(DIR."/design/email.html"));
		$message = str_replace(array_keys($replace), $replace, $message);
		$headers=   "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=utf-8\r\n";
		$headers .= "From: I-Market.com.ua <help@i-market.com.ua>";
		return mail($email, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $headers);
	}
	function getInfo($page_id='index')
	{

		$replace = Array(
			'%CATEGORY%' => $this->search->CATEGORY['name'],
			'%REGION%' => !empty($this->search->R) ? $this->search->REGION['name'] : "Украина",
			'%SITE%' => "Market"
			);
		if(empty($this->INFO)) {
		if($page_id == 'search' && isset($this->search->INFO) && !empty($this->search->INFO))
		{
			$result = $this->search->INFO;
		}else{
			$result = $this->db->query("SELECT * FROM options WHERE link = '".functions::q($page_id)."'");
			if($this->db->getNumRows() > 0)
			{
				$result = $this->db->returnFirst();
			}
			else
			{
				$result = $this->db->returnFirst("SELECT * FROM options WHERE link = 'default'");
			}

		}
		}else{
			$result = $this->INFO;
		}
		if(is_array($result))
		foreach ($result as $k=>$v)
		{
			$result[$k] = str_replace(array_keys($replace), $replace, $v);
		}
		$this->INFO = $result;
	}
}
$board = new board;
?>
