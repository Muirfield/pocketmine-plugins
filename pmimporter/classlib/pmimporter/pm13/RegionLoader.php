<?php
namespace pmimporter\pm13;
use pmimporter\ImporterException;
use pmimporter\LevelFormat;
use pocketmine_1_3\pmf\PMFLevel;


class RegionLoader {
  protected $formatProvider;

  public function __construct(LevelFormat $fmt) {
    $this->formatProvider = $fmt;
  }

  public function chunkExists($x,$z) {
    $x -= $this->formatProvider->getSetting("Xoff");
    $z -= $this->formatProvider->getSetting("Zoff");
    return ($x < 16 && $z < 16 && $x >= 0 && $z >= 0);
  }
  public function close(){
    $this->levelProvider = null;
  }
  public function getX(){
    return 0;
  }
  public function getZ(){
    return 0;
  }

  protected function writeChunkData($oX,$oZ,$data) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function readChunkData($x,$z) {
    return "$x,$y";
  }
  public function newChunk($cX,$cZ) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function writeChunk($oX,$oZ,$chunk) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function readChunk($x,$z) {
    $x -= $this->formatProvider->getSetting("Xoff");
    $z -= $this->formatProvider->getSetting("Zoff");
    return new Chunk($this->formatProvider,$x,$z);
  }
}
