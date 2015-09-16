<?
// Подключаемся к MySQL
class DataBase
{
####################Данные для подключения к базе данных####################

protected $host      = "localhost"; #Хост
protected $login     = "root";                   #Логин
protected $password  = "";                      #Пароль
protected $baza_name = "market";                   #Имя базы


public $query;
public $data;
public $mysql_array;

function __construct()
	{
		$db = @mysql_connect($this->host, $this->login, $this->password);
		if (!$db) exit("<p>К сожалению, не доступен сервер MySQL</p>");
		if (!@mysql_select_db($this->baza_name,$db)) exit("<p>К сожалению, не доступна база данных</p>");  
		else{
		mysql_query ("set character_set_results='utf8'"); 
		mysql_set_charset("utf8");}
	}
public function setQuery($q)
	{
		$this -> query = $q;
	}
public function query($q=null)
{
if($q != null)
{
	$this->setQuery($q);
	$this->data = mysql_query($q);
	return $this->data;
}else
	return mysql_query($this -> query);
}
	
public function start($a, $q=null)
	{
		if(!empty($q))
			$this -> setQuery($q);
			
		$query = mysql_query($this -> query);
		while($arr = mysql_fetch_array($query))
			$a($arr);
	}
public function returnFirst($q=null, $p=null)
	{
		if(!empty($q))
			$this -> setQuery($q);
			
		$query = mysql_query($this -> query);
		while($arr = mysql_fetch_array($query)) {
			if($p==null)
			return $arr;
			else
			return $arr[$p];
		}
	}
public function result($q = null, $n=0, $n2=0)
	{
		if(!empty($q))
			$query = $q;
		else
			$query = $this -> query;
		
		$query = mysql_query($query);
		$num = mysql_result($this->query("SELECT FOUND_ROWS()"), 0);
		if($num > 0)
		return mysql_result($query, $n, $n2);
	}
public function getLastId()
{
	return mysql_insert_id();
}
public function getNumRows()
	{
		return mysql_num_rows($this->data);
	}
}

$db = new DataBase;

?>