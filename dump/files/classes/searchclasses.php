<?php
class Search
{
/**************************************************** Параметры *************************************************/

public $q;
public $COUNT_ADS = 30;
public $NUM;
public $PAGE = 1;
public $DESCRIPTION;
public $KEYWORDS;
public $FOOT_TEXT;
public $TITLE;
public $USER;
protected $SQL_QUERY;
protected $COUNT_PAGES;
protected $FAV;
protected $NUMBER;
private $f;

public $CAT_HREF;
public $R;
var $REGION;
var $CATEGORY;
var $db;



/****************************************************  Функции  *************************************************/


public function __construct ()
{
$this->widgets = new widgets();
$this->db = new DataBase();
if(!defined(CATEGORY))
$this->CAT_HREF = CATEGORY;
if(!defined(REGION))
$this->R = REGION;
$this->setInf();

if(!isset($_GET['page'])) $this -> PAGE = 1;
else $this -> PAGE = intval($_GET['page']);

$this -> setTitleInfo();
/*
if(isset($_GET['q']) && !empty($_GET['q']) && trim($_GET['q']) != "")
	{
		$n = $this->result("SELECT COUNT(*) FROM search WHERE q = '".$_GET['q']."'", 0);

		if($n > 0)
			{
				$this -> query("UPDATE search SET count = count + 1 WHERE q = '".$_GET['q']."'");
			}
		else
			{
				$this -> query("INSERT search (q, count) VALUES ('".$_GET['q']."','1')");
			}
	}
*/
}
function setInf()
{
	if(!empty($this->CAT_HREF))
	$this->CATEGORY = mysql_fetch_assoc($this->db->query("SELECT * FROM categories WHERE href='".functions::q($this->CAT_HREF)."' LIMIT 1"));
	if(!empty($this->R))
	$this->REGION = mysql_fetch_assoc($this->db->query("SELECT * FROM region WHERE href = '".functions::q($this->R)."' LIMIT 1"));
}
function getBoardTypeFilter()
{
	$arr = Array(
		'' => "Все",
		'S' => "Частные лица",
		'B' => "Бизнес"
	);

	echo "<ul class='bottom-pages liststyle-none right mright5'>";
	$i = 0;
	foreach ($arr as $k=>$v)
	{
		$link = $this->getFullUrl('type').($k != "" ? "type=$k" : "");
		$link = $k=="" ? substr($link, 0, strlen($link) - 1) : $link;
		$tag = isset($_GET['type'])?($_GET['type'] == $k  ? "i" : "a"):'a';
		$class = isset($_GET['type'])?($_GET['type'] == $k ? "page-active" : ""):'';
		$class .= ($i < count($arr)-1? " bright-none" : "");
		$class .= $i > 0 ? " brleft-none" : "";
		echo "<li><$tag href='$link' class='$class'>$v</$tag></li>";
		$i++;
	}
	echo "</ul>";
	$type = isset($_GET['type'])?$_GET['type']:'';
	echo "<input type='hidden' name='type' value='".$type."'>";
}
function getBoardTypeFilterMobile()
{
	$arr = Array(
		'' => "Все",
		'S' => "Частные лица",
		'B' => "Бизнес"
	);

	echo '<div class="seller-type"><div role="group" class="btn-group btn-group-large">';
	$i = 0;
	foreach ($arr as $k=>$v)
	{
		$link = $this->getFullUrl('type').($k != "" ? "type=$k" : "");
		$link = $k=="" ? substr($link, 0, strlen($link) - 1) : $link;
		$class = isset($_GET['type'])?($_GET['type'] == $k ? "page-active" : ""):'';
		echo "<a href='$link' class='btn btn-default$class'>$v</a>";
		$i++;
	}
	echo "</div></div>";
	$type = isset($_GET['type'])?$_GET['type']:'';
	echo "<input type='hidden' name='type' value='".$type."'>";
}
function getFilters()
{

	echo "<div class='left'>";
    echo "<form action='http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."' method='GET'>";
	echo "<span class='additem-select w130 left'>";
	echo "<select id='search-category' onChange='f_reload(this)'>";
	$top_all = isset($_GET['top_all']) ? '?top_all' : '' ;
	echo "<option data-href='".HOME."search/$top_all' ".(empty($this->CAT_HREF) ? 'selected' : '').">Все категории</option>";
	$this->db->query("SELECT * FROM categories WHERE root_id = '0' ORDER BY name ASC");
	while($c = mysql_fetch_array($this->db->data))
	{
		echo "<option data-href='".HOME.(!empty($this->R) ? $this->R.'/' : '').$c['href']."/$top_all' ".
		(($this->CAT_HREF == $c['href'] or $this->CATEGORY['root_id'] == $c['id']) ? 'selected' : '').">".$c['name']."</option>";
	}
	echo "</select>
	</span>";
	$price_min = isset($_GET['price_min'])?$_GET['price_min']:'';
	echo "<div class='left mleft10'><input type='text' class='enter-input'
	data-placeholder='Цена, от' name='price_min'  value='".$price_min."' data-pos=\"bottom\" data-info=\"Нажмите на кнопку поиска<br /> чтобы применить фильтры!\"/>
	</div>";
	$price_max = isset($_GET['price_max'])?$_GET['price_max']:'';
	echo "<div class='left mleft10'>
	<input type='text' class='enter-input' data-placeholder='Цена, до' name='price_max'  value='".$price_max."'
	data-pos=\"bottom\"  data-info=\"Нажмите на кнопку поиска<br /> чтобы применить фильтры!\"/>
	</div>";
	echo "</div>";
	$intext = isset($_GET['intext'])?($_GET['intext'] == 'on' ? ' checked':''):'';
	echo "<div class='left s-checkbox'>
	<input type='checkbox' name='intext' id='intext' name='intext'".$intext."/><label for='intext'>Искать в тексте</label>
	</div>";
	$priceonly = isset($_GET['priceonly'])?($_GET['priceonly'] == 'on' ? ' checked':''):'';
	echo "<div class='left s-checkbox'>
	<input type='checkbox' name='priceonly' id='priceonly' name='priceonly'".$priceonly."/><label for='priceonly'>Только с ценой</label>
	</div>";
    echo "<button style='margin: 5px 0 0 20px;'>Поиск</button>";
    echo "</form>";
	echo "<div class='cboth'></div>";

	$url = HOME.(!empty($this->R) ? $this->REGION['href']."/" : "");
	if(!empty($this->CAT_HREF) && !$this->CATEGORY['root_id'] != "0") {
		echo "<div class='top-filters' align='left'>";
		$this->getSubCats($url);
		echo "</div>";
	}

	echo "<div class='cboth'></div>
	<div class='cboth'></div>";

}
function getBread()
{
	if(!empty($this->CAT_HREF) or !empty($this->R)) {
	echo "<div class='bread' align='left'>";
	echo "<a href='".HOME."search/'>Все объявления</a>";
	if(!empty($this->R)){
	if(!$this->REGION['root_id'] != 0)
	$this->getBreadLink(HOME.$this->REGION['href']."/", $this->REGION['name']);
	else {
	$root = $this->db->returnFirst("SELECT * FROM region WHERE id = '".$this->REGION['root_id']."'");
	$this->getBreadLink(HOME.$root['href']."/", $root['name']);
	$this->getBreadLink(HOME.$this->REGION['href']."/", $this->REGION['name']);
	}
	}
	if(!empty($this->CAT_HREF)) {
		$url = !empty($this->R) ? HOME.$this->REGION['href'].'/' : HOME;
		if($this->CATEGORY['root_id'] == '0')
		$this->getBreadLink(HOME.$this->CATEGORY['href']."/", $this->CATEGORY['name']);
		else {
		$root = $this->db->returnFirst("SELECT * FROM categories WHERE id = '".functions::q($this->CATEGORY['root_id'])."'");
		$this->getBreadLink($url.$root['href']."/", $root['name']);
		$this->getBreadLink($url.$this->CATEGORY['href']."/", $this->CATEGORY['name']);
		}
	}
	echo "</div>";
	}
}
function getBreadLink($url, $name)
{
	$name = trim($name);
	echo "<span class='inline sleft msl'></span><a href='$url'>$name</a>";
}
function getSubCats($url)
{
	$cats = $this->db->query("SELECT * FROM categories WHERE root_id = '".functions::q($this->CATEGORY['id'])."'");
	echo "<table cellpadding='0' cellspacing='0' class='subcategories'>";
	$i = 0;
	$top = isset($_GET['top_all']) ? '?top_all' : '' ;
	while($cat = mysql_fetch_assoc($cats))
	{
		$i++;
		echo "<td><a href='".$url.$cat['href']."/$top'>".$cat['name']."</a></td>";
		echo $i % 4 == 0 && $i > 0 ? "<tr />" : "";
	}
	echo "</table>";
}
public function getTitleInfo()
	{

	}
public function getBoard ($op='search') // ******** Функция показа объявлений ***
	{
		$this->getQuery($op);
		$this->getResultsInfo();
		$query = $this -> SQL_QUERY;
		if($this -> NUM > 0)
		{
			$ii = 0;
			$b = array();
                        while($board = mysql_fetch_assoc($query))
                                {
                                        $b[] = $board;
					$ii++;
                                }
			if($op == "otherboard")
			{
				echo "<div class='p10px'>";
				echo "<h2 class='mh1 arial bright-none btop-none mleft0 c555'>Похожие объявления. Найдено ".$ii." объявлений.</h2>";
				echo "</div>";
			}
			elseif($op == "search")
			{
				echo "<div class='p10px'>";
				echo "<h2 class='mh1 arial bbottom-none bright-none btop-none mbottom0 mleft0 p0px c555' align='left'>Все объявления. Найдено ".$this -> NUM." объявлений.</h2>";
				echo "</div>";
			}
                        elseif($op == "top4")
                        {

                                echo "<div class='p10px'>";
                                echo "<h2 class='mh1 arial bbottom-none bright-none btop-none mbottom0 mleft0 p0px c555' align='left'>Топовые объявления. <a href='/search/?top_all' id='top_all'>Показать все</a>.</h2>";
                                echo "</div>";
                        }
			elseif($op == "top")
			{
				echo "<div class='p10px'>";
				echo "<h2 class='mh1 arial bbottom-none bright-none btop-none mbottom0 mleft0 p0px c555' align='left'>Топовые объявления. Найдено ".$ii." объявлений.  <a href='search/'>Назад к списку</a></h2>";
				echo "</div>";
			}
		$this -> NUMBER = 0;
		if ($op == "otherboard")
			echo "<table class='board' cellpadding='0' cellspacing='0' align='left' ".(($op == "top4") ? 'style="box-shadow: 0px 0px 1px 1px rgb(220, 220, 220);"' : '').">";
		else
			echo "<table class='board' cellpadding='0' cellspacing='0' align='left' ".(($op == "top4") ? 'style="box-shadow: 0px 0px 1px 1px rgb(220, 220, 220);"' : 'style="box-shadow: 0px 0px 1px 1px rgb(220, 220, 220);"').">";
			foreach($b as $board)
				{
					$this->WriteBoard($board);
					$this->NUMBER++;
				}
			echo "</table>";
			echo "<div class='cboth'></div>";
				//echo "<div style='clear:both;'></div>";
		echo "";
		} else {
			if($op == 'search')
			echo "<div class='none' align='center'>По данному запросу нет объявлений.</div>";
		}
	}
	public function getBoardMobile ($op='search') // ******** Функция показа объявлений ***
		{
			$this->getQuery($op);
			$this->getResultsInfo();
			$query = $this -> SQL_QUERY;
			if($this -> NUM > 0)
			{
				$ii = 0;
				$b = array();
	                        while($board = mysql_fetch_assoc($query))
	                                {
	                                        $b[] = $board;
						$ii++;
	                                }
				if($op == "otherboard")
				{
					echo "<div class=\"search-header\">";
					echo "<h4>Похожие объявления. Найдено ".$ii." объявлений.</h4>";
					echo "</div>";
				}
				elseif($op == "search")
				{
					echo "<div class=\"search-header\">";
					echo "<h4>Все объявления. Найдено ".$this -> NUM." объявлений.</h4>";
					echo "</div>";
				}
	                        elseif($op == "top4")
	                        {

	                                echo "<div class=\"search-header\">";
	                                echo "<h4>Топовые объявления. <a href='/search/?top_all' id='top_all'>Показать все</a>.</h4>";
	                                echo "</div>";
	                        }
				elseif($op == "top")
				{
					echo "<div class=\"search-header\">";
					echo "<h4>Топовые объявления. Найдено ".$ii." объявлений.  <a href='search/'>Назад к списку</a></h4>";
					echo "</div>";
				}
			$this -> NUMBER = 0;
				echo "<table class='result-table'>";
				foreach($b as $board)
					{
						$this->WriteBoardMobile($board);
						$this->NUMBER++;
					}
				echo "</table>";
			echo "";
			} else {
				if($op == 'search')
				echo '
				<div class="panel panel-default">
				  <div class="panel-body">
				    По данному запросу нет объявлений.
				  </div>
				</div>';
			}
		}
function WriteBoard($board)
{
	$class=($this->NUMBER % 2 == 0 ? "bgfa" : "").($board['is_color'] == '1' ? " colored" : "");
	echo "<tr><td>";
	echo "<table cellpadding='0' cellspacing='0' class='border-b {$class}'>";
	echo "<td class='td1'>".$this->getDate($board['time'])."</td>";
	echo "<td class='td2'>";
	if($board['photos_id'] != '') {
		$url = HOME."photo/".$board['photos_id']."/avatar.jpg";
		$url_local = DIR."/photo/".$board['photos_id']."/avatar.jpg";
		if(file_exists($url_local)) {
			$size = functions::getFullSize($url_local, 90, 90);
			echo "
			<a href='".HOME."obj/".$board['board_id']."/'>
			<span class='avatar' style='text-align: center;'>
			<img src='$url?".time()."' width='".$size['width']."' height='".$size['height']."' style='width: ".$size['width']."px !important; height: ".$size['height']."px !important; margin-top: ".((90-$size['height'])/2)."px;'/>
			</span>
			</a>";
		}else echo "";
	}else echo "&nbsp";
	echo "</td>";
	echo "<td class='td3' align='left'>";
	echo ($board['is_important']=='1'?"<span class='important'>Срочно!</span>":"")."<a href='".HOME."obj/".$board['board_id']."/' class='a-obj'>".$board['header']."</a>";
	echo ($price = $this->getPrice($board['price'])) !== '0' ? "<span class='price'>".functions::currency($board['price'], $board['price_t'])." грн."."</span>": "";
	echo "<span class='s-categories' style='white-space: normal;'>
	<a href='".HOME.$board['city']."/".$board['root_href']."/'>".$board['root_category']."</a> »
	<a href='".HOME.$board['city']."/".$board['cat_href']."/'>".$board['category']."</a>
	<br /><a href='".HOME.$board['city']."/'>".$board['city_name']."</a></span>";
	echo "</td>";
	echo "<td class='' align='right'>";
	echo "</td>";
	echo "</table>";
	echo "</td></tr>";
}


function WriteBoardMobile($board)
{
	$class=($board['is_color'] == '1' ? " colored" : "");
	echo "<tr><td>";
	echo "<table class='result-table'>";
	echo "<col width=\"5%\"/>";
	echo "<col width=\"5%\"/>";
	echo "<col width=\"90%\"/>";
	echo "<tr class='{$class}'>";
	echo "<td class='date'><div class='count'><span class=\"date\">".$this->getDate($board['time'])."</span></div></td>";
	echo "<td class=\"thumb\">";
	if($board['photos_id'] != '') {
		$url = HOME."photo/".$board['photos_id']."/avatar.jpg";
		$url_local = DIR."/photo/".$board['photos_id']."/avatar.jpg";
		if(file_exists($url_local)) {
			$size = functions::getFullSize($url_local, 90, 90);
			echo "
			<a href='".HOME."obj/".$board['board_id']."/'>
			<img src='$url?".time()."'/>
			</a>";
		}else echo "";
	}else echo "&nbsp";
	echo "</td>";
	echo "<td class=\"text\">";
	echo ($board['is_important']=='1'?"<span class='important'>Срочно!</span>":"")."<a href='".HOME."obj/".$board['board_id']."/' class='link'>".$board['header']."</a>";
	echo ($price = $this->getPrice($board['price'])) !== '0' ? "<span class='price'>".functions::currency($board['price'], $board['price_t'])." грн."."</span>": "";
	echo "<span class='category'>
	<a href='".HOME.$board['city']."/".$board['root_href']."/'>".$board['root_category']."</a> »
	<a href='".HOME.$board['city']."/".$board['cat_href']."/'>".$board['category']."</a>
	<br /><a href='".HOME.$board['city']."/'>".$board['city_name']."</a></span>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</td></tr>";
}
function symb_max($text, $max_symb)
{
	$strlen = iconv_strlen($text, 'UTF-8');
	if($strlen > $max_symb)
	$text = mb_substr($text, 0, $max_symb, 'UTF-8')."...";
	return $text;
}

public function getResultsInfo()
	{
		if(isset($_GET['q']) && !empty($_GET['q']))
		echo "<div class='results-info'></div>";
	}
protected function getPhoto($ID, $size=null, $w_size=null)
{
	$num = $this -> result("SELECT COUNT(*) FROM photo WHERE folder = '".functions::q($ID)."' and status = 'ok'", 0);
	if($num > 0)
		{
			$url = "photo/".$ID."/avatar_gallery.jpg";
			if(file_exists($url))
				{
				echo "<img src='http://".$_SERVER['HTTP_HOST']."/".$url."' height='262'/>";
				}else{
				echo "<img src='http://".$_SERVER['HTTP_HOST']."/img/nophoto-gallery.png'>";
				}
		}
	else
		{
			echo "<img src='http://".$_SERVER['HTTP_HOST']."/img/nophoto-gallery.png'>";
		}
}

protected function getFullUrl ()
{
	$args = func_get_args();
	$url = "http://".$_SERVER['HTTP_HOST']."/".(empty($this->CAT_HREF) ? (empty($this->R) ? 'search/' : $this->R."/") : (empty($this->R) ? $this->CAT_HREF."/" : $this->R."/".$this->CAT_HREF."/"));
	$i = 0;
	foreach ($_GET as $a => $b)
	{
		if($a != 'page' && $a != 'r' && $a != 'cat_href' && !in_array($a, $args))
		{
			$url .= ($i == 0 ? '?' : '&').($a."=".urlencode($b));
			$i++;
		}
	}
	$url .= $i == 0 ? "?" : "&";

	return $url;
}

function getPages ($f_url="")
{
	if(empty($f_url)) $f_url = $this->getFullUrl();
	if($this->getCountPages() > 1) {
		$url_start = "";
		$url_end   = "";
		$url       = "";

		$diapazon = 4;
		$page_from = $this -> PAGE - $diapazon;
		if($page_from < 1) $page_from = 1;
		$page_to   = $this -> PAGE + $diapazon;
		if($page_to > $this -> getCountPages()) $page_to = $this -> getCountPages();


		$url_start = $f_url;
		$url_start = substr($url_start, 0, strlen($url_start)-1);
		$url_end = $f_url."page=". $this -> getCountPages()."";
		$url_def   = $f_url."page=";

		echo "<ul class=\"bottom-pages liststyle-none mtop11\">";
		if ($this -> PAGE > ($diapazon+1))
			{
				echo "<li class='left mright5'><a href=\"".$url_start."\" class=''>1...</a></li>";
			}
		for ($i = $page_from; $i <= $page_to; $i++)
			{
				if($this->PAGE == $i)
				{
					$class = 'page-active';
					$tag = 'i';
				}
				else
				{
					$tag = 'a';
					$class = '';
				}
				$rel = "";
				if($i == 1){
				$url = $url_start;
				}else
				$url = $url_def.$i;
				echo "<li class='left mright5'><$tag href=\"".$url."\" class='$class'$rel>";
					echo $i;
				echo "</$tag></li>";
			}
		if ($this -> PAGE < ($this -> getCountPages() - $diapazon))
			{
				echo "<li><a href=\"".$url_end."\" class=''>...".$this -> getCountPages()."</a></li>";
			}
		if ($this -> PAGE >1)
			{
				echo "<li class='left mright5'><a href=\"".$url_def.intval($this -> PAGE - 1)."\" class='' rel='prev'>« назад</a></li>";
			}
		if ($this -> PAGE < $this -> getCountPages())
			{
				echo "<li><a href=\"".$url_def.intval($this -> PAGE + 1)."\" class='' rel='next'>вперед »</a></li>";
			}
		echo "</ul>";
		}
}

function getPagesMobile($f_url="")
{
	if(empty($f_url)) $f_url = $this->getFullUrl();
	if($this->getCountPages() > 1) {
		$url_start = "";
		$url_end   = "";
		$url       = "";

		$diapazon = 4;
		$page_from = $this -> PAGE - $diapazon;
		if($page_from < 1) $page_from = 1;
		$page_to   = $this -> PAGE + $diapazon;
		if($page_to > $this -> getCountPages()) $page_to = $this -> getCountPages();


		$url_start = $f_url;
		$url_start = substr($url_start, 0, strlen($url_start)-1);
		$url_end = $f_url."page=". $this -> getCountPages()."";
		$url_def   = $f_url."page=";
		echo '<section class="pagin">';
		echo "<ul class=\"pagination\">";
		if ($this -> PAGE >1)
			{
				echo "<li><a href=\"".$url_def.intval($this -> PAGE - 1)."\" class='' rel='prev'>«</a></li>";
			}
		if ($this -> PAGE > ($diapazon+1))
			{
				echo "<li><a href=\"".$url_start."\" class=''>1...</a></li>";
			}
		for ($i = $page_from; $i <= $page_to; $i++)
			{
				if($this->PAGE == $i)
				{
					$class = 'active';
				}
				else
				{
					$class = '';
				}
				$rel = "";
				if($i == 1){
				$url = $url_start;
				}else
				$url = $url_def.$i;
				echo "<li class='$class'><a href=\"".$url."\"$rel>";
					echo $i;
				echo "</a></li>";
			}
		if ($this -> PAGE < ($this -> getCountPages() - $diapazon))
			{
				echo "<li><a href=\"".$url_end."\">...".$this -> getCountPages()."</a></li>";
			}
		if ($this -> PAGE < $this -> getCountPages())
			{
				echo "<li><a href=\"".$url_def.intval($this -> PAGE + 1)."\" class='' rel='next'>»</a></li>";
			}
		echo "</ul>";
		echo "</section>";
		}
}

protected function _isset($g, $g1=null)
	{
	$errors = 0;
		foreach ($_GET as $a => $b)
			if($a != $g)
				if($g1 != null)
				{
					if($a !=$g1)
						$errors ++;
				}else
					$errors ++;

	if($errors == 0)
		return true;
	else
		return false;
	}

function getPageLimit()
{
	return (($this -> COUNT_ADS) * ($this -> PAGE) - ($this -> COUNT_ADS));
}

function getCountPages()
{
	$num = $this -> NUM;
	$count_pages = intval($num / $this -> COUNT_ADS);
	$ostatok = $num % $this -> COUNT_ADS;

	if($ostatok > 0) $count_pages++;


	return $count_pages;
}

protected function getQuery($status=null) //******* Функция составления запроса для БД
{
mysql_query("UPDATE board SET top_time='0' WHERE top_time<'".time()."'");
$query  = "SELECT SQL_CALC_FOUND_ROWS *, A.title as header, A.href as board_id, A.id as b_id,
			B.name as category, C.name as root_category, B.href as category_href,
			B.href as cat_href, C.href as root_href, D.name as city_name,
			A.*, B.*, C.*, D.* FROM
			board as A,
			categories as B,
			categories as C,
			region as D
			WHERE
			B.id = A.id_category and C.id = B.root_id and D.href = A.city
			and status = 'ok'";
			if(isset($_GET['board_id']) && !empty($_GET['board_id']))
				{
					$query .= " and A.href != '".functions::q($_GET['board_id'])."'";
				}
			if(!empty($this->CAT_HREF))
				{
					$cat = $this -> CATEGORY['id'];
					if($this->CATEGORY['root_id'] != '0')
						{
							$query .= " and id_category = '".functions::q($cat)."'";
						}
					else
						{
							$query .=  " and id_category IN(".$this -> getSubCategories($cat).")";
						}
				}
			if(isset($_GET['q']) && !empty($_GET['q']))
				{
					$query .= " and ".$this -> getMorphy();
				}
			if(!empty($this->R))
				{
					if($this -> checkRegion($this->R)){
						$query .= " and (city IN(".$this -> getSubRegions($this->REGION['id']).") or city = 'all')";
					}else
						$query .= " and (city = '".functions::q($this->R)."' or city = 'all')";
				}
			if(isset($_GET['photoonly']) && !empty($_GET['photoonly']))
				{
					$query .= " and photos_id != '0'";
				}
			if(isset($_GET['priceonly']) && !empty($_GET['priceonly']))
				{
					$query .= " and A.price != ''";
				}
			if(isset($_GET['price_min']) && !empty($_GET['price_min']) && is_numeric($_GET['price_min']))
				{
					$query .= " and A.price > ".$_GET['price_min']."";
				}
			if(isset($_GET['price_max']) && !empty($_GET['price_max']) && is_numeric($_GET['price_max']))
				{
					$query .= " and A.price < ".$_GET['price_max']."";
				}
			if(isset($_GET['today']) && !empty($_GET['today']))
				{
					$query .= " and A.time > UNIX_TIMESTAMP(CURDATE())";
				}
			if(isset($_GET['hash']) && !empty($_GET['hash']))
				{
					$query .= " and MD5(A.email) = '".functions::q($_GET['hash'])."'";
				}
			if(isset($_GET['rayon']) && !empty($_GET['rayon']))
				{
					$query .= " and A.rayon = '".trim(functions::q($_GET['rayon']))."'";
				}
			if(isset($_GET['metro']) && !empty($_GET['metro']))
				{
					$query .= " and A.metro = '".trim(functions::q($_GET['metro']))."'";
				}
				if($status == "top")
				{
					$query .= " AND top_time!='0' ORDER BY top_time DESC";
				}elseif($status == "top4"){
					$query .= " AND top_time!='0' ORDER BY RAND()";
				}else{

				if(isset($_GET['type']) && !empty($_GET['type']))
						if($_GET['type'] == 'S' or $_GET['type'] == 'B')
						$query .= " and A.type = '".functions::q($_GET['type'])."'";
				$order = isset($_GET['sort']) && !empty($_GET['sort']) ? functions::q($_GET['sort']) : "time DESC";
					$query .= " ORDER BY $order";

					//$query .= " ORDER BY ".$sort." ".$order;
				}
				if($status == "top")
				{
					$query .= " LIMIT ".$this -> getPageLimit().", ".$this -> COUNT_ADS;
				}elseif($status == "top4")
				{
					$query .= " LIMIT 4";

				}else{
					$query .= " LIMIT ".$this -> getPageLimit().", ".$this -> COUNT_ADS;
				}
$this->SQL_QUERY = $this->db->query($query);
$this -> NUM = $this->db->result("SELECT FOUND_ROWS()", 0);
}

function getDate ($time) //*******Возвращает ДАТУ
{
$newdate=date("d.m.y", time());
$olddate=date("d.m.y", $time);
if($newdate == $olddate)
	{
	$date = "Cегодня<br />  ".date("H:i", $time);
	}else{
	$date = $this -> rdate("j M", $time);
	}
return $date;
}

protected function getPrice ($p) //******* Возвращает отформатированую ЦЕНУ
{
	if(!empty($p))
	{
		if(is_numeric($p))
			$price=number_format($p, 0, '.', ',');
		else
			$price=$p;
		return $price;
	}
	else
	{
	return "0";
	}
}

protected function getCategory($_HREF)
{
	return $this->db->result('SELECT id FROM categories WHERE href = "'.$_HREF.'" LIMIT 1', 0);
}

protected function checkCategory ($_ID_CAT) //******* Проверка на наличие подразделов
{
	$sql = "SELECT root_id FROM categories WHERE href = '".functions::q($_ID_CAT)."' LIMIT 1";
	$query = $this->db->result($sql, 0);

	if ($query == "0")
		return true;
	else
		return false;
}


protected function checkRegion ($region) //******* Проверка на наличие ГОРОДОВ
{
if(!empty($region))
{
	$region = trim($region);
	$sql = "SELECT root_id FROM region WHERE href = '".functions::q($region)."' LIMIT 1";
	$query = $this->db->result($sql, 0);

	if ($query == "0")
		return true;
	else
		return false;
}
}


protected function getSubCategories ($_CAT_ID) //******* Возвращает ПОДРАЗДЕЛЫ
{
	return "SELECT id FROM categories WHERE root_id = '".functions::q($_CAT_ID)."'";
}


protected function getSubRegions ($h) //******* Возвращает ГОРОДА в регионе
{
	$q =  $this->db->query("SELECT href FROM region WHERE root_id = '".functions::q($h)."'");
	$h = "";
	while($arr = mysql_fetch_array($q))
	{
		$h .= "'".$arr['href']."', ";
	}
	$h=substr($h, 0, strlen($h)-2);
	return $h;

}


protected function getRegionId ($href) // ******* Возвращает ID региона
{
	$sql = "SELECT id FROM region WHERE href = '".functions::q($href)."' LIMIT 1";
	$query = $this->result($sql, 0);
	return $query;
}


protected function setTitleInfo()
{
	if(!empty($this->CAT_HREF))
		{
			$this->INFO['title'] = !empty($this->CATEGORY['title']) ? $this->CATEGORY['title'] : "%CATEGORY% : %REGION% - объявления на %SITE%";
			$this->INFO['description'] = $this->CATEGORY['description'];
			$this->INFO['keywords'] = $this->CATEGORY['keywords'];
			$this->INFO['foot_text'] = $this->CATEGORY['foot_text'];
			$this->INFO['h1'] = !empty($this->CATEGORY['h1']) ? $this->CATEGORY['h1'] : "%CATEGORY% - %REGION%";
		}else{
			$this->INFO = $this->db->returnFirst("SELECT * FROM options WHERE link = 'search'");
		}
		if(isset($_GET['page']) && !empty($_GET['page'])) $this->INFO['title'] .= " - Страница №".intval($_GET['page']);
}


protected function getMorphy($txt = null) // ******* Функция МОРФОЛОГИИ
{
require_once($_SERVER['DOCUMENT_ROOT'].'/plugins/phpmorphy/src/common.php');

$opts = array(
	'storage' => PHPMORPHY_STORAGE_FILE,
	// Extend graminfo for getAllFormsWithGramInfo method call
	'with_gramtab' => false,
	// Enable prediction by suffix
	'predict_by_suffix' => true,
	// Enable prediction by prefix
	'predict_by_db' => true
);

	// Path to directory where dictionaries located
	$dir = $_SERVER['DOCUMENT_ROOT'].'/plugins/phpmorphy/dicts/';

	// Create descriptor for dictionary located in $dir directory with russian language
	$dict_bundle = new phpMorphy_FilesBundle($dir, 'rus');

	// Create phpMorphy instance
	try {
	$morphy = new phpMorphy($dict_bundle, $opts);
	} catch(phpMorphy_Exception $e) {
	die('Error occured while creating phpMorphy instance: ' . $e->getMessage());
	}


	$search = $_GET['q'];

	$search = mysql_real_escape_string($search);
	$search = mb_substr($search, 0, 30, 'UTF-8');

	$search = explode(" ", $search);
	$q='';
	for ($i=0; $i<count($search); $i++) {
		$ass= $search[$i];
		$ass = mb_strtoupper($ass, 'utf-8');
		$pseudo_root = $morphy->getPseudoRoot($ass);
		if(false === $pseudo_root)
			{
				$rep=$ass;
			}
			else
			{
				$rep=$pseudo_root[0];
			}
			if($txt == "text")
				$q=$q."A.text LIKE '%".functions::q($rep)."%' and ";
			else
				$q=$q."A.title LIKE '%".functions::q($rep)."%' and ";

	}
	$searchwords=substr($q, 0, strlen($q)-4);
		return $searchwords;
}

protected function getExtension($filename) {
    $path_info = pathinfo($filename);
    return $path_info['extension'];
  }
protected function rdate($param, $time=0) {
	if(intval($time)==0)$time=time();
	$MonthNames=array("янв.", "фев.", "мар.", "апр.", "мая", "июн.", "июл.", "авг.", "сен.", "окт.", "нояб.", "дек.");
	if(strpos($param,'M')===false) return date($param, $time);
		else return date(str_replace('M',$MonthNames[date('n',$time)-1],$param), $time);
}


}
