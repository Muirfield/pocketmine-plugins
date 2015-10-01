<?php
//= api-features
//: - Skin tools
namespace aliuly\common;
use pocketmine\entity\Human;
/**
 * Routines for manipulating skins
 */
abstract class SkinUtils {
  const RAW_FMT = "raw";
  const PNG_FMT = "png";
  const AUTO_FMT = "";
  /**
   * Return supported file formats
   * @return str[]
   */
  static public function formats() {
    $fmt = [ self::RAW_FMT => self::RAW_FMT ];
		if(extension_loaded("gd")) $fmt[self::PNG_FMT] = self::PNG_FMT;
    return $fmt;
  }
  /**
   * Check if the filename extension is .png
   * @param str $f
   * @return bool
   */
  static public function isPngExt($f) {
    return preg_match('/\.[pP][nN][gG]$/',$f);
  }
  /**
   * Check if file is a valid skin
   * @param str $file
   * @return bool
   */
  static public function isSkinFile($file) {
    if (!file_exists($file)) return false;
    if (preg_match('/\.[sS][kK][iI][nN]$/', $file)) return true;
    if (!extension_loaded("gd")) return false;
    if (self::isPngExt($file)) {
      $size = getimagesize($file);
      //print_r($size);//##DEBUG
      //print_r([$file,IMAGETYPE_PNG]);//##DEBUG
      //var_dump($size[0] == 64 && $size[1] == 32 && $size[2] == IMAGETYPE_PNG);//##DEBUG
      return ($size[0] == 64 && $size[1] == 32 && $size[2] == IMAGETYPE_PNG);
    }
    return false;
  }

  /**
   * Change the skin type (slim or non-slim)
   * @param Human $human
   * @param bool $slim
   */
  static public function setSlim(Human $human, $slim = false) {
    $human->setSkin($human->getSkinData(),$slim);
  }
  /**
   * Save skin
   * @param Human $human
   * @param str $fn
   * @param str $fmt
   * @return int
   */
  static public function saveSkin(Human $human, $fn, $fmt = self::AUTO_FMT) {
    if ($fmt === self::AUTO_FMT) {
      $fmt = self::RAW_FMT;
      if (self::isPngExt($fn)) $fmt = self::PNG_FMT;
    }
    if(extension_loaded("gd") && $fmt == self::PNG_FMT) {
      $img = imagecreatetruecolor(64, 32);
      // Allocate colors...
      $colors = [];
      $bytes = $human->getSkinData();
      echo "BYTES=".strlen($bytes)."\n";//##DEBUG
      $x = $y = $c = 0;
      while ($y < 32) {
        $cid = substr($bytes, $c, 3);
        if (!isset($colors[$cid])) {
          $colors[$cid] = imagecolorallocate($img, ord($cid{0}), ord($cid{1}),ord($cid{2}) );
        }
        imagesetpixel($img, $x, $y, $colors[$cid]);
        $x++;
        $c += 4;
        if ($x === 64) {
          $x = 0;
          $y++;
        }
      }
      echo  "COLORS=".count($colors)."\n";//##DEBUG
      if (!imagepng($img, $fn)) {
        imagedestroy($img);
        return 0;
      }
      imagedestroy($img);
      return filesize($fn);
    }
    $bin = zlib_encode($human->getSkinData(),ZLIB_ENCODING_DEFLATE,9);
    file_put_contents($fn,$bin);
    return strlen($bin);
	}

  /**
   * Load skin
   * @param Human $human
   * @param bool $slim
   * @param str $fn
   * @return bool
   */
  static public function loadSkin(Human $human, $slim, $fn) {
    if (self::isPngExt($fn)) {
      if(!extension_loaded("gd")) return false;
      if(!self::isSkinFile($fn)) return false;
      $img = imagecreatefrompng($fn);
      if ($img === false) return false;
      $bytes = "";
      $x = $y = 0;
      while ($y < 32) {
        $rgb = imagecolorat($img,$x,$y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        $bytes .= chr($r).chr($g).chr($b).chr(255);

        $x++;
        if ($x === 64) {
          $x = 0;
          $y++;
        }
      }
      imagedestroy($img);
      echo "BYTES=".strlen($bytes)."\n";//##DEBUG
      $human->setSkin($bytes,$slim);
      return true;
    }
    $bin = file_get_contents($fn);
    if ($bin === false) return false;
    $human->setSkin(zlib_decode($bin),$slim);
    return true;
  }
}
