<?php
namespace pmimporter;
use pmimporter\Blocks;
use pmimporter\Entities;

abstract class Copier {
  static public function copyChunk(&$src,&$dst) {
    $dst->setPopulated($src->isPopulated());
    $dst->setGenerated($dst->isGenerated());

    //
    // Copy blocks
    //
    for ($x = 0;$x < 16;$x++) {
      for ($z=0;$z < 16;$z++) {
	for ($y=0;$y < 128;$y++) {
	  list($id,$meta) = $src->getBlock($x,$y,$z);
	  // if ($id !== Blocks::xlateBlock($id)) ++$converted;
	  $id = Blocks::xlateBlock($id);
	  $dst->setBlock($x,$y,$z,$id,$meta);
	  $dst->setBlockSkyLight($x,$y,$z,$src->getBlockSkyLight($x,$y,$z));
	  $dst->setBlockLight($x,$y,$z,$src->getBlockLight($x,$y,$z));
	}
	$dst->setBiomeId($x,$z,$src->getBiomeId($x,$z));
      }
    }

    //
    // Copy data arrays
    //
    $heights = $src->getHeightMapArray();
    foreach ($heights as $off => $y) {
      $x = $off & 0xf;
      $z = $off >> 4;
      $dst->setHeightMap($x,$z,$y);
    }
    $colors = $src->getBiomeColorArray();
    foreach ($colors as $off => $color) {
      $x = $off & 0xf;
      $z = $off >> 4;
      $color = $color & 0xFFFFFF;
      list($r,$g,$b) = [$color >> 16, ($color >> 8) & 0xFF, $color & 0xFF];
      $dst->setBiomeColor($x,$z,$r,$g,$b);
    }


    // Copy Entities
    $entities = [];
    foreach ($src->getEntities() as $entity) {
      if (!isset($entity->id)) continue;
      if (Entities::getId($entity->id->getValue()) === null) continue;
      $entities[] = clone $entity;
      $dst->setEntities($entities);
    }
    // Copy tiles
    $tiles = [];
    foreach ($src->getTileEntities() as $tile) {
      if (!isset($tile->id)) continue;
      if (Blocks::getTileId($tile->id->getValue()) === null) continue;
      $tiles[] = clone $tile;
      $dst->setTileEntities($tiles);
    }
  }

  static public function copyRegion($region,&$srcfmt,&$dstfmt,$cb=null) {
    list($rX,$rZ) = $region;
    if (is_callable($cb)) call_user_func($cb,"CopyRegionStart","$rX,$rZ");

    $srcregion = $srcfmt->getRegion($rX,$rZ);
    $dstregion = $dstfmt->getRegion($rX,$rZ);

    for ($oX = 0; $oX < 32; $oX++) {
      $cX = $rX * 32 + $oX;
      for ($oZ = 0; $oZ < 32 ; $oZ++) {
	$cZ = $rZ * 32 + $oZ;
	if ($srcregion->chunkExists($oX,$oZ)) {
	  $srcchunk = $srcregion->readChunk($oX,$oZ);
	  if ($srcchunk->isPopulated() || $srcchunk->isGenerated()) {
	    $dstchunk = $dstregion->newChunk($cX,$cZ);
	    if (is_callable($cb)) call_user_func($cb,"CopyChunk","$cX,$cZ");
	    self::copyChunk($srcchunk,$dstchunk);
	    $dstregion->writeChunk($oX,$oZ,$dstchunk);
	  }
	}
      }
    }
    $dstregion->close();
    $srcregion->close();
    if (is_callable($cb)) call_user_func($cb,"CopyRegionDone","$rX,$rZ");
  }
}
