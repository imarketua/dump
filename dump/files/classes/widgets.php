<?
class widgets
{
	var $db;
	var $email;
	var $pass;
	var $errors;

	var $INFO;
	function __construct()
	{
		$this->db = new dataBase();
	}
	function setInf()
	{

		$this->email = isset($_SESSION['email'])?$_SESSION['email']:false;
		$this->pass  = isset($_SESSION['pass'])?$_SESSION['pass']:false;
	}
	function getAjaxRegions()
	{
		echo "<span class='mh1 mbottom0 arial' style='font-size: 13px; color: #555555;'>Выберите регион</span>";
		$this->getCitiesAjax();
	}
	function getCitiesAjax($val="")
	{
		$i = 0;
		$root_id = empty($_GET['id']) ? '0' : functions::q($_GET['id']);
		$this->db->query("SELECT * FROM region WHERE root_id = '$root_id' ORDER BY priority DESC, name");
		echo "<div class='p5px mtop7'>";
		echo "<div class='relative h33px search-c w320px mleft2'>
		<span class='search-city'></span>
		<input class='form-input w297px find-region-input' type='text' placeholder='Начните вводить...' data-category='".functions::q($_GET['cat_href'])."'/>
		</div>";
		echo "</div>";
		echo "<table cellpadding='0' cellspacing='0' width='700' ".($root_id == 0 ? "class='pbt5'" : "").">";
		if($root_id != '0')
		{
			$url_back = "#info.php?op=regions";
			$root = $this->db->returnFirst("SELECT * FROM region WHERE id = '$root_id' LIMIT 1");
			$url = HOME.$root['href']."/".(!empty($_GET['cat_href']) ? functions::q($_GET['cat_href']).'/':'');
			echo "<div class='block bbottom-eee'>";
			echo "<div class='left p10px'>
			<a href='$url_back' class='bold'>Назад</a>";
			echo "</div>";
			echo "<div class='left p10px'>
			<a href='$url' class='bold ajax-region' data-category='".functions::q($_GET['cat_href'])."'><span class='inline sleft'></span>".$root['name']."</a>";
			echo "</div>";
			echo "<div class='cboth'></div>";
			echo "</div>";
		}
		while($r = mysql_fetch_array($this->db->data))
		{
			$url = $r['root_id'] == 0 ? "#info.php?op=regions&id=".$r['id'] : HOME.$r['href']."/".(!empty($_GET['cat_href']) ? functions::q($_GET['cat_href']).'/':'');
			$class = ($r['priority'] == '2' ? " bold": $r['priority'] == '3' ? " bold" : '').($r['root_id'] != '0' ? ' ajax-region':'');
			$i++;
			echo "<td valign='top' width='20%' class='p10px'>
			<a href='$url' class='$class'  data-category='".functions::q($_GET['cat_href'])."'>".$r['name']."</a>
			</td>";
			if($i % 4 == 0) echo "<tr />";
		}
			echo "</table>";

	}
	function changePass()
	{
		if(isset($_POST['pass']))
		{
			$errors = "";
			if($_POST['pass'] != $_POST['pass2'])
			$errors .= "Пароли не совпадают";
			elseif(empty($_POST['pass']))
			$errors .= "Не введен пароль";
			elseif(strlen($_POST['pass']) < 6)
			$errors .= "Минимум - 6 символов";
			elseif(strlen($_POST['pass']) > 20)
			$errors .= "Максимум - 20 символов";
			if(!empty($errors))
			$this->errors = $errors;
			else
			{
				$pass = $_POST['pass'];
				if($this->db->query("UPDATE users SET pass = '".md5($pass)."' WHERE id = '".$this->getUserId()."'"))
				{
					$_SESSION['pass'] = md5($pass);
					$this->setInf();
					if(isset($_COOKIE['remember']) && !empty($_COOKIE['remember']))
					setcookie ('remember', md5($this->email).":".$this->pass, time()+9999999, '/');
				}
			}
		}
	}
	function changeEmail()
	{
		if(isset($_POST['email']))
		{
			$errors = "";
			if(empty($_POST['email']))
			$errors .= "Не введен e-mail";
			elseif(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $errors .= "Неправильный e-mail!<br />";
			elseif($this->db->result("SELECT COUNT(*) FROM users WHERE email = '".functions::q($_POST['email'])."'") > 0)
			$errors .= "Такой e-mail уже зарегистрирован в базе!<br />";
			if(!empty($errors))
			$this->errors = $errors;
			else
			{
				if($this->db->query("UPDATE users SET email = '".functions::q($_POST['email'])."' WHERE id = '".$this->getUserId()."'"))
				{
					$_SESSION['email'] = functions::q($_POST['email']);
					$this->setInf();
					if(isset($_COOKIE['remember']) && !empty($_COOKIE['remember']))
					setcookie ('remember', md5($this->email).":".$this->pass, time()+9999999, '/');
				}
			}
		}
	}
	function getUser()
	{
		$num = $this->db->result("SELECT COUNT(*) FROM users WHERE email='".$this->email."' and pass='".$this->pass."'");
		if($num > 0)
		return true;
		else
		return false;
	}
	function getAdmin()
	{
		if($this->db->result("SELECT is_admin FROM users WHERE email='".$this->email."' and pass='".$this->pass."'") == '1')
		{
			return true;
		}
		else
		{
			return false;
		}

	}
	function getUserId()
	{
		return $this->db->result("SELECT id FROM users WHERE email='".$this->email."' and pass='".$this->pass."'");
	}
	function getForm($menu, $values="")
	{
		foreach ($menu as $k=>$v)
		{
			$ckeditor = Array("foot_text", "text", "info");
			if(is_array($values))
				$val = isset($values[$k])?$values[$k]:'';
			elseif($values = "")
				$val = isset($_POST[$k])?$_POST[$k]:'';
			else $val = "";

			$text = $v[0];
			echo "<div class='pbt5'>
			<div class='left item-left mtop7 tleft pleft5'>$text <span class='red'></span></div>
			<div class='left mleft10'>";

			if($v[1] == 'textarea')
				echo "<textarea class='additem-input additem-textarea ".(in_array($k, $ckeditor) ? 'ckedit' : '')."' name=\"{$k}\">{$val}</textarea>";
			elseif($v[1] == 'text' or $v[1] == 'password')
				echo "<input type='".$v[1]."' class='additem-input' value=\"{$val}\" name=\"{$k}\"/>";
			elseif($v[1] == 'hidden')
				echo "<input type='".$v[1]."' value=\"{$val}\" name=\"{$k}  \"/>";
			elseif($v[1] == 'select') {
					echo "<span class='additem-select'>";
					echo "<select name=\"$k\">";
						foreach($v[2] as $value=>$text)
						{
							echo "<option value='$value' ".($val == $value ? 'selected' : '').">$text</option>";
						}
					echo "</select>";
					echo "</span>";
					}
					echo "</div>
					<div class='cboth'></div>
					</div>";
				}
				echo "<script type='text/javascript'>
				  CKEDITOR.replaceAll('ckedit');
				   </script>";
	}
	function getFormMobile($menu, $values="")
	{
		foreach ($menu as $k=>$v)
		{
			$ckeditor = Array("foot_text", "text", "info");
			if(is_array($values))
				$val = $values[$k];
			elseif($values = "")
				$val = $_POST[$k];
			else $val = "";

			$text = $v[0];
			echo "<div class='form-group'>
			<label for='{$k}'>$text</label>";

			if($v[1] == 'textarea')
				echo "<textarea id='{$k}' class='form-control ".(in_array($k, $ckeditor) ? 'ckedit' : '')."' name=\"{$k}\">{$val}</textarea>";
			elseif($v[1] == 'text' or $v[1] == 'password')
				echo "<input type='".$v[1]."' id='{$k}' class='form-control' value=\"{$val}\" name=\"{$k}\"/>";
			elseif($v[1] == 'hidden')
				echo "<input type='".$v[1]."' id='{$k}' value=\"{$val}\" name=\"{$k}  \"/>";
			elseif($v[1] == 'select') {
					echo "<select class=\"form-control\" id='{$k}' name=\"$k\">";
						foreach($v[2] as $value=>$text)
						{
							echo "<option value='$value' ".($val == $value ? 'selected' : '').">$text</option>";
						}
					echo "</select>";
					}
					echo "
					</div>";
				}
				echo "<script type='text/javascript'>
				  CKEDITOR.replaceAll('ckedit');
				   </script>";
	}
	function getOption($array, $table, $validate, $header = "страницу", $drop=1, $add=1, $action = Array())
	{
		$def_action = Array(
			'action' => URL,
			'id' => 'id',
			'op2' => isset($_GET['op2'])?$_GET['op2']:''
		);
		if(is_array($action))
			foreach($def_action as $k=>$v) if(!isset($action[$k])) $action[$k] = $v;
		else $action = $def_action;

		if(is_array($drop))
		if(in_array($action['op2'], $drop)) $drop = 0;
		$errors = "";
		$page = !empty($action['op2']) ? $this->db->query("SELECT * FROM $table WHERE id = '".functions::q($action['op2'])."'") : "";
		if(!empty($action['op2']))
		if($this->db->getNumRows() == 0)
		{
			echo "<div class='none' align='center'>Страница удалена</div>";
			return;
		}else $page = $this->db->returnFirst();
		if($add==0 && empty($action['op2']))
		{
			echo "<div class='none' align='center'>Страница не найдена</div>";
			return;
		}
		$values = count($_POST) > 0 ? $_POST : $page;

		if(isset($_GET['op3']) && $drop == 1 && $_GET['op3'] == 'drop') {
			if(count($_POST) > 0) {
				$query = "DELETE FROM $table WHERE id = '".$action['op2']."'";
				if($this->db->query($query))
				echo "<div class='none' align='center'>Информация успешно удалена</div>";
			}else{
				echo "<h2 class='mh1 mtop11 bold pleft5'>Вы действительно хотите удалить $header?</h2>";
				echo "<form method='post' action='".$action['action']."'>";
				echo "<div class='pleft5'>
				<button type='submit' class='button' name='delete'>
				<div class='submit'>Удалить $header</div>
				</div>";
				echo "<div class='pleft5 mtop11'>
				<button type='button' class='button'>
				<div class='submit' onClick=\"location.href='".HOME."profile/admin/".$_GET['op']."/".$action['op2']."/'\">Отмена</div>
				</button></div>";
				echo "</form>";
			}
		}else{
			if(count($_POST) > 0) {
				$info = $_POST;
				foreach ($validate as $k=>$v)
					{
						$v = explode(',',$v);
						foreach($v as $v_v)
						{
							$v_v = trim($v_v);
							if($v_v != "")
							$errors .= $this->validate($k, $v_v, $array[$v_v][0], $table, $page, $info);
						}
					}
				if(empty($errors)) {
					$vals = Array("","","");
					foreach($info as $key=>$val) {
						$vals[0] .= $key.", ";
						$vals[1] .= "'".$val."', ";
						$vals[2] .= $key." = '".$val."', ";
					}
					foreach ($vals as $ks => $kv) $vals[$ks] = substr($kv, 0, strlen($kv) - 2);
					$query = isset($_GET['op2']) ? "UPDATE $table SET ".$vals[2]." WHERE id = '".$action['op2']."'":
					"INSERT $table (".$vals[0].") VALUES (".$vals[1].")";
					if($this->db->query($query)) {
						echo "<div class='success w100 p0px bnone br-none mtop0 mbottom0'>
						<div class='p10px'>".(!isset($_GET['op2']) ? "Добавление":"Изменение")." прошло успешно</div></div>";
					}
				}else echo "<div class='errors w666 p0px bnone br-none'><div class='p10px'>$errors</div></div>";
			}
			echo "<h2 class='mh1 mtop11 bold pleft5'>".(empty($action['op2']) ? "Добавить":"Изменить")." $header</h2>";
			echo "<form method='post' action='".$action['action']."'>";
			$this->getForm($array, $values);
			echo "<div class='m11px p10px'>
			<div class='left'>
			<button type='submit' class='button'>
			<div class='submit'>".(empty($action['op2']) ? "Добавить":"Изменить")." $header</div>
			</button>
			</div>";
			if(!empty($action['op2']) && $drop == 1)
			echo "<div class='left pleft5'>
			<button type='button' class='button' onClick=\"location.href='".HOME."profile/admin/".$_GET['op']."/".$action['op2']."/drop/'\">
			<div class='submit'>Удалить</div>
			</button>
			</div>";
			echo "</div>";
			echo "</form>";
		}
	}
	function getOptionMobile($array, $table, $validate, $header = "страницу", $drop=1, $add=1, $action = Array())
	{
		$def_action = Array(
			'action' => URL,
			'id' => 'id',
			'op2' => isset($_GET['op2'])?$_GET['op2']:''
		);

		if(is_array($action))
			foreach($def_action as $k=>$v) if(!isset($action[$k])) $action[$k] = $v;
		else $action = $def_action;

		if(is_array($drop))
		if(in_array($action['op2'], $drop)) $drop = 0;
		$errors = "";
		$page = !empty($action['op2']) ? $this->db->query("SELECT * FROM $table WHERE id = '".functions::q($action['op2'])."'") : "";
		if(!empty($action['op2']))
		if($this->db->getNumRows() == 0)
		{
			echo "<div class=\"alert alert-danger\" role=\"alert\">Страница удалена</div>";
			return;
		}else $page = $this->db->returnFirst();
		if($add==0 && empty($action['op2']))
		{
			echo "<div class=\"alert alert-danger\" role=\"alert\">Страница не найдена</div>";
			return;
		}
		$values = count($_POST) > 0 ? $_POST : $page;

		if(isset($_GET['op3']) && $drop == 1 && $_GET['op3'] == 'drop') {
			if(count($_POST) > 0) {
				$query = "DELETE FROM $table WHERE id = '".$action['op2']."'";
				if($this->db->query($query))
				echo "<div class=\"alert alert-success\" role=\"alert\">Информация успешно удалена</div>";
			}else{
				echo "<h4>Вы действительно хотите удалить $header?</h4>";
			  echo "<div class='container'>";
				echo "<form method='post' class='form-horizontal' action='".$action['action']."'>";
				echo "<div class='form-group'>
				<button type='submit' class='btn btn-danger' name='delete'>
				Удалить $header
				</div>";
				echo "<div class='form-group'>
				<button type='button' class='btn btn-default' onClick=\"location.href='".HOME."profile/admin/".$_GET['op']."/".$action['op2']."/'\">
				Отмена
				</button></div>";
				echo "</form>";
				echo '</div>';
			}
		}else{
			if(count($_POST) > 0) {
				$info = $_POST;
				foreach ($validate as $k=>$v)
					{
						$v = explode(',',$v);
						foreach($v as $v_v)
						{
							$v_v = trim($v_v);
							if($v_v != "")
							$errors .= $this->validate($k, $v_v, $array[$v_v][0], $table, $page, $info);
						}
					}
				if(empty($errors)) {
					$vals = Array("","","");
					foreach($info as $key=>$val) {
						$vals[0] .= $key.", ";
						$vals[1] .= "'".$val."', ";
						$vals[2] .= $key." = '".$val."', ";
					}
					foreach ($vals as $ks => $kv) $vals[$ks] = substr($kv, 0, strlen($kv) - 2);
					$query = isset($_GET['op2']) ? "UPDATE $table SET ".$vals[2]." WHERE id = '".$action['op2']."'":
					"INSERT $table (".$vals[0].") VALUES (".$vals[1].")";
					if($this->db->query($query)) {
						echo "<div class=\"alert alert-success\" role=\"alert\">".(!isset($_GET['op2']) ? "Добавление":"Изменение")." прошло успешно</div>";
					}
				}else echo "<div class=\"alert alert-danger\" role=\"alert\">$errors</div>";
			}
			echo "<h4>".(empty($action['op2']) ? "Добавить":"Изменить")." $header</h4>";
			echo "<div class='container'>";
			echo "<form method='post' class='form-horizontal' action='".$action['action']."'>";
			$this->getFormMobile($array, $values);
			echo "<div class='form-group'>
			<button type='submit' class='btn btn-success'>
			".(empty($action['op2']) ? "Добавить":"Изменить")." $header
			</button>
			</div>";
			if(!empty($action['op2']) && $drop == 1)
			echo "<div class='form-group'>
			<button type='button' class='btn btn-success' onClick=\"location.href='".HOME."profile/admin/".$_GET['op']."/".$action['op2']."/drop/'\">
			Удалить
			</button>
			</div>";
			echo "</form>";
			echo '</div>';
		}
	}

	function validate($k, $v, $name, $table, $page, $info)
	{
		$errors = "";
		if(empty($page)) $page[$v] = "";
		switch ($k)
			{
				case 'required':
				if(isset($_POST[$v]) && empty($info[$v])) $errors .= "Не заполнено обязательное поле: \"$name\"<br />";
				break;
				case 'numeric':
				if(!is_numeric($info[$v]) && !empty($info[$v]))	$errors .= "Неправильное значение: поле \"$name\" должно содержать только цифры!<br />";
				break;
				case 'exists':
				if($this->db->result("SELECT COUNT(*) FROM $table WHERE $v = '".$info[$v]."'") > 0 && ($info[$v] != $page[$v]))
				$errors .= "Такое значение $name уже существует в базе!<br />";
				break;
			}
		return $errors;
	}
	function getCategories($start = HOME, $count=4, $id=0)
	{
		$i = 0;
        $i1= 1;
		$this->db->query("SELECT * FROM categories WHERE root_id = '0'");
		$num = $this->db->getNumRows();
		while($v = mysql_fetch_assoc($this->db->data))
		{
			$i++;
            if ($i1 == 1){
                echo "<div class='inline-list-ul'>";
            }
            echo "<a href='".$start.($id == 1 ? $v['id'] : $v['href'])."/'><span class='icon-cat icon-cat-".$i."'></span>".$v['name']."</a>";
			if ($i1 == 3){
			  echo "</div>";
              $i1 = 0;
			}
            $i1++;
            /*echo "<a href='".$start.($id == 1 ? $v['id'] : $v['href'])."/' class='a-cat'>
			<div class='category left".
			($i % $count == 0 ? " bright-none" : "").
			($i > $num - $count ? " bbottom-none" : "")
			."'>
			<span class='".$v['class']."'></span>
			<span class='cat-label'>".$v['name']."</span>
			</div>
			</a>".($i > 0 && $i % $count == 0 ? "<div class='cboth'></div>" : "");*/
		}
	}
	function getCategoriesMobile($start = HOME, $count=4, $id=0)
	{
		$i = 0;
		$i1= 1;
		$this->db->query("SELECT * FROM categories WHERE root_id = '0'");
		$num = $this->db->getNumRows();
		echo '<div class="list-group">';
		while($v = mysql_fetch_assoc($this->db->data))
		{
			echo "<a href='".$start.($id == 1 ? $v['id'] : $v['href'])."/' class='list-group-item'>".$v['name']."</a>";
		}
		echo '</div>';
	}
	function getSubCategories($cat, $path=HOME)
	{
		echo "<table cellpadding='0' cellspacing='0' class='dtable' border>";
		$i = 0;
			$this->db->query("SELECT * FROM categories WHERE root_id = '".$cat."'");
			if($this->db->getNumRows() > 0)
			{
				while($r = mysql_fetch_array($this->db->data))
				{
					$url = $path.$r['id']."/";
					$i++;
					echo "<td><span class='city-name'><a href='$url'>".$r['name']."</a></span></td>";
					if($i % 2 == 0)
					echo "<tr />";
				}
				echo "<tr /><td colspan='2' class='bold'><a href='http://".HOST.$_SERVER['REDIRECT_URL']."add/'>Добавить категорию</a></td><tr />
				<td colspan='2' class='bold'><a href='http://".HOST.$_SERVER['REDIRECT_URL']."add-list/'>Добавить категоиии списком</a></td>";
			}else{
				echo "<td colspan='2'>Не найдено категорий по в данной рубрике.
				<br /><a href='http://".HOST.$_SERVER['REDIRECT_URL']."add/'>Добавить категорию</a>
				<br /><a href='http://".HOST.$_SERVER['REDIRECT_URL']."add-list/'>Добавить категоиии списком</a></td>";
			}
		echo "</table>";
	}
	function getSubCategoriesMobile($cat, $path=HOME)
	{
		echo '<div class="list-group">';
		$i == 0;
			$this->db->query("SELECT * FROM categories WHERE root_id = '".$cat."'");
			if($this->db->getNumRows() > 0)
			{
				while($r = mysql_fetch_array($this->db->data))
				{
					$url = $path.$r['id']."/";
					$i++;
					echo "<a class='list-group-item' href='$url'>".$r['name']."</a>";
				}
			}else{
				echo "<span class='list-group-item'>Не найдено категорий по в данной рубрике.</span>";
			}
			echo "<a class='list-group-item list-group-item-success' href='http://".HOST.$_SERVER['REDIRECT_URL']."add/'>Добавить категорию</a>
				  <a class='list-group-item list-group-item-success' href='http://".HOST.$_SERVER['REDIRECT_URL']."add-list/'>Добавить категоиии списком</a>";
		echo '</div>';
	}
	function getEnterFormMobile()
	{
			$errors = $this->errors;
			?>
			<section class="login">
				<div class="container">
					<?php if(!empty($errors))
					echo "<div class=\"alert alert-danger\" role=\"alert\">$errors</div>"; ?>
					<h3>Вход в личный кабинет</h3>
					<div class="row">
						<div class="register-form">
							<form method="post" action="profile/">
								<div class="form-group">
									<input type="email" placeholder="Ваш e-mail" name="email" id="plc0">
								</div>
								<div class="form-group">
									<input type="password" placeholder="Пароль" name="pass" id="plc1">
								</div>
								<div class="form-group">
									<div class="rf-left">
										<button type="submit" class="register-submit">Вход</button>
									</div>
									<div class="rf-right">
										<input type="checkbox" name="remember" id="remember">
										<label for="remember">Запомнить меня</label><a href="profile/remember/">Забыли пароль</a>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</section>
			<?
	}
	function getEnterForm()
	{
			$errors = $this->errors;
			echo "<h1 class='mh1 w352'>Вход в личный кабинет</h1>
			<form action='profile/' method='post'>
			<div class='pbt5'><input type='text' class='form-input' data-placeholder='Ваш e-mail' name='email' value='".(isset($_POST['email']) ? $_POST['email'] : '')."'/></div>
			<div class='pbt5'><input type='password' class='form-input' data-placeholder='Введите пароль' name='pass'/></div>";
			if(!empty($errors))
			echo "<div class='errors'>$errors</div>";
			echo "<div class='left'>";
			echo "<button type='submit' class='button mtop11'>
			<div class='submit'>
			Войти
			</div>
			</button>";
			echo "</div>";
			echo "<div class='left mtop15 mleft10'>";
			echo "<div class='left s-checkbox'>";
			echo "<input type='checkbox' name='remember' id='remember'/>";
			echo "<label for='remember' class='relative minus1'/>Запомнить меня</label></div>";
			echo "<div class='left s-checkbox'><span class='block mtop3'><a href='profile/remember/'>Забыли пароль?</a></span></div>";
			//echo "<a href='remember/'>Забыли пароль?</a>";
			echo "</div>";
			echo "<div class='cboth'></div>";
			echo "</form>";

	}
	function remember()
	{
		if(isset($_POST['email'])) {
				if(empty($_POST['email']))
				$this->errors .= "E-mail не введен<br />";
				if($this->db->result("SELECT COUNT(*) FROM users WHERE email = '".functions::q($_POST['email'])."'") == 0)
				$this->errors .= "E-mail не найден в базе<br />";
				if(empty($this->errors))
				{
					$this->rememberPassword($_POST['email']);
				}else{
					$this->getRememberForm();
				}
		}else{
			$this->getRememberForm();
		}
	}
	function rememberMobile()
	{
		if(isset($_POST['email'])) {
				if(empty($_POST['email']))
				$this->errors .= "E-mail не введен<br />";
				if($this->db->result("SELECT COUNT(*) FROM users WHERE email = '".functions::q($_POST['email'])."'") == 0)
				$this->errors .= "E-mail не найден в базе<br />";
				if(empty($this->errors))
				{
					$this->rememberPassword($_POST['email']);
				}else{
					$this->getRememberFormMobile();
				}
		}else{
			$this->getRememberFormMobile();
		}
	}
	function getRememberForm()
	{
				$errors = $this->errors;
				echo "<h1 class='mh1 w352'>Напоминание пароля</h1>
				<form action='".URL."' method='post'>
				<div class='pbt5'><input type='text' class='form-input' data-placeholder='Введите Ваш e-mail' name='email' value='".(isset($_POST['email']) ? $_POST['email'] : '')."'/></div>";
				if(!empty($errors))
				echo "<div class='errors'>$errors</div>";
				echo "<div class='left'>";
				echo "<button type='submit' class='button mtop11 w372px'>
				<div class='submit'>
				Отправить новый пароль на почту
				</div>
				</button>";
				echo "</div>";
				echo "<div class='cboth'></div>";
				echo "</form>";

	}
	function getRememberFormMobile()
	{
				$errors = $this->errors;
				?>
				<section class="login">
					<div class="container">
						<?php if(!empty($errors))
						echo "<div class=\"alert alert-danger\" role=\"alert\">$errors</div>"; ?>
						<h3>Напомнить пароль</h3>
						<div class="row">
							<div class="register-form">
								<form method="post" action="http://www.i-market.com.ua/profile/remember/">
									<div class="form-group">
										<input type="email" placeholder="Введите ваш e-mail" name="email" id="plc0">
									</div>
									<div class="form-group">
										<button type="submit" class="register-submit">Отправить новый пароль на почту</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</section>
				<?
	}
	function getRegisterForm($errors="")
	{
		echo "<h1 class='mh1 w352'>Регистрация на доске объявлений iMarket.ua</h1>
		<form action='register/' method='post'>
		<div class='pbt5'><input type='text' class='form-input' data-placeholder='Ваш e-mail' name='email' value='".(isset($_POST['email']) ? $_POST['email'] : '')."'/></div>
		<div class='pbt5'><input type='password' class='form-input' data-placeholder='Введите пароль' name='pass'/></div>
		<div class='pbt5'><input type='password' class='form-input' data-placeholder='Повторите пароль' name='pass2'/></div>";
		if(!empty($errors))
		echo "<div class='errors'>$errors</div>";
		echo "<button type='submit' class='button1 mtop11'>
		<div class='submit'>
		Регистрация
		</div>
		</button>
		</form>";
	}

	function getRegisterFormMobile($errors="")
	{
		?>
		<section class="register">
			<div class="container">
				<h3>Регистрация на iMarket</h3>
				<?php if(!empty($errors))
				echo "<div class=\"alert alert-danger\" role=\"alert\">$errors</div>"; ?>
				<div class="row">
					<div class="register-form">
						<form method="post" action="register/">
							<div class="form-group">
								<input type="email" placeholder="Ваш e-mail" name="email">
							</div>
							<div class="form-group">
								<input type="password" placeholder="Пароль" name="pass">
							</div>
							<div class="form-group">
								<input type="password" placeholder="Подтвердите пароль" name="pass2">
							</div>
							<button type="submit" class="register-submit">Регистрация</button>
						</form>
					</div>
				</div>
			</div>
		</section>
		<?
	}

	function getHelpForm($errors="")
	{
		echo "<h1 class='mh1 w352'>Обратная связь</h1>
		<form action='help/' method='post' onSubmit='return validate($(this));'>
		<div class='pbt5'>
		<input type='text' class='form-input' data-validate='v_required,v_email' data-placeholder='Ваш e-mail' name='email' value='".(isset($_POST['email']) ? $_POST['email'] : '')."'/></div>
		<div class='pbt5'><textarea name='text' data-validate='v_required,v_min_15' class='additem-textarea form-input' data-placeholder='Опишите предложение или проблему'></textarea>";
		if(count($_POST) > 0) {
			$errors = "";
			if(empty($_POST['email'])) $errors .= "Не введен e-mail!<br />";
			if(empty($_POST['text']))  $errors .= "Не введено сообщение!<br />";
			if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))	$errors .= "Неправильный e-mail!";
			if(!empty($errors))
			echo "<div class='errors'>$errors</div>";
			else{
				$message  = '<strong>От:</strong> <a href="mailto:'.$_POST["email"].'">'.$_POST['email'].'</a><br />';
				$message .= '<strong>Текст:</strong><br />'.$_POST['text']."<br /><br />";
				$email = "help@i-market.com.ua";
				$subject = "ТЕХПОДДЕРЖКА САЙТА";
				$headers=   "MIME-Version: 1.0\r\n";
				$headers .= "Content-type: text/html; charset=utf-8\r\n";
				$headers .= "From: I-Market.com.ua <help@i-market.com.ua>";
				if(mail($email, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $headers))
				echo "<div class='success'>Запрос успешно отправлен. Ожидайте ответа на E-mail</div>";
			}
		}
		echo "
		</div>
		<div class='pbt5'>
		<button type='submit' class='button3 mtop11'>
		<div class='submit'>
		Отправить запрос
		</div>
		</button>
		</div
		</form>";
	}
	function getHelpFormMobile($errors="")
	{
	echo '<section class="callback">
  <div class="container">';
		if(count($_POST) > 0) {
			$errors = "";
			if(!isset($_POST['email']) or empty($_POST['email'])) $errors .= "Не введен e-mail!<br />";
			if(!isset($_POST['text']) or empty($_POST['text']))  $errors .= "Не введено сообщение!<br />";
			if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))	$errors .= "Неправильный e-mail!";
			if(!empty($errors))
			echo "<div class=\"alert alert-success\" role=\"alert\">$errors</div>";
			else{
				$message  = '<strong>От:</strong> <a href="mailto:'.$_POST["email"].'">'.$_POST['email'].'</a><br />';
				$message .= '<strong>Текст:</strong><br />'.$_POST['text']."<br /><br />";
				$email = "help@i-market.com.ua";
				$subject = "ТЕХПОДДЕРЖКА САЙТА";
				$headers=   "MIME-Version: 1.0\r\n";
				$headers .= "Content-type: text/html; charset=utf-8\r\n";
				$headers .= "From: I-Market.com.ua <help@i-market.com.ua>";
				if(mail($email, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $headers))
				echo "<div class=\"alert alert-success\" role=\"alert\">Запрос успешно отправлен. Ожидайте ответа на E-mail</div>";
			}
		}
		echo '
    <h3>Обратная связь</h3>
    <div class="row">
      <div class="callback-form">';
        echo '<form action="help/" method="post" id="help-form">';
      	echo '<div class="form-group-custom">
            <input name="email" type="text" data-validate="v_required,v_email" value="'.(isset($_POST['email']) ? $_POST['email'] : '').'" placeholder="Ваш e-mail">
          </div>';
      	echo '<div class="form-group-custom">
            <textarea name="text" data-validate="v_required,v_min_15" placeholder="Опишите предложение или проблему"></textarea>
          </div>';
		echo '<button type="submit" class="submit-btn callback-submit">Отправить</button>';
		echo "</form>";
		echo '
      </div>
    </div>
  </div>
</section>';
	}
	function getCities($val="")
	{
		if(empty($val))
		{
			$i = 0;
			$this->db->query("SELECT * FROM region WHERE root_id IN(SELECT id FROM region WHERE root_id = '0' and priority IN('2','3')) ORDER BY priority DESC, name ASC LIMIT 4");
			while($r = mysql_fetch_array($this->db->data))
			{
				$url = HOME.$r['href']."/".(!empty($_GET['cat_href']) ? $_GET['cat_href'].'/':'');
				$i++;
				echo "<a href='$url' class='ajax-region'>".$r['name']."</a>";
			}
			$this->db->query("SELECT * FROM region WHERE root_id = '0' ORDER BY priority DESC, name ASC LIMIT 5");
			while($r = mysql_fetch_array($this->db->data))
			{
				$url = HOME.$r['href']."/".(!empty($_GET['cat_href']) ? $_GET['cat_href'].'/':'');
				$i++;
				echo "<a href='$url' class='ajax-region'>".$r['name']."</a>";
			}
		}else{
			$i = 0;
			$this->db->query("SELECT * FROM region WHERE name LIKE '".$val."%' ORDER BY priority DESC, name ASC LIMIT 10");
			if($this->db->getNumRows() > 0)
			{
				while($r = mysql_fetch_array($this->db->data))
				{
					$url = HOME.$r['href']."/".(!empty($_GET['cat_href']) ? $_GET['cat_href'].'/':'');
					$v = PREG_REPLACE("/(".$val.")+/iu","<b>$1</b>",$r['name']);
					$i++;
					echo "<a href='$url' class='ajax-region'>".$v."</a>";
				}
			}else{
				echo "Не найдено регионов по данному запросу";
			}
		}
	}
	function getCityBlock()
	{
		echo "<div class='choose-city'>
		<div class='p20px'>
			<span class='block relative'>
			<input type='text' class='enter-city' data-placeholder='Начните вводить...'/>
			<span class='search-city'></span>
			</span>
		</div>
		<div class='p20px ptop0px city-content'>
			<table cellpadding='0' cellspacing='0'>";
				$this->getCities();
		echo"</table>
		</div>
		</div>";
	}
}
?>
