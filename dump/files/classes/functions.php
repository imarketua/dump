<?
class functions
{
public static function translit($str) 
	{
		$tr = array(
			"А"=>"a","Б"=>"b","В"=>"v","Г"=>"g",
			"Д"=>"d","Е"=>"e","Ё"=>"e","Ж"=>"j","З"=>"z","И"=>"i",
			"Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n",
			"О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t",
			"У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch",
			"Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"yi","Ь"=>"",
			"Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b",
			"в"=>"v","г"=>"g","д"=>"d","е"=>"e","ё"=>"e","ж"=>"j",
			"з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
			"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
			"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
			"ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
			"ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya", 
			" "=> "-", ","=> "", "."=> "", "/"=> "-", "!" => "",
			"?" => "", "(" => "", ")" => "", "ї" => "i", "Ї" => "I", "і" => "i", "І" => "I", '"' => "", "'" => "",
			"»" => "", "«" => ""
		);
		$url = strtr($str,$tr);
		if (preg_match('/[^A-Za-z0-9_\-]/', $url)) {
		$url = preg_replace('/[^A-Za-z0-9_\-]/', '', $url);
		}
		return $url;
		
	}
public static function generate_password($number)
  {
    $arr = array('a','b','c','d','e','f',
                 'g','h','i','j','k','l',
                 'm','n','o','p','r','s',
                 't','u','v','x','y','z',
                 'A','B','C','D','E','F',
                 'G','H','I','J','K','L',
                 'M','N','O','P','R','S',
                 'T','U','V','X','Y','Z',
                 '1','2','3','4','5','6',
                 '7','8','9','0');
    $pass = "";
    for($i = 0; $i < $number; $i++)
    {
      $index = rand(0, count($arr) - 1);
      $pass .= $arr[$index];
    }
    return $pass;
  }
public function crop_str($string, $limit)
	{
		$string=$string.' ';
		$strLength = iconv_strlen($string, 'Windows-1251') - 1;
		$substring_limited = substr($string,0, $limit);        //режем строку от 0 до limit
		$result=substr($substring_limited, 0, strrpos($substring_limited, ' ' ));    //берем часть обрезанной строки от 0 до последнего пробела
		$strlimit = iconv_strlen($result, 'Windows-1251');
		if($strLength>$strlimit )
		$result=$result."...";
		return $result;
	}
public function rdate($param, $time=0) {
	if(intval($time)==0)$time=time();
	$MonthNames=array("янв.", "фев.", "мар.", "апр.", "мая", "июн.", "июл.", "авг.", "сен.", "окт.", "нояб.", "дек.");
	if(strpos($param,'M')===false) return date($param, $time);
		else return date(str_replace('M',$MonthNames[date('n',$time)-1],$param), $time);
}
function getPhoto($name, $width, $height, $params="", $method="echo")
	{
		$url = HOME.substr($name, 2);
		$size = functions::getFullSize(DIR.substr($name, 2), $width, $height);
		
		$width = $size['width'];
		$height = $size['height'];
		
		$txt = "<img src=\"".$url."\" width=".$width." height=".$height." ".$params."/>";
		if($method == "return")
		return $txt;
		else
		echo $txt;
	}
	public static function getFullSize($url, $width=90, $height=90)
	{
		$result = Array (
			"width"  => 0,
			"height" => 0
		);

		if (file_exists($url)) {
			if($imgsize=getimagesize ($url))
			{
				$img_width = $imgsize[0];
				$img_height = $imgsize[1];
				
				$coef = $img_width / $img_height;
		
				if($img_width < $img_height)
				{
					$imgheight=$height;
					$imgwidth=round($coef*$height);
				}else{
				$h = round($width/$coef);
				if($h <= $height) {
					$imgheight = $h;
					$imgwidth=$width;
				}else{
					$imgheight=$height;
					$imgwidth=round($coef*$height);
				}
				}
				
				$result = Array (
					"width"  => $imgwidth,
					"height" => $imgheight
				);
			}
		}

		return $result;
	}
	
	public static function q($s)
	{
		if (!get_magic_quotes_gpc()) {
			if (is_string ($s)) {
				$s = mysql_real_escape_string ((string) $s);
				return $s;
			}
			elseif (is_array ($s)) {
				foreach ($s as $key => $v) {
					$s[$key] = mysql_real_escape_string ((string) $v);
				}
				return $s;
			} else {
				return $s; 
			}
		}
		
		return $s;
	}

    public static function currency($num, $type){

        if($type == 'uah') return number_format($num);

        $ch = curl_init('https://api.privatbank.ua/p24api/pubinfo?exchange&coursid=5');

        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_VERBOSE => false
        );
        curl_setopt_array($ch, $options);
        $r = explode("<exchangerate",curl_exec($ch));
        $r2 = explode(' ', $r[4]);
        $cur = str_replace('"','', str_replace('sale=','',$r2[4]));

        return number_format(round($num * $cur));

    }
}
?>