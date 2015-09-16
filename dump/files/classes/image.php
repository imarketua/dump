<?
//http://browsershots.org/http://sowa.net.ua/#
class SimpleImage {
 
   var $image;
   var $image_type;
 
   function load($filename) {
      $image_info = getimagesize($filename);
      $this->image_type = $image_info[2];
      if( $this->image_type == IMAGETYPE_JPEG ) {
         $this->image = imagecreatefromjpeg($filename);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
         $this->image = imagecreatefromgif($filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {
         $this->image = imagecreatefrompng($filename);
      }
   }
   function save($filename, $image_type=null, $compression=75, $permissions=null) {
   
	  if($image_type == null)
		$image_type=$this->image_type;
		$white = imagecolorallocate($this->image,255,255,255); //определяем белый цвет
		imagecolortransparent($this->image,$white); //делаем белый цвет прозрачным
	
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image,$filename,$compression);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image,$filename);
      } elseif( $image_type == IMAGETYPE_PNG ) {
		imagesavealpha($this->image, true);
         imagepng($this->image,$filename);
      }
      if( $permissions != null) {
         chmod($filename,$permissions);
      }
   }
   function output($image_type=IMAGETYPE_JPEG) {
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image);
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image);
      }
   }
   function getWidth() {
      return imagesx($this->image);
   }
   function getHeight() {
      return imagesy($this->image);
   }
   function resizeToHeight($height) {
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }
   function resizeToWidth($width) {
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height);
   }
   function scale($scale) {
      $width = $this->getWidth() * $scale/100;
      $height = $this->getheight() * $scale/100;
      $this->resize($width,$height);
   }
   function resize($width,$height) {
      $new_image = imagecreatetruecolor($width, $height);
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $this->image = $new_image;
   }
   function AddWatermark($watermark = 'img/watermark.png'){
		$offset = 5;
		$img = $this -> image;
		$xImg = $this -> getWidth();
		$yImg = $this -> getHeight();
		

		$r = imagecreatefrompng($watermark);
		$x = imagesx($r);
		$y = imagesy($r);

		$xDest = $xImg - ($x + $offset);
		$yDest = $yImg - ($y + $offset);
		imageAlphaBlending($img,1);
		imageAlphaBlending($r,1);
		imagesavealpha($img,1);
		imagesavealpha($r,1);
		imagecopyresampled($img,$r,$xDest,$yDest,0,0,$x,$y,$x,$y);
		$this -> image = $img;
	}
	function crop($width,$height) {
      $new_image = imagecreatetruecolor($width, $height);
	  
	  $src_width = $this->getWidth();
	  $src_height = $this->getHeight();
	  
	  if($width > $height)
	  {
		$this->resizeToWidth($width);
	  }else{
		$this->resizeToHeight($height);
	  }
      imagecopy($new_image, $this->image, 0, 0, 0, 0, $width, $height);
      $this->image = $new_image;
   }
   function crop_width($width) {
	  if($width < $this->getWidth())
		{
		  $new_image = imagecreatetruecolor($width, $this->getHeight());
		  imagecopy($new_image, $this->image, 0, 0, 0, 0, $width, $this->getHeight());
		  $this->image = $new_image;
		}
   }
   function crop_height($height) {
	  if($height < $this->getHeight())
		{
		  $new_image = imagecreatetruecolor($this->getWidth(), $height);
		  imagecopy($new_image, $this->image, 0, 0, 0, 0, $this->getWidth(), $height);
		  $this->image = $new_image;
		}
   }
}
?>