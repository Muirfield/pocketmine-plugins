<?php
namespace pmimporter\mcregion;
use pmimporter\mcregion\Chunk;

use pocketmine\utils\Binary;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\Long;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\ByteArray;
use pocketmine\nbt\tag\IntArray;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\NBT;

class RegionLoader extends \pmimporter\generic\RegionLoader {
  public function readChunk($x,$z) {
    $data = $this->readChunkData($x,$z);
    if ($data === null) return null;
    return Chunk::fromBinary($data);
    /*
    $w =  Chunk::fromBinary($data);
    if ($w === null) {
      $index = self::getChunkOffset($x, $z);
      echo("ERROR READING CHUNK $x,$z $index - ");
      echo(implode(':',[$this->locationTable[$index][0],$this->locationTable[$index][1]])."\n");
    }
    return($w);
    */
  }
  public function newChunk($cX,$cZ) {
    //Allocate space
    $nbt = new Compound("Level", []);
    $nbt->xPos = new Int("xPos", $cX);
    $nbt->zPos = new Int("zPos", $cZ);
    $nbt->LastUpdate = new Long("LastUpdate", 0);
    $nbt->LightPopulated = new Byte("LightPopulated", 0);
    $nbt->TerrainPopulated = new Byte("TerrainPopulated", 0);
    $nbt->V = new Byte("V", self::VERSION);
    $nbt->InhabitedTime = new Long("InhabitedTime", 0);
    $nbt->Biomes = new ByteArray("Biomes", str_repeat(Binary::writeByte(-1), 256));
    $nbt->HeightMap = new IntArray("HeightMap", array_fill(0, 256, 127));
    $nbt->BiomeColors = new IntArray("BiomeColors", array_fill(0, 256, Binary::readInt("\x00\x85\xb2\x4a")));

    $nbt->Blocks = new ByteArray("Blocks", str_repeat("\x00", 32768));
    $nbt->Data = new ByteArray("Data", $half = str_repeat("\x00", 16384));
    $nbt->SkyLight = new ByteArray("SkyLight", $half);
    $nbt->BlockLight = new ByteArray("BlockLight", $half);

    $nbt->Entities = new Enum("Entities", []);
    $nbt->Entities->setTagType(NBT::TAG_Compound);
    $nbt->TileEntities = new Enum("TileEntities", []);
    $nbt->TileEntities->setTagType(NBT::TAG_Compound);
    $nbt->TileTicks = new Enum("TileTicks", []);
    $nbt->TileTicks->setTagType(NBT::TAG_Compound);
    $nbt->setName("Level");

    return new Chunk($nbt);
  }
  public function writeChunk($oX,$oZ,$chunk) {
    $chunkData = $chunk->toBinary();
    $this->writeChunkData($oX,$oZ,$chunkData);
  }
}
