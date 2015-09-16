<?
class profile_content extends widgets
{
	var $db;
	var $functions;
	var $search;
	var $INFO;
	var $place_id;
	private $uid;


	function __construct($uid = NULL)
	{
		$this->uid = $uid;
		$this->db = new dataBase();
		$this->functions = new functions();
		$this->search = new Search();
		$this->place_id = Array(
		0 => Array('name' => 'Баннер вверху (поиск)'),
		1 => Array('name' => 'Баннер под топами (поиск)'),
		2 => Array('name' => 'Баннер внизу (поиск)'),
		3 => Array('name' => 'Баннер справа (объявления)'),
		4 => Array('name' => 'Баннер внизу (объявления)'),
		);
	}
	function ___save(){
		$author = isset($_POST['autor'])?$_POST['autor']:'';
		$phone = isset($_POST['phone'])?$_POST['phone']:'';
		$city = isset($_POST['city'])?$_POST['city']:'';
		if($this->db->query("UPDATE users SET name = '".functions::q($author)."', phone = '".functions::q($phone)."', region = '".functions::q($city)."' WHERE id = " . $this->uid)){
			$text = "Данные успешно сохранены!";
		echo "<div class='green'>
		$text
		<span class='links'>
		<a href=''>Мой профиль</a>
		<a href=''>Поиск по сайту</a>
		<a href=''>Техподдержка</a>
		</span>
		</div>";
		}
	}
	function ___saveMobile(){
		$author = isset($_POST['autor'])?$_POST['autor']:'';
		$phone = isset($_POST['phone'])?$_POST['phone']:'';
		$city = isset($_POST['city'])?$_POST['city']:'';
		if($this->db->query("UPDATE users SET name = '".functions::q($author)."', phone = '".functions::q($phone)."', region = '".functions::q($city)."' WHERE id = " . $this->uid)){
			$text = "Данные успешно сохранены!";
		echo '<div class="alert alert-success" role="alert">'.$text.'</div>';
		echo "
		<div class=\"list-group\">
			<a class=\"list-group-item\" href='/profile/'>Мой профиль</a>
			<a class=\"list-group-item\" href='/search/'>Поиск по сайту</a>
			<a class=\"list-group-item\" href='/help/'>Техподдержка</a>
		</div>
		";
		}
	}
	function ___personal(){
		$res = $this->db->query("SELECT * from users WHERE id = " . $this->uid);
		$user = mysql_fetch_array($res);
		echo '<form method="post" action="/profile/save" onsubmit="return submit_form($(this));">
		<div class="pbt5"><div class="left item-left mtop7 tleft pleft5">Ваше имя <span class="red">*</span></div>
		<div class="left mleft10">
		<input type="text" class="additem-input" name="autor" value="'.$user['name'].'" data-validate="v_required, v_max_60"></div>
		<div class="cboth"></div></div>
		<div class="pbt5">
		<div class="left item-left mtop7 tleft pleft5">Ваш телефон <span class="red">*</span></div>
		<div class="left mleft10"><input type="text" class="additem-input" name="phone" value="'.$user['phone'].'" data-validate="v_required, v_phone"></div>
		<div class="cboth"></div>
		</div>';
		$reg['root_id'] = 0;
		if(!empty($user['region'])){
		        $res = $this->db->query("select root_id from region where href= '".$user['region']."'");
		        $reg = mysql_fetch_array($res);
		}
	        $root_region = $reg['root_id'];
	        $city = $user['region'];
	        getRegionP(0, $root_region);
	        if($root_region != 0)
	        getRegionP($root_region, $city, "Город");

		echo '<div class="m11px p10px"><button type="submit" class="button2"><div class="submit">Сохранить</div></button></div></form>';
    ?>
<script>$('#choose-region').find('select').on('change', function()
{
var html = "";
var id = $(this).find("option:selected").data('id');
$.ajax({
type: "GET",
url: 'http://'+window.location.hostname+'/ajax/info.php',
scriptCharset: 'utf-8',
data: '&reg_id='+encodeURIComponent(id),
success: function(response)
{
arr = cJSON(response);
if(!arr) alert("");
else
{
var html = "<div class='pbt5' id='city'>";
html += "<div class='left item-left mtop7 tleft pleft5'>Город <span class='red'>*</span></div>";
html += "<div class='left mleft10'>";
    html += "<span class='additem-select'>";
				html += "<select name='city' data-validate=\"v_required\">";
            html += "<option value='' disabled selected>Выбрать...</option>";
            for(var i in arr)
            {
            html += "<option value='"+i+"'>"+arr[i]+"</option>";
            }
            html += "</select></span></div>";
html += "<div style='clear: both;'></div>";
$('#city').remove();
$('#choose-region').after(html);
}
}
});
});</script>
    <?php
	}
	function ___personalMobile(){
		$res = $this->db->query("SELECT * from users WHERE id = " . $this->uid);
		$user = mysql_fetch_array($res);

		$reg['root_id'] = 0;
		if(!empty($user['region'])){
		        $res = $this->db->query("select root_id from region where href= '".$user['region']."'");
		        $reg = mysql_fetch_array($res);
		}
	        $root_region = $reg['root_id'];
	        $city = $user['region'];
	  ?>
		<section class="personal">
			<div class="container">
				<h4>Персональные данные</h4>
				<div class="row">
					<div class="add-item-form">
						<form action="/profile/save" method="post" id="add-item-form">
							<div class="form-group-custom">
								<h4>Ваше имя *</h4>
								<input type="text" name="autor" data-validate="v_required, v_max_60" placeholder="Имя" value="<?=$user['name']?>">
							</div>
							<div class="form-group-custom">
								<h4>Ваш телефон *</h4>
								<input type="text" placeholder="Телефон" name="phone" data-validate="v_required, v_phone" value="<?=$user['phone']?>">
							</div>
							<?php
							getRegionPMobile(0, $root_region);
							if($root_region != 0)
							getRegionPMobile($root_region, $city, "Город");
							?>
							<button type="submit" class="submit-btn">Сохранить</button>
						</form>
					</div>
				</div>
			</div>
		</section>
<script>$('#choose-region').find('select').on('change', function()
{
var html = "";
var id = $(this).find("option:selected").data('id');
$.ajax({
type: "GET",
url: 'http://'+window.location.hostname+'/ajax/info.php',
scriptCharset: 'utf-8',
data: '&reg_id='+encodeURIComponent(id),
success: function(response)
{
arr = cJSON(response);
if(!arr) alert("");
else
{
var html = "<div class='pbt5' id='city'>";
html += "<div class='left item-left mtop7 tleft pleft5'>Город <span class='red'>*</span></div>";
html += "<div class='left mleft10'>";
    html += "<span class='additem-select'>";
				html += "<select name='city' data-validate=\"v_required\">";
            html += "<option value='' disabled selected>Выбрать...</option>";
            for(var i in arr)
            {
            html += "<option value='"+i+"'>"+arr[i]+"</option>";
            }
            html += "</select></span></div>";
html += "<div style='clear: both;'></div>";
$('#city').remove();
$('#choose-region').after(html);
}
}
});
});</script>
    <?php
	}
	function ___service(){
                $this->search->PAGE = !empty($_GET['page']) ? $_GET['page'] : 1;
                $this->search->NUM = $this->db->result("SELECT COUNT(*) FROM board WHERE user_id = '".$this->uid."'");
                $this->search->COUNT_ADS = 10;
                $this->db->query("SELECT * FROM board WHERE status = 'ok' AND user_id = '".$this->uid."' LIMIT ".$this->search->getPageLimit().", ".$this->search->COUNT_ADS);
                while($board = mysql_fetch_assoc($this->db->data))
                {
                        echo "<table class='board-profile' cellpadding='0' cellspacing='0'>";
                        echo "<tr>";
                        echo "<td class='bbottom-t'>";
                        echo "<table cellpadding='0' cellspacing='0'>";
                        echo "<td class='td1' style='width: 100px !important;'>";
                        echo $this->search->getDate($board['time']);
                        echo "</td>";
                        echo "<td class='td2' style='width: 400px !important;'>";
                        echo "<a href='".HOME."obj/".$board['href']."/'><strong>".$board['title']."</strong></a>";
                        echo "</td>";
						echo "<td class='td4'>";
			echo "<a href='".HOME."add-item/".$board['href']."/'>Редактировать</a><br /><a href='".HOME."profile/drop/".$board['id']."/'>Удалить</a><br />";
			echo "</td>";
                        echo "<td class='td3' style='text-align: center;'>";
						echo '<div class="service-buttons"><a class="button service" href="service/top/'.$board['id'].'"><img src="img/button-top.png" class="img-button"></a>';
                        echo '<a class="button service" href="/service/color/'.$board['id'].'" style=""><img src="img/button-color.png" class="img-button"></a><a class="button service" href="/service/important/'.$board['id'].'" style=""><img src="img/button-time.png" class="img-button"></a></div>';
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
	function ___settings($id)
	{
		$user = $this->db->returnFirst("SELECT * FROM users WHERE id = '".functions::q($id)."'");
		echo "<form method='post' action='".URL."'>";
		echo "<h1 class='mh1 mtop11 pleft5 mbottom0'>Изменить e-mail</h1>";
		$this->getForm( Array(
			'email' => Array('Ваш E-mail', 'text')
		), $user);
		if(isset($_POST['email']))
		if(!empty($this->errors))
		echo "<div class='errors'>".$this->errors."</div>";
		else echo "<div class='success'>E-mail успешно изменен</div>";
		echo "<div class='m11px p10px'>
		<button type='submit' class='button2'>
		<div class='submit'>
		Изменить e-mail
		</div>
		</button>
		</div>";
		echo "</form>";
		echo "<h1 class='mh1 mtop11 pleft5 mbottom0'>Изменить пароль</h1>";
		echo "<form method='post' action='".URL."'>";
		$this->getForm( Array(
			'pass'  => Array('Пароль', 'password'),
			'pass2' => Array('Повторите пароль', 'password')
		), $_POST);
		if(isset($_POST['pass']))
		if(!empty($this->errors))
		echo "<div class='errors'>".$this->errors."</div>";
		else echo "<div class='success'>Пароль успешно изменен</div>";
		echo "<div class='m11px p10px'>
		<button type='submit' class='button2'>
		<div class='submit'>
		Изменить пароль
		</div>
		</button>
		</div>";
		echo "</form>";
	}
	function ___settingsMobile($id)
	{
		$user = $this->db->returnFirst("SELECT * FROM users WHERE id = '".functions::q($id)."'");
		?>
		<section class="settings">
		<?php
		if(isset($_POST['email']))
		if(!empty($this->errors))
		echo "<div class=\"alert alert-danger\" role=\"alert\">".$this->errors."</div>";
		else echo "<div class=\"alert alert-success\" role=\"alert\">E-mail успешно изменен</div>";
		?>
		<?php
		if(isset($_POST['pass']))
		if(!empty($this->errors))
		echo "<div class=\"alert alert-danger\" role=\"alert\">".$this->errors."</div>";
		else echo "<div class=\"alert alert-success\" role=\"alert\">Пароль успешно изменен</div>";
		?>
      <div class="container">
        <h3>Настройки</h3>
        <hr>
        <div class="row">
          <div class="settings-block">
            <h4>Изменить e-mail</h4>
            <form action="/profile/settings/" method="post">
              <div class="settings-form-group">
                <input type="text" name="email" value="<?=$user['email']?>">
              </div>
              <button type="submit" class="save">Изменить e-mail</button>
            </form>
            <hr>
          </div>
          <div class="settings-block">
            <h4>Изменить пароль</h4>
            <form action="<?=URL?>" method="post">
              <div class="settings-form-group"><span>Новый пароль</span>
                <input type="password" name="pass"><span>Подтвердите пароль</span>
                <input type="password" name="pass2">
              </div>
              <button type="submit" class="save">Изменить пароль</button>
            </form>
          </div>
        </div>
      </div>
    </section>
		<?
	}
	function ___admin($uid, $postfix = false, $back = false)
	{
        $this->setInf();

        $back = $back?$back:'<section class="item-list">
				      <div class="container">
				        <h4>Мой профиль</h4>
				        <div class="row">
				          <div class="list"></div>
				        </div>
				      </div>
				    </section>';

        if($this->getAdmin()) {
            if (isset($_GET['op']) and !empty($_GET['op'])) {
            	$function = '____' . str_replace("-", "_", $_GET['op'].$postfix);
            	if (method_exists($this, $function)) {
            		$this->$function();
            	} else {
            		$postfix = '____new'.$postfix;
            		$this->{$postfix}();
            	}
            }
            else echo "Страница не найдена";
        } else {
            echo $back;
        }
	}
	function ___adminMobile($uid)
	{
		$back = '<section class="item-list">
							<div class="container">
								<h4>Мой профиль</h4>
								<div class="row">
									<div class="list"></div>
								</div>
							</div>
						</section>';
		$this->___admin($uid, 'Mobile', $back);
	}
	function ____new()
	{
		echo "<form action='".HOME."profile/mark_messages/' method='post'>";
		echo "<h1 class='mh1 mtop11 bold pleft5 mbottom0'>
		<input type='checkbox' class='check-all' onClick='mark_all($(this))'/>Новые объявления</h1>";
		$this->search->PAGE = !empty($_GET['page']) ? $_GET['page'] : 1;
		$this->search->NUM = $this->db->result("SELECT COUNT(*) FROM board WHERE status = 'new'");
		$this->search->COUNT_ADS = 20;
		$this->_getBoard("SELECT * FROM board WHERE status = 'new' LIMIT ".$this->search->getPageLimit().", ".$this->search->COUNT_ADS);
		$this->search->getPages(HOME.'profile/admin/new/?');
		echo "</form>";
		echo "
		<script type='text/javascript'>
		function mark_all(el)
		{
			$('.board-checkbox').each(function()
			{
				if(el.is(':checked')) $(this).attr('checked', 'checked');
				else $(this).removeAttr('checked');
			});
		}
		</script>
		";
	}
	function ____newMobile()
	{
		echo "<form action='".HOME."profile/mark_messages/' method='post'>";
		echo "<h4>
		<input type='checkbox' class='check-all' onClick='mark_all($(this))'/>Новые объявления</h4>";
		$this->search->PAGE = !empty($_GET['page']) ? $_GET['page'] : 1;
		$this->search->NUM = $this->db->result("SELECT COUNT(*) FROM board WHERE status = 'new'");
		$this->search->COUNT_ADS = 20;
		$this->_getBoardMobile("SELECT * FROM board WHERE status = 'new' LIMIT ".$this->search->getPageLimit().", ".$this->search->COUNT_ADS);
		$this->search->getPagesMobile(HOME.'profile/admin/new/?');
		echo "</form>";
		echo "
		<script type='text/javascript'>
		function mark_all(el)
		{
			$('.board-checkbox').each(function()
			{
				if(el.is(':checked')) $(this).attr('checked', 'checked');
				else $(this).removeAttr('checked');
			});
		}
		</script>
		";
	}
	function ____wrong()
	{
		$this->search->PAGE = !empty($_GET['page']) ? $_GET['page'] : 1;
		$this->search->NUM = $this->db->result("SELECT COUNT(*) FROM board WHERE status = 'wrong'");
		$this->search->COUNT_ADS = 20;
		echo "<form action='".HOME."profile/mark_messages/' method='post'>
		<h1 class='mh1 mtop11 bold pleft5 mbottom0'>
		<input type='checkbox' class='check-all' />Запрещенные объявления</h1>";
		$this->_getBoard("SELECT * FROM board WHERE status = 'wrong' LIMIT ".$this->search->getPageLimit().", ".$this->search->COUNT_ADS);
		$this->search->getPages(HOME.'profile/admin/wrong/?');
		echo "</form>";
	}
	function ____wrongMobile()
	{
		$this->search->PAGE = !empty($_GET['page']) ? $_GET['page'] : 1;
		$this->search->NUM = $this->db->result("SELECT COUNT(*) FROM board WHERE status = 'wrong'");
		$this->search->COUNT_ADS = 20;
		echo "<form action='".HOME."profile/mark_messages/' method='post'>
		<h4>
		<input type='checkbox' class='check-all' />Запрещенные объявления</h4>";
		$this->_getBoardMobile("SELECT * FROM board WHERE status = 'wrong' LIMIT ".$this->search->getPageLimit().", ".$this->search->COUNT_ADS);
		$this->search->getPagesMobile(HOME.'profile/admin/wrong/?');
		echo "</form>";
	}
	function ____all()
	{
		echo "<form action='".HOME."profile/mark_messages/' method='post'>";
		echo "<h1 class='mh1 mtop11 bold pleft5 mbottom0'>
		<input type='checkbox' class='check-all' onClick='mark_all($(this))'/>Новые объявления</h1>";
		$this->search->PAGE = !empty($_GET['page']) ? $_GET['page'] : 1;
		$this->search->NUM = $this->db->result("SELECT COUNT(*) FROM board WHERE status = 'ok' ORDER BY `board`.`time` DESC ");
		$this->search->COUNT_ADS = 20;
		$this->_getBoard("SELECT * FROM board WHERE status = 'ok' ORDER BY `board`.`time` DESC LIMIT ".$this->search->getPageLimit().", ".$this->search->COUNT_ADS);
		$this->search->getPages(HOME.'profile/admin/all/?');
		echo "</form>";
		echo "
		<script type='text/javascript'>
		function mark_all(el)
		{
			$('.board-checkbox').each(function()
			{
				if(el.is(':checked')) $(this).attr('checked', 'checked');
				else $(this).removeAttr('checked');
			});
		}
		</script>
		";
	}
	function ____allMobile()
	{
		echo "<form action='".HOME."profile/mark_messages/' method='post'>";
		echo "<h4>
		<input type='checkbox' class='check-all' onClick='mark_all($(this))'/>Новые объявления</h4>";
		$this->search->PAGE = !empty($_GET['page']) ? $_GET['page'] : 1;
		$this->search->NUM = $this->db->result("SELECT COUNT(*) FROM board WHERE status = 'ok' ORDER BY `board`.`time` DESC ");
		$this->search->COUNT_ADS = 20;
		$this->_getBoardMobile("SELECT * FROM board WHERE status = 'ok' ORDER BY `board`.`time` DESC LIMIT ".$this->search->getPageLimit().", ".$this->search->COUNT_ADS);
		$this->search->getPagesMobile(HOME.'profile/admin/all/?');
		echo "</form>";
		echo "
		<script type='text/javascript'>
		function mark_all(el)
		{
			$('.board-checkbox').each(function()
			{
				if(el.is(':checked')) $(this).attr('checked', 'checked');
				else $(this).removeAttr('checked');
			});
		}
		</script>
		";
	}
	function _getBoard($query)
	{
		$b = $this->db->query($query);
		if ($this->db->getNumRows() > 0) {
		while($board = mysql_fetch_assoc($b))
		{
			echo "<table class='board-profile' cellpadding='0' cellspacing='0'>";
			echo "<tr>";
			echo "<td class='bbottom-t'>";
			echo "<table cellpadding='0' cellspacing='0'>";
			echo "<td class='td0'>";
			echo "<input type='checkbox' name='ad[".$board['id']."]' class='board-checkbox'/>";
			echo "</td>";
			echo "<td class='td1'>";
			if($board['photos_id'] != "") {
				$this->db->query("SELECT * FROM photo WHERE folder = '".functions::q($board['photos_id'])."' LIMIT 1");
				while($p = mysql_fetch_assoc($this->db->data)) {
					$url = HOME.'photo/'.$p['folder'].'/'.$p['name'];
					$size = functions::getFullSize(DIR.'/photo/'.$p['folder'].'/'.$p['name'], 80, 65);
					echo "<img src='$url' width='".$size['width']."' height='".$size['height']."' />";
				}
			}
			echo "</td>";
			echo "<td class='td2'>";

			echo "<a href='".HOME."obj/".$board['href']."/'><strong>".$board['title']."</strong></a>";
			echo "</td>";
			echo "<td class='td3'>";
			echo "<span class='grey-info'>Успешно подано!</span>";
			echo "</td>";
			echo "<td class='td4'>";
			echo "<a href='".HOME."add-item/".$board['href']."/'>Редактировать</a><br /><a href='".HOME."profile/drop/".$board['id']."/'>Удалить</a><br />";
			echo "</td>";

			echo "<tr><td style='border-top: 1px dashed #ddd;' colspan='5'>";
			echo "<div class='admin-board-text'>".$board['text']."</div>";
			echo "</td>";

			echo "</tr>";

			echo "</table>";
			echo "</td>";
			echo "</tr>";
			echo "</table>";
		}
		echo "<input type='hidden' name='op' value='ok' class='op'/>";
		echo "<input type='submit' value='Разрешить объявления' style='padding: 7px;' onClick=\"$('.op').val('ok');\"/>";
		echo "<input type='submit' value='Запретить объявления' style='padding: 7px;' onClick=\"$('.op').val('wrong');\"/>";
		echo "<input type='submit' value='Удалить объявления' style='padding: 7px;' onClick=\"$('.op').val('drop');\"/>";
		}else{
			echo "<div class='none' align='center'>Нет объявлений</div>";
		}
	}


	function getShortText($str, $maxLen){
		if ( mb_strlen( $str ) > $maxLen )
		{
			preg_match( '/^.{0,'.$maxLen.'} .*?/ui', $str, $match );
			return isset($match[0])?$match[0].'...':'';
		} else {
			return $str;
		}
	}

	function _getBoardMobile($query)
	{
		$b = $this->db->query($query);
		if ($this->db->getNumRows() > 0) {
		while($board = mysql_fetch_assoc($b))
		{
			echo "<table class='result-table'>";
			echo "<tr>";
			echo "<td>";
			echo "<table class='result-table'>";
			$class=($board['is_color'] == '1' ? " colored" : "");
			echo "<tr class='{$class}'>";
			echo "<td class='checkbox-colum'>";
			echo "<input type='checkbox' name='ad[".$board['id']."]' class='board-checkbox'/>";
			echo "</td>";
			echo "<td class='thumb'>";
			if($board['photos_id'] != "") {
				$this->db->query("SELECT * FROM photo WHERE folder = '".functions::q($board['photos_id'])."' LIMIT 1");
				while($p = mysql_fetch_assoc($this->db->data)) {
					$url = HOME.'photo/'.$p['folder'].'/'.$p['name'];
					$size = functions::getFullSize(DIR.'/photo/'.$p['folder'].'/'.$p['name'], 80, 65);
					echo "<img src='$url' width='".$size['width']."' height='".$size['height']."' />";
				}
			}
			echo "</td>";
			echo "<td class='text'>";

			echo "<a href='".HOME."obj/".$board['href']."/'><strong>".$board['title']."</strong></a>";
			echo "<div class='admin-board-text'>".$this->getShortText($board['text'], 120)."</div>";
			echo "</td>";
			echo "<td class='sales'>";
			echo "<span class='grey-info'>Успешно подано!</span>";
			echo '<div class="btn-group btn-group-sm" role="group">';
			echo "<a class='btn btn-info' href='".HOME."add-item/".$board['href']."/'><i class='glyphicon glyphicon-pencil'></i></a><a class='btn btn-danger' href='".HOME."profile/drop/".$board['id']."/'><i class='glyphicon glyphicon-trash'></i></a>";
			echo '</div>';
			echo "</td>";

			echo "</tr>";
			echo "</table>";
			echo "</td>";
			echo "</tr>";
			echo "</table>";
		}
		echo "<input type='hidden' name='op' value='ok' class='op'/>";
		echo '<div class="btn-group-vertical btn-group-sm" role="group">';
		echo "<input type='submit' value='Разрешить объявления' class='btn btn-success' onClick=\"$('.op').val('ok');\"/>";
		echo "<input type='submit' value='Запретить объявления' class='btn btn-warning' onClick=\"$('.op').val('wrong');\"/>";
		echo "<input type='submit' value='Удалить объявления' class='btn btn-danger' onClick=\"$('.op').val('drop');\"/>";
		echo '</div>';
		}else{
			echo "
			<div class=\"panel panel-default\">
			  <div class=\"panel-body\">
			    Нет объявлений
			  </div>
			</div>";
		}
	}
	function ____categories()
	{
            $sitename = $this->db->result('SELECT name FROM options WHERE id = "1"');
            if (empty($_GET['op2'])) {
                echo "<h2 class='mh1 mtop11 bold pleft5 mbottom0'>Категории</h2>";
                echo "<div class='p10px'>";
                $this->getCategories(HOME . "profile/admin/categories/", 4, 1);
                echo "</div>";
                echo "<h2 class='mh1 mtop11 bold pleft5 mbottom0'>Страницы</h2>";
                $b = $this->db->query("SELECT * FROM options");
                while ($page = mysql_fetch_assoc($b)) {
                    $title = str_replace('%SITE%', $sitename, $page['title']);
                    echo "<a href='" . HOME . "profile/admin/seo/" . $page['id'] . "/'  class='block left-menu pbt5 bbottom-eee'>" . $page['head'] . ' - ' . $title . "</a>";
                }
            } else $this->____cat($_GET['op2']);
	}
	function ____categoriesMobile()
	{
            $sitename = $this->db->result('SELECT name FROM options WHERE id = "1"');
            if (empty($_GET['op2'])) {
                echo "<h4>Категории</h4>";
                $this->getCategoriesMobile(HOME . "profile/admin/categories/", 4, 1);

                echo "<h4>Страницы</h4>";
                $b = $this->db->query("SELECT * FROM options");
                echo '<div class="list-group">';
                while ($page = mysql_fetch_assoc($b)) {
                    $title = str_replace('%SITE%', $sitename, $page['title']);
                    echo "<a href='" . HOME . "profile/admin/seo/" . $page['id'] . "/' class='list-group-item'>" . $page['head'] . ' - ' . $title . "</a>";
                }
                echo '</div>';
            } else $this->____catMobile($_GET['op2']);
	}
	function ____statistics()
	{
		echo "Статистика";
	}
	function ____cat($act, $postfix = false)
	{
		if (isset($_GET['op3'])) {
			$function = '____'.str_replace("-", "_", $_GET['op3']).$postfix;

			if(method_exists($this->profile_content, $function)) {
				$this->profile_content->$function($this->getUserId());
				return 0;				
			}
		} else {
			$postfix = '____changeCat'.$postfix;
			$this->{$postfix}($act);
			return 0;
		}

		echo "Страница не найдена";
	}
	function ____catMobile($act)
	{
		$this->____cat($act, 'Mobile');
	}
	function ____add()
	{
		echo "<h2 class='mh1 mtop11 bold pleft5'>Добавить категорию</h2>";
		$root_id = $_GET['op2'];
		if(count($_POST) > 0) {
			if($this->db->result("SELECT COUNT(*) FROM categories WHERE href = '".functions::q($_POST['href'])."'") == 0)
			{
				$keys = "";
				$val = "";
				$errors = "";
				$required = Array("name", "href", "title");
				if(!preg_match("([A-Za-z0-9_\-]+)", $_POST['href'])) $errors = "!Неправильный URL категории";
				$info = $_POST;
				$info['root_id'] = $root_id;
				foreach ($info as $k=>$v)
				{
					if(in_array($k, $required) && $v == "")
					$errors = "!Введены не все обязательные параметры";
					$keys .= $k.", ";
					$val .= "'".$v."', ";
				}
				$keys = substr($keys, 0, strlen($keys) - 2);
				$val = substr($val, 0, strlen($val) - 2);
				if(empty($errors)) {
				if($this->db->query("INSERT categories ($keys) VALUES ($val)"))
				echo "<div class='pbt5 pleft5 success'>Категория успешно добавлена</div>";
				else echo "<div class='errors pbt5 pleft5'>!Произошла ошибка</div>";
				}else echo "<div class='errors pbt5 pleft5'>$errors</div>";
			}else{
				echo "<div class='errors pbt5 pleft5'>!Категория с таким URL уже сужествует</div>";
			}
		}
		$menu = Array(
			'name'        => Array('Имя категории', 'text'),
			'title'       => Array('Тег title', 'text'),
			'h1'          => Array('Заголовок H1', 'text'),
			'href'        => Array('URL категории', 'text'),
			'description' => Array('Описание (description)', 'textarea'),
			'keywords'    => Array('Ключевые слова (keywords)', 'textarea'),
			'foot_text'    => Array('Текст внизу', 'textarea'),
			'visible'     => Array('Статус', 'select', Array('1' => 'Опубликовано', '0' => 'Не опубликовано'))
		);
		echo "<form method='post' action='".URL."'>";
		$this->getForm($menu, $_POST);
		echo "<div class='m11px p10px'>
		<button type='submit' class='button'>
		<div class='submit'>
		Добавить категорию
		</div>
		</button>
		</div>";
		echo "</form>";
	}
	function ____addMobile()
	{
		$root_id = $_GET['op2'];
		if(count($_POST) > 0) {
			if($this->db->result("SELECT COUNT(*) FROM categories WHERE href = '".functions::q($_POST['href'])."'") == 0)
			{
				$keys = "";
				$val = "";
				$errors = "";
				$required = Array("name", "href", "title");
				if(!preg_match("([A-Za-z0-9_\-]+)", $_POST['href'])) $errors = "!Неправильный URL категории";
				$info = $_POST;
				$info['root_id'] = $root_id;
				foreach ($info as $k=>$v)
				{
					if(in_array($k, $required) && $v == "")
					$errors = "Введены не все обязательные параметры";
					$keys .= $k.", ";
					$val .= "'".$v."', ";
				}
				$keys = substr($keys, 0, strlen($keys) - 2);
				$val = substr($val, 0, strlen($val) - 2);
				if(empty($errors)) {
				if($this->db->query("INSERT categories ($keys) VALUES ($val)"))
				echo "<div class=\"alert alert-success\" role=\"alert\">Категория успешно добавлена</div>";
				else echo "<div class=\"alert alert-danger\" role=\"alert\">Произошла ошибка</div>";
				}else echo "<div class=\"alert alert-danger\" role=\"alert\">$errors</div>";
			}else{
				echo "<div class=\"alert alert-danger\" role=\"alert\">Категория с таким URL уже сужествует</div>";
			}
		}
		$menu = Array(
			'name'        => Array('Имя категории', 'text'),
			'title'       => Array('Тег title', 'text'),
			'h1'          => Array('Заголовок H1', 'text'),
			'href'        => Array('URL категории', 'text'),
			'description' => Array('Описание (description)', 'textarea'),
			'keywords'    => Array('Ключевые слова (keywords)', 'textarea'),
			'foot_text'    => Array('Текст внизу', 'textarea'),
			'visible'     => Array('Статус', 'select', Array('1' => 'Опубликовано', '0' => 'Не опубликовано'))
		);
		echo "<h4>Добавить категорию</h4>";
		echo "<form method='post' action='".URL."'>";
		$this->getFormMobile($menu, $_POST);
		echo '<button type="submit" class="btn btn-success">Добавить категорию</button>';
		echo "</form>";
	}
	function ____seo()
	{
		$menu = Array(Array(
			'head'        => Array('Имя страницы', 'text'),
			'title'       => Array('Тег title', 'text'),
			'h1'          => Array('Заголовок H1', 'text'),
			'description' => Array('Описание (description)', 'textarea'),
			'keywords'    => Array('Ключевые слова (keywords)', 'textarea'),
			'foot_text'    => Array('Текст внизу', 'textarea')
			),
			"options",
			Array('required' => "name,link,title", 'exists'=>'link'),
			"правила для страницы"
		);
		if($_GET['op2'] == 1) $menu[0]['name'] = Array("Имя сайта", "text");
		elseif(!isset($_GET['op2'])) $menu[0]['link'] = Array("Псеводним", "text");

		$this->getOption($menu[0], $menu[1], $menu[2], $menu[3], 0);
	}
	function ____seoMobile()
	{
		$menu = Array(Array(
			'head'        => Array('Имя страницы', 'text'),
			'title'       => Array('Тег title', 'text'),
			'h1'          => Array('Заголовок H1', 'text'),
			'description' => Array('Описание (description)', 'textarea'),
			'keywords'    => Array('Ключевые слова (keywords)', 'textarea'),
			'foot_text'    => Array('Текст внизу', 'textarea')
			),
			"options",
			Array('required' => "name,link,title", 'exists'=>'link'),
			"правила для страницы"
		);
		if($_GET['op2'] == 1) $menu[0]['name'] = Array("Имя сайта", "text");
		elseif(!isset($_GET['op2'])) $menu[0]['link'] = Array("Псеводним", "text");

		$this->getOptionMobile($menu[0], $menu[1], $menu[2], $menu[3], 0);
	}
	function ____coords()
	{
		if(isset($_POST['region']))
		{
			foreach ($_POST['region'] as $k=>$v)
			{
				$this->db->query("UPDATE region SET coords = '".functions::q($v)."' WHERE id = '".functions::q($k)."'");
			}
		}
		$this->db->query("SELECT * FROM region WHERE root_id = '0'");
		$array = Array();
		$values = Array();
		while($r = mysql_fetch_assoc($this->db->data))
		{
			$array['region['.$r['id'].']'] = Array($r['name'], "text");
			$values['region['.$r['id'].']'] = $r['coords'];
		}
		echo "<form method='post' action='".URL."'>";
		echo "<h2 class='mh1 mtop11 bold pleft5 mbottom0'>Координаты областей</h2>";
		$this->getForm($array, $values);
		echo "<div class='pleft5 mtop11'>
		<button type='submit' class='button'>
		<div class='submit'>Изменить</div>
		</button></div>";
		echo "</form>";
	}
	function ____add_banner()
	{
		$selects = Array();
			foreach ($this->place_id as $k=>$v) $selects[0][$k] = $v['name'];
			$selects[1] = Array(
			'adsense' => 'AdSense',
			'swf' => "Файл '.swf'",
			'image' => 'Графический файл(gif, png, jpg и др.)'
			);
			$selects[2] = Array(
			'0' => 'Отключен',
			'1' => "Включен"
			);
		$menu = Array(Array(
			'title'       => Array('Имя баннера', 'text'),
			'place_id'    => Array('Место', 'select', $selects[0]),
			'link'        => Array('Ссылка', 'text'),
			'type'        => Array('Тип баннера', 'select', $selects[1]),
			'width'       => Array('Ширина', 'text'),
			'height'      => Array('Высота', 'text'),
			'visible'     => Array('Статус', 'select', $selects[2]),
			'content'     => Array('Путь к файлу или код adsense', 'textarea')
			), "banners",  Array('required' => "title,content", 'numeric' => 'width, height', 'exists'=>'title'), "Баннер"
		);
		$validate = Array('required' => "title,content", 'numeric' => 'width, height');
		$this->getOption($menu[0], $menu[1], $menu[2], $menu[3]);
	}
	function ____add_bannerMobile()
	{
		$selects = Array();
			foreach ($this->place_id as $k=>$v) $selects[0][$k] = $v['name'];
			$selects[1] = Array(
			'adsense' => 'AdSense',
			'swf' => "Файл '.swf'",
			'image' => 'Графический файл(gif, png, jpg и др.)'
			);
			$selects[2] = Array(
			'0' => 'Отключен',
			'1' => "Включен"
			);
		$menu = Array(Array(
			'title'       => Array('Имя баннера', 'text'),
			'place_id'    => Array('Место', 'select', $selects[0]),
			'link'        => Array('Ссылка', 'text'),
			'type'        => Array('Тип баннера', 'select', $selects[1]),
			'width'       => Array('Ширина', 'text'),
			'height'      => Array('Высота', 'text'),
			'visible'     => Array('Статус', 'select', $selects[2]),
			'content'     => Array('Путь к файлу или код adsense', 'textarea')
			), "banners",  Array('required' => "title,content", 'numeric' => 'width, height', 'exists'=>'title'), "Баннер"
		);
		$validate = Array('required' => "title,content", 'numeric' => 'width, height');
		$this->getOptionMobile($menu[0], $menu[1], $menu[2], $menu[3]);
	}
	function ____banners()
	{
            echo "<h2 class='mh1 mtop11 bold pleft5 mbottom0'>Баннера</h2>";
            $b = $this->db->query("SELECT * FROM banners");
            while ($page = mysql_fetch_assoc($b)) {
                echo "<table class='board-profile' cellpadding='0' cellspacing='0'>";
                echo "<tr>";
                echo "<td class=''>";
                echo "<table cellpadding='0' cellspacing='0' class='bbottom-eee'>";
                echo "<td class='td3 bright-eee'>Показов: <strong>";
                echo $page['hits'];
                echo "</strong></td>";
                echo "<td class='' style='padding-left: 10px;'>";
                echo "<a href='" . HOME . "profile/admin/add_banner/" . $page['id'] . "/'><strong>" . $page['title'] . "</strong></a>";
                echo "</td>";
                echo "</table>";
                echo "</td>";
                echo "</tr>";
                echo "</table>";
            }
            echo "<a href='" . HOME . "profile/admin/add_banner/' class='block left-menu pbt5 bold'>Добавить баннер</a>";
	}
	function ____bannersMobile()
	{
            echo "<h4>Баннера</h4>";
            $b = $this->db->query("SELECT * FROM banners");
            while ($page = mysql_fetch_assoc($b)) {
                echo "<table class='result-table'>";
                echo "<tr>";
                echo "<td class=''>";
                echo "<table class='result-table'>";
                echo "<td class='date'>Показов: <strong>";
                echo $page['hits'];
                echo "</strong></td>";
                echo "<td class='text'>";
                echo "<a href='" . HOME . "profile/admin/add_banner/" . $page['id'] . "/'><strong>" . $page['title'] . "</strong></a>";
                echo "</td>";
                echo "</table>";
                echo "</td>";
                echo "</tr>";
                echo "</table>";
            }
            echo "<a href='" . HOME . "profile/admin/add_banner/' class='btn btn-success'>Добавить баннер</a>";
	}
	function ____emails()
	{
		echo "<h2 class='mh1 mtop11 bold pleft5 mbottom0'>Настройки почты</h2>";
		$b = $this->db->query("SELECT * FROM emails");
			while($page = mysql_fetch_assoc($b))
			{
				echo "<a href='".HOME."profile/admin/add_email/".$page['id']."/' class='block left-menu pbt5 bbottom-eee'>".$page['name']."</a>";
			}
		echo "<a href='".HOME."profile/admin/add_email/'  class='block left-menu pbt5 bold'>Добавить почту</a>";
	}
	function ____emailsMobile()
	{
		echo "<h4>Настройки почты</h4>";
		$b = $this->db->query("SELECT * FROM emails");
		echo '<div class="list-group">';
			while($page = mysql_fetch_assoc($b))
			{
				echo "<a href='".HOME."profile/admin/add_email/".$page['id']."/' class='list-group-item'>".$page['name']."</a>";
			}
			echo "<a href='".HOME."profile_contentile/admin/add_email/'  class='list-group-item list-group-item-success'>Добавить почту</a>";
		echo '</div>';
	}
	function ____add_email()
	{
		$menu = Array(Array(
			'name'        => Array('Имя рассылки', 'text'),
			'subject'     => Array('Тема', 'text'),
			'act'         => Array('Имя операции', 'text'),
			'text'        => Array('Текст почты', 'textarea'),
			'info'        => Array('Информация', 'textarea')
			),
			"emails",
			Array('required' => "name,act", 'exists'=>'act'),
			"текст почты"
		);
		$this->getOption($menu[0], $menu[1], $menu[2], $menu[3], 1, 1);
	}
	function ____add_emailMobile()
	{
		$menu = Array(Array(
			'name'        => Array('Имя рассылки', 'text'),
			'subject'     => Array('Тема', 'text'),
			'act'         => Array('Имя операции', 'text'),
			'text'        => Array('Текст почты', 'textarea'),
			'info'        => Array('Информация', 'textarea')
			),
			"emails",
			Array('required' => "name,act", 'exists'=>'act'),
			"текст почты"
		);
		$this->getOptionMobile($menu[0], $menu[1], $menu[2], $menu[3], 1, 1);
	}
	function ____information()
	{
		echo "<h2 class='mh1 mtop11 bold pleft5 mbottom0'>Информационные страницы</h2>";
		$b = $this->db->query("SELECT * FROM pages");
			while($page = mysql_fetch_assoc($b))
			{
				echo "<a href='".HOME."profile/admin/add_page/".$page['id']."/' class='block left-menu pbt5 bbottom-eee'>".$page['name']."</a>";
			}
		echo "<a href='".HOME."profile/admin/add_page/' class='pbt5 block left-menu pbt5 bold'>Добавить страницу</a>";
	}
	function ____informationMobile()
	{
		echo "<h4>Информационные страницы</h4>";
		$b = $this->db->query("SELECT * FROM pages");
		echo '<div class="list-group">';
			while($page = mysql_fetch_assoc($b))
			{
				echo "<a href='".HOME."profile/admin/add_page/".$page['id']."/' class='list-group-item'>".$page['name']."</a>";
			}
		echo '</div>';
		echo "<a href='".HOME."profile/admin/add_page/' class='btn btn-success'>Добавить страницу</a>";
	}
		function ____add_page()
	{
		$menu = Array(Array(
			'href'         => Array('URL Страницы', 'text'),
			'name'         => Array('Название в меню', 'text'),
			'title'        => Array('Название страницы(title)', 'text'),
			'h1'           => Array('Заголовок страницы (title)', 'text'),
			'text'         => Array('Содержимое страницы', 'textarea'),
			'description'  => Array('Описание страницы(description)', 'textarea'),
			'keywords'     => Array('Ключевые слова(keywords)', 'textarea')
			),
			"pages",
			Array('required' => "href, name, title", 'exists'=>'href'),
			"информационную страницу"
		);
		$this->getOption($menu[0], $menu[1], $menu[2], $menu[3], 1, 1);
	}
	function ____add_pageMobile()
	{
		$menu = Array(Array(
			'href'         => Array('URL Страницы', 'text'),
			'name'         => Array('Название в меню', 'text'),
			'title'        => Array('Название страницы(title)', 'text'),
			'h1'           => Array('Заголовок страницы (title)', 'text'),
			'text'         => Array('Содержимое страницы', 'textarea'),
			'description'  => Array('Описание страницы(description)', 'textarea'),
			'keywords'     => Array('Ключевые слова(keywords)', 'textarea')
			),
			"pages",
			Array('required' => "href, name, title", 'exists'=>'href'),
			"информационную страницу"
		);
		$this->getOptionMobile($menu[0], $menu[1], $menu[2], $menu[3], 1, 1);
	}
	function ____add_list()
	{
		$cat = $this->db->result("SELECT id FROM categories WHERE href = '".functions::q($_GET['op2'])."'");

		echo "<h2 class='mh1 mtop11 bold pleft5'>Добавить категории</h2>";
		echo "<form method='post' action='http:/нов/".HOST.$_SERVER['REDIRECT_URL']."'>";
		$menu = Array('cats' => Array('Категории (с новой строки)', 'textarea'));
		$this->getForm($menu);
		if(isset($_POST['cats']))
		{
			$cats = $_POST['cats'];
			$array = explode('<br />', nl2br($cats));
			$err = 0;

			foreach($array as $k=>$v)
			{
				$href = $this->functions->translit($v);
				if($v != '' and $this->db->result("SELECT COUNT(*) FROM categories WHERE href = '".functions::q($href)."'") == 0)
				if(!$this->db->query("INSERT categories (root_id, name, href, title, h1, description, visible, class)
				VALUES ('".$cat."', '".$v."', '".$href."', '', '', '', '1', '')")) $err++;
			}

			if($err == 0) echo "<div>Категории добавлены</div>";
			else echo "<div>Ошибка при добавлении категорий</div>";
		}
		echo "<div class='m11px p10px'>
		<button type='submit' class='button'>
		<div class='submit'>
		Добавить категории
		</div>
		</button>
		</div>";
		echo "</form>";
	}
	function ____add_listMobile()
	{
		$cat = $this->db->result("SELECT id FROM categories WHERE href = '".functions::q($_GET['op2'])."'");

		echo "<h4>Добавить категории</h4>";
		echo "<form method='post' action='http:/нов/".HOST.$_SERVER['REDIRECT_URL']."'>";
		$menu = Array('cats' => Array('Категории (с новой строки)', 'textarea'));
		if(isset($_POST['cats']))
		{
			$cats = $_POST['cats'];
			$array = explode('<br />', nl2br($cats));
			$err = 0;

			foreach($array as $k=>$v)
			{
				$href = $this->functions->translit($v);
				if($v != '' and $this->db->result("SELECT COUNT(*) FROM categories WHERE href = '".functions::q($href)."'") == 0)
				if(!$this->db->query("INSERT categories (root_id, name, href, title, h1, description, visible, class)
				VALUES ('".$cat."', '".$v."', '".$href."', '', '', '', '1', '')")) $err++;
			}

			if($err == 0) echo "<div class=\"alert alert-danger\" role=\"alert\">Категории добавлены</div>";
			else echo "<div class=\"alert alert-danger\" role=\"alert\">Ошибка при добавлении категорий</div>";
		}
		$this->getFormMobile($menu);
		echo '<button type="submit" class="btn btn-success">Добавить категории</button>';
		echo "</form>";
	}
	function ____changeCat($cat)
	{
		$info = "";
		$errors = "";
		$text = isset($text)?$text:'';
		$required = Array("name", "href", "title");
		$values = '';
		if(count($_POST) > 0)
		{
			foreach($_POST as $k=>$v) {
			if(in_array($k, $required) && $v == "") $errors = "<div class='errors'>!Не заполнены обязательные поля</div>";
				$values .= "$k = '".functions::q($v)."', ";
			}
			$values = substr($values, 0, strlen($values) - 2);

			if(!preg_match("([A-Za-z0-9_\-]+)", $_POST['href']))
			$info .= "<div class='errors'>!Неправильный URL категории</div>";
			elseif(!empty($errors))
			$info .= $errors;
			elseif($this->db->result("SELECT COUNT(*) FROM categories WHERE href = '".functions::q($_POST['href'])."'") > 1)
			$info .= "<div class='errors'>Категория с таким URL уже существует</div>";
			elseif($this->db->query("UPDATE categories SET $values WHERE id = '".functions::q($cat)."'"))
			$info .= "<div class='pbt5 pleft5 success'>Категория успешно изменена</div>";
			else $info .= "<div>Произошла ошибка</div>";
		}
		echo "<h2 class='mh1 mtop11 bold pleft5'>Изменить категорию</h2>";
		echo $info;
		$this->db->query("SELECT * FROM categories WHERE id = '".functions::q($cat)."'");
		if($this->db->getNumRows() > 0)
		{
			echo "<form method='post' action='http://".HOST."/".$_SERVER['REDIRECT_URL']."'>";
			while($cat = mysql_fetch_assoc($this->db->data))
			{
				$menu = Array(
					'name'        => Array('Имя категории', 'text'),
					'title'       => Array('Тег title', 'text'),
					'h1'          => Array('Заголовок H1', 'text'),
					'href'        => Array('URL категории', 'text'),
					'description' => Array('Описание (description)', 'textarea'),
					'keywords' 	  => Array('Ключивые слова (keywords)', 'textarea'),
					'foot_text'	  => Array('Текст внизу', 'textarea'),
					'root_id'     => Array('', 'hidden'),
					'visible'     => Array('Статус', 'select', Array('1' => 'Опубликовано', '0' => 'Не опубликовано'))
				);
				$this->getForm($menu, $cat);
				if($cat['root_id'] == 0) {
					echo "<div class='pbt5'>
					<div class='left item-left mtop7 tleft pleft5'>$text <span class='red'></span></div>
					<div class='left mleft10'>";
					$this->getSubCategories($cat['id'], HOME."profile/admin/categories/");
					echo "</div>
					<div class='cboth'></div>
					</div>";
				}
				echo "<div class='m11px p10px'>
				<button type='submit' class='button'>
				<div class='submit'>
				Изменить категорию
				</div>
				</button>
				</div>";
			}
			echo "</form>";
		}
		else echo "Категория не найдена";
	}

function ____changeCatMobile($cat)
	{
		$info = "";
		$errors = "";
		$required = Array("name", "href", "title");
		if(count($_POST) > 0)
		{
			foreach($_POST as $k=>$v) {
			if(in_array($k, $required) && $v == "") $errors = "<div class=\"alert alert-danger\" role=\"alert\">Не заполнены обязательные поля</div>";
				$values .= "$k = '".functions::q($v)."', ";
			}
			$values = substr($values, 0, strlen($values) - 2);

			if(!preg_match("([A-Za-z0-9_\-]+)", $_POST['href']))
			$info .= "<div class=\"alert alert-danger\" role=\"alert\">Неправильный URL категории</div>";
			elseif(!empty($errors))
			$info .= $errors;
			elseif($this->db->result("SELECT COUNT(*) FROM categories WHERE href = '".functions::q($_POST['href'])."'") > 1)
			$info .= "<div class=\"alert alert-danger\" role=\"alert\">Категория с таким URL уже существует</div>";
			elseif($this->db->query("UPDATE categories SET $values WHERE id = '".functions::q($cat)."'"))
			$info .= "<div class=\"alert alert-success\" role=\"alert\">Категория успешно изменена</div>";
			else $info .= "<div class=\"alert alert-danger\" role=\"alert\">Произошла ошибка</div>";
		}
		echo $info;
		echo "<h4>Изменить категорию</h4>";
		$this->db->query("SELECT * FROM categories WHERE id = '".functions::q($cat)."'");
		if($this->db->getNumRows() > 0)
		{
			echo "<form method='post' action='http://".HOST."/".$_SERVER['REDIRECT_URL']."'>";
			while($cat = mysql_fetch_assoc($this->db->data))
			{
				$menu = Array(
					'name'        => Array('Имя категории', 'text'),
					'title'       => Array('Тег title', 'text'),
					'h1'          => Array('Заголовок H1', 'text'),
					'href'        => Array('URL категории', 'text'),
					'description' => Array('Описание (description)', 'textarea'),
					'keywords' 	  => Array('Ключивые слова (keywords)', 'textarea'),
					'foot_text'	  => Array('Текст внизу', 'textarea'),
					'root_id'     => Array('', 'hidden'),
					'visible'     => Array('Статус', 'select', Array('1' => 'Опубликовано', '0' => 'Не опубликовано'))
				);
				$this->getFormMobile($menu, $cat);
				if($cat['root_id'] == 0) {
					$this->getSubCategoriesMobile($cat['id'], HOME."profile/admin/categories/");
				}

				echo '<button type="submit" class="btn btn-success">Изменить категорию</button>';
			}
			echo "</form>";
		}
		else echo "Категория не найдена";
	}
}
function getRegionP($root_id = 0, $selected = 0, $txt = "Регион")
{
        $db = new dataBase();
        echo "<div class='pbt5' ".($root_id == 0 ? "id='choose-region'":"id='city'").">
        <div class='left item-left mtop7 tleft pleft5'>$txt <span class='red'>*</span></div>
        <div class='left mleft10' >";
        echo "<span class='additem-select'>";
        echo "<select ".($root_id != 0 ? "name='city'":"")." data-validate=\"v_required\">
        <option value='' disabled ".($selected == 0 ? "selected" : "").">Выбрать...</option>";
        $db->query("SELECT * FROM region WHERE root_id = '$root_id'");
        while($c = mysql_fetch_array($db->data))
        {
                echo "<option value='".$c['href']."' data-id='".$c['id']."' ".(($selected === $c['id'] or $selected === $c['href']) ? "selected" : "").">".$c['name']."</option>";
        }
        echo "</select>
        </span>";
        echo "</div>
        <div class='cboth'></div>
        </div>";
}
function getRegionPMobile($root_id = 0, $selected = 0, $txt = "Регион")
{
        $db = new dataBase();
        echo "<div class='form-group-custom' ".($root_id == 0 ? "id='choose-region'":"id='city'").">
				<h4>$txt *</h4>";
        echo "<select ".($root_id != 0 ? "name='city'":"")." class=\"region\" data-validate=\"v_required\">
        <option value='' disabled ".($selected == 0 ? "selected" : "").">Выбрать...</option>";
        $db->query("SELECT * FROM region WHERE root_id = '$root_id'");
        while($c = mysql_fetch_array($db->data))
        {
                echo "<option value='".$c['href']."' data-id='".$c['id']."' ".(($selected === $c['id'] or $selected === $c['href']) ? "selected" : "").">".$c['name']."</option>";
        }
        echo "</select>";
        echo "</div>";
}
?>
