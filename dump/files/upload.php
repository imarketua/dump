<?php
require_once("classes/functions.php");
require_once("classes/image.php");
require_once("defines.php");
require_once("mysql.php");

class upload extends DataBase
{
	private $db;
	private $f;
	private $image;
	
	private $dir = "photo/";  
	private $_count = 6;     
	private $max_width = 600;
	
	private $act;
	private $ext;
	private $folder;
	private $path;
	private $name;
	
	public function __construct()
	{
		$this->folder = functions::q($_POST['id']);
		$this->act = functions::q($_POST['op']);
		
		$this->db = new DataBase();
		$this->f = new functions();
		$this->image = new SimpleImage();
		
		if($this->act == 'upload') {
			$name = $_FILES['uploadfile']['name'];
			$exp = explode('.', $name); 
			$this->ext = $exp[count($exp) - 1];
			$filetypes = array('jpg','gif','bmp','png','JPG','BMP','GIF','PNG','jpeg','JPEG');
			if(!in_array($this->ext, $filetypes)) die("wrong_format");
			else {
				$this->photoName();	
				if($this->getNum() < $this->_count) $this->uploadFile();
				else die("Максимальное количество загружаемых фотографий в объявление не должно превышать 6 шт.");
			}
		} elseif($this->act == 'delete') {
			$this->delete();
		}
	}
	protected function getNum()
	{
		return $this->db->result("SELECT COUNT(*) FROM photo WHERE folder = '".$this->folder."'");
	}
	protected function photoName()
	{
		$dir = $this->dir.$this->folder;
		if(!is_dir($dir)) mkdir($dir, 0777);
		
		$code = $this->f->generate_password(20);
		$name = $code.".".$this->ext; 
		$path = $this->dir.$this->folder."/".$name;

		$this -> path = $path;
		$this -> name = $name;
	}
	protected function uploadFile()
	{
		if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $this->path)) 
		{
			$path = $this->path;

			if(!$this->db->query("INSERT photo (name,folder,time,status) 
			VALUES ('".$this->name."','".$this->folder."','".time()."','temp')")) die("error");
			$id = $this->db->getLastId();

			$this->image->load($path);
			$full = new SimpleImage();
			$full->load($path);
			if($full->getWidth() > 1200) $full->resizeToWidth(1200);
			$full->save(str_replace('.','.full.',$path));

			if($this->image->getWidth() > 600) $this->image->resizeToWidth(600);
			$this->image->save($path);

			$size = $this->f->getFullSize($path, 90, 90);

			$img = Array(
				'name' => $this->name,
				'path' => $path,
				'count' => $this->getNum(),
				'width' => $size['width'],
				'height' => $size['height'],
				'id' => $id
			);

			echo json_encode($img);
		}else echo "error";
	}
	protected function delete()
	{
		$this->db->query("SELECT * FROM photo WHERE id = '".$this->folder."'");
		$query = $this->db->data;
		if($this->db->getNumRows() > 0) {
			while($b = mysql_fetch_assoc($query)) {
				$file = "./".$this->dir.$b['folder']."/".$b['name'];
				
				if(@unlink($file)) {
					  function removeDirectory($dir) {
    if ($objs = glob($dir."/*")) {
       foreach($objs as $obj) {
         is_dir($obj) ? removeDirectory($obj) : unlink($obj);
       }
    }
					rmdir("./".$this->dir.$b['folder']."/");
}
					if($this->db->query("DELETE FROM photo WHERE id = '".$this->folder."'")) 
					{
						$n = $this->db->result("SELECT COUNT(*) FROM photo WHERE folder = '".$b['folder']."' 
						and status = 'ok'");
						if($n == 0)
						{
							$avatar = "./photo/".$b['folder']."/avatar.jpg";
							$this->db->query("UPDATE board SET photos_id = '0' WHERE photos_id = '".$folder."'");
							if(file_exists($avatar)) unlink($avatar);
						}
						echo "ok";
					} else die("error");
				} else die("error");  
			}
		} else die("Photo not found");
	}
}
new upload();



?>
