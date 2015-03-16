<?php
namespace pmimporter\anvil;
use pmimporter\mcregion\McRegion;
use pmimporter\anvil\RegionLoader;

class Anvil extends McRegion {
  public static function getFormatName() {
    return "anvil";
  }
  public static function isValid($path) {
    $isValid = (file_exists($path."/level.dat") && is_dir($path."/region/"));
    if ($isValid) {
      $files = glob($path."/region/*.mc*");
      foreach($files as $f) {
	if (substr($f,-4) != ".mca") {// not Anvil...
	  $isValid = false;
	  break;
	}
      }
    }
    return $isValid;
  }
  public function getRegions() {
    if ($this->regions === null) {
      $this->regions = [];
      $files = glob($this->getPath()."region/r.*.mca");
      foreach ($files as $f) {
	$pp = [];
	if (preg_match('/r\.(-?\d+)\.(-?\d+)\.mca$/',$f,$pp)) {
	  array_shift($pp);
	  $this->regions[$pp[0].",".$pp[1]] = $pp;
	}
      }
    }
    return $this->regions;
  }

  public function getRegion($x,$z) {
    return new RegionLoader($this,$x,$z,"mca",$this->readOnly);
  }
}
