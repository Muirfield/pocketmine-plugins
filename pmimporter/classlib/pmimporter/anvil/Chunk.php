<?php
namespace pmimporter\anvil;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\ByteArray;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\IntArray;
use pocketmine\utils\Binary;

use pmimporter\chunksection\AnvilSection;
use pmimporter\chunksection\EmptyChunkSection;
use pmimporter\ImporterException;

class Chunk extends \pmimporter\generic\Chunk {
  protected $sections = [];

  public function __construct(Compound $nbt){
    parent::__construct($nbt);

    if(isset($this->nbt->Sections) and ($this->nbt->Sections instanceof Enum)){
      $this->nbt->Sections->setTagType(NBT::TAG_Compound);
    } else {
      $this->nbt->Sections = new Enum("Sections", []);
      $this->nbt->Sections->setTagType(NBT::TAG_Compound);
    }
    $sections = [];
    foreach($this->nbt->Sections as $section){
      if($section instanceof Compound){
	$y = (int) $section["Y"];
	if($y < 8){
	  $sections[$y] = new AnvilSection($section);
	}
      }
    }
    for($y = 0; $y < 8; ++$y){
      if(!isset($sections[$y])){
	$sections[$y] = new EmptyChunkSection($y);
      }
    }
    $this->sections = $sections;

    unset($this->nbt->Sections);
  }



  /**
   * @param string        $data
   * @param LevelProvider $provider
   *
   * @return Chunk
   */
  public static function fromBinary($data, LevelProvider $provider = null){
    $nbt = new NBT(NBT::BIG_ENDIAN);

    $nbt->readCompressed($data, ZLIB_ENCODING_DEFLATE);
    $chunk = $nbt->getData();

    if(!isset($chunk->Level) or !($chunk->Level instanceof Compound)){
      return null;
    }

    return new Chunk($chunk->Level);
  }

  public function toBinary(){
    $nbt = clone $this->getNBT();

    $nbt->xPos = new Int("xPos", $this->x);
    $nbt->zPos = new Int("zPos", $this->z);

    $nbt->Sections = new Enum("Sections", []);
    $nbt->Sections->setTagType(NBT::TAG_Compound);
    foreach($this->getSections() as $section){
      if($section instanceof EmptyChunkSection){
	continue;
      }
      $nbt->Sections[$section->getY()] = 
	new Compound(null, 
		     [
		      "Y" => new Byte("Y", $section->getY()),
		      "Blocks" => new ByteArray("Blocks", $section->getIdArray()),
		      "Data" => new ByteArray("Data", $section->getDataArray()),
		      "BlockLight" => new ByteArray("BlockLight", $section->getLightArray()),
		      "SkyLight" => new ByteArray("SkyLight", $section->getSkyLightArray())
		      ]);
    }

    $nbt->Biomes = new ByteArray("Biomes", $this->getBiomeIdArray());
    $nbt->BiomeColors = new IntArray("BiomeColors", $this->getBiomeColorArray());

    $nbt->HeightMap = new IntArray("HeightMap", $this->getHeightMapArray());

    $entities = [];

    foreach($this->getEntities() as $entity){
      if(!($entity instanceof Player) and !$entity->closed){
	$entity->saveNBT();
	$entities[] = $entity->namedtag;
      }
    }

    $nbt->Entities = new Enum("Entities", $entities);
    $nbt->Entities->setTagType(NBT::TAG_Compound);


    $tiles = [];
    foreach($this->getTiles() as $tile){
      $tile->saveNBT();
      $tiles[] = $tile->namedtag;
    }

    $nbt->TileEntities = new Enum("TileEntities", $tiles);
    $nbt->TileEntities->setTagType(NBT::TAG_Compound);
    $writer = new NBT(NBT::BIG_ENDIAN);
    $nbt->setName("Level");
    $writer->setData(new Compound("", ["Level" => $nbt]));

    return $writer->writeCompressed(ZLIB_ENCODING_DEFLATE, RegionLoader::$COMPRESSION_LEVEL);
  }

  public function getBlock($x, $y, $z) {
    $full = $this->sections[$y >> 4]->getFullBlock($x, $y & 0x0f, $z);
    $blockId = $full >> 4;
    $meta = $full & 0x0f;
    return [$blockId,$meta];
  }

  public function setBlock($x, $y, $z, $blockId = null, $meta = null){
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function getBlockSkyLight($x, $y, $z) {
    return $this->sections[$y >> 4]->getBlockSkyLight($x, $y & 0x0f, $z);
  }
  public function setBlockSkyLight($x, $y, $z, $level) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function getBlockLight($x, $y, $z) {
    return $this->sections[$y >> 4]->getBlockLight($x, $y & 0x0f, $z);

  }
  public function setBlockLight($x, $y, $z, $level) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
}