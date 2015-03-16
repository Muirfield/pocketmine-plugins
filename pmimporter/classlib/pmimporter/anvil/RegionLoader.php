<?php
namespace pmimporter\anvil;
use pocketmine\utils\Binary;
use pmimporter\LevelFormat;
use pmimporter\anvil\Chunk;
use pmimporter\ImporterException;

class RegionLoader extends \pmimporter\generic\RegionLoader {
  public function readChunk($x,$z) {
    $data = $this->readChunkData($x,$z);
    if ($data === null) return null;
    return Chunk::fromBinary($data);
  }

  public function newChunk($oX,$oZ) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function writeChunk($oX,$oZ,$chunk) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }

}
