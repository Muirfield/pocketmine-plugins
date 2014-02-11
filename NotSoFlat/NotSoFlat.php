<?php
/*
__PocketMine Plugin__
name=NotSoFlat
description=An Alternative World Generator
version=0.1
author=Alex
class=none
apiversion=11
*/

/**
 ** # META_NAME
 **
 ** * * *
 **
 **     META_NAME META_VERSION
 **     Copyright (C) 2013 Alejandro Liu  
 **     All Rights Reserved.
 **
 **     This program is free software: you can redistribute it and/or modify
 **     it under the terms of the GNU General Public License as published by
 **     the Free Software Foundation, either version 2 of the License, or
 **     (at your option) any later version.
 **
 **     This program is distributed in the hope that it will be useful,
 **     but WITHOUT ANY WARRANTY; without even the implied warranty of
 **     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 **     GNU General Public License for more details.
 **
 **     You should have received a copy of the GNU General Public License
 **     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **
 ** * * *
 **
 ** This is a simple PocketMine Terrain Generator.  It is based on the
 ** Superflat Generator, but mixes in the 
 ** [Diamond-Square](http://en.wikipedia.org/wiki/Diamond-square_algorithm)
 ** algorithm to generate decent looking terrain.
 **
 ** # Usage
 **
 ** Copy to your plugins directory to install.  Use `simpleworlds` to
 ** generate new worlds.
 **
 ** # Configuration
 **
 ** This is configured using the same _presets_ string as in `Superflat`
 ** block layer configuration.
 **
 ** Presets string:
 **
 **        version ; blocks ; biome ; options
 **
 ** It recognises the following options from superflat:
 **
 ** - spawn
 ** - decoration
 **
 ** In addition the following options are defined:
 **
 ** ## dsq
 **
 ** - min : minimum height of terrain
 ** - max : maximum height of terrain
 ** - water : water level
 ** - off : how quickly terrain will vary
 **
 ** # Changes
 **
 ** * 0.1 : Initial release
 **
 ** # TODO
 **
 ** - Add code to modify terrain based on biome settings.
 **   biome determine dsq values and topsoil blocks.
 ** - Add topsoil by lattitude and height
 ** - redo decoration (based on biome?)
 **
 ** # Known Issues
 **
 ** - `decorations.treecount` doesn't seem to work
 **
 **/

class NotSoFlat implements LevelGenerator{
  private $options;	// Generation options
  private $level;	// level being generated
  private $random;	// random functions
  private $populators;	// standard populators

  private $structure;	// terrain structure
  private $floorLevel;
  private $waterLevel;
  private $hmap;

  const PRESETS = "2;7,59x1,3x3,2;1;spawn(radius=10 block=89),dsq(min=60 max=120 water=80 off=20),decoration(treecount=80 grasscount=45)";
  const DSQ_SIZE = 257; // 2^8+1

  public function __construct(array $options = array()) {
    $this->options = $options;
    $this->populators = array();
    if (isset($options["preset"])) {
      $this->parsePreset($options["preset"]);
    } else {
      $this->parsePreset(self::PRESETS);
    }
    if(isset($this->options["decoration"])){
      $ores = new OrePopulator();
      $ores->setOreTypes(array(new OreType(new CoalOreBlock(), 20, 16, 0, 128),
			       new OreType(New IronOreBlock(), 20, 8, 0, 64),
			       new OreType(new RedstoneOreBlock(), 8, 7, 0, 16),
			       new OreType(new LapisOreBlock(), 1, 6, 0, 32),
			       new OreType(new GoldOreBlock(), 2, 8, 0, 32),
			       new OreType(new DiamondOreBlock(), 1, 7, 0, 16),
			       new OreType(new DirtBlock(), 20, 32, 0, 128),
			       new OreType(new GravelBlock(), 10, 16, 0, 128),
			       ));
      $this->populators[] = $ores;
    }
  }

  public function parsePreset($preset){
    $this->preset = $preset;

    // Make sense of preset line...
    $preset = explode(";", $preset);
    $version = (int) $preset[0];
    $blocks = @$preset[1];
    $biome = isset($preset[2]) ? $preset[2]:1;
    $options = isset($preset[3]) ? $preset[3]:"";

    // Parse block structure
    preg_match_all('#(([0-9]{0,})x?([0-9]{1,3}:?[0-9]{0,2})),?#', $blocks, $matches);
    $y = 0;
    $this->structure = array();
    $this->chunks = array();
    foreach($matches[3] as $i => $b){
      $b = BlockAPI::fromString($b);
      $cnt = $matches[2][$i] === "" ? 1:intval($matches[2][$i]);
      for($cY = $y, $y += $cnt; $cY < $y; ++$cY){
	$this->structure[$cY] = $b->getID();
      }
    }
    $this->floorLevel = $y;

    //////////////////////////////////////////////////////////////////////
    // additional options
    preg_match_all('#(([0-9a-z_]{1,})\(?([0-9a-z_ =:]{0,})\)?),?#', $options, $matches);
    foreach($matches[2] as $i => $option){
      $params = true;
      if($matches[3][$i] !== ""){
	$params = array();
	$p = explode(" ", $matches[3][$i]);
	foreach($p as $k){
	  $k = explode("=", $k);
	  if(isset($k[1])){
	    $params[$k[0]] = $k[1];
	  }
	}
      }
      $this->options[$option] = $params;
    }
  }

  //////////////////////////////////////////////////////////////////////
  //
  // These implement the Diamond-Square algorithm
  // (http://en.wikipedia.org/wiki/Diamond-square_algorithm)
  //
  //////////////////////////////////////////////////////////////////////

  function rnd() {
    //return floatval(mt_rand()) / floatval(mt_getrandmax());
    return $this->random->nextFloat();
  }

  function dsval(&$dat,$x,$y) {
    if (isset($dat[$x][$y])) return $dat[$x][$y];
    $count = 0;
    $sum = 0.0;
    foreach (array(array(0,0),array(0,-1),array(1,-1),array(1,0),array(1,1),
		   array(0,1), array(-1,1),array(-1,0),array(-1,-1)) as $r) {
      list($i,$j) = $r;
      if ($x+$i < 0 || $y+$j < 0) continue;
      if (isset($dat[$x+$i][$y+$j])) {
	$count++;
	$sum += $dat[$x+$i][$y+$j];
      }
    }
    if ($count) return $sum/$count;
    return NULL;
  }

  function DiamondSqr($seed,$sz,$h) {
    $dat = Array();
    $dat[0][0] = $dat[0][$sz-1] = $dat[$sz-1][0] = $dat[$sz-1][$sz-1] = $seed;

    for ($sideLen = $sz-1; $sideLen >= 2; $sideLen /= 2, $h /= 2.0) {
      $halfSide = $sideLen/2;
      for($x=0; $x<$sz-1;$x+=$sideLen) {
	for($y=0; $y<$sz-1; $y+=$sideLen) {

	  //x,y is upper left corner of the square
	  //calculate average of existing corners
	  $avg = ($this->dsval($dat,$x,$y) +				// tl
		  $this->dsval($dat,$x+$sideLen,$y) +			// tr
		  $this->dsval($dat,$x,$y+$sideLen) +			// ll
		  $this->dsval($dat,$x+$sideLen,$y+$sideLen))/4.0;	// lr

	  //center is average plus random offset in the range (-h, h)
	  $offset = ($this->rnd() * ($h+$h))-$h;
	  $dat[$x+$halfSide][$y+$halfSide] = $avg + $offset;
	} //for y
      } // for x

      //Generate the diamond values
      //Since diamonds are staggered, we only move x by half side
      //NOTE: if the data shouldn't wrap the x < DATA_SIZE and y < DATA_SIZE
      for ($x=0; $x<$sz; $x+=$halfSide) {
	for ($y=($x+$halfSide)%$sideLen; $y<$sz; $y+=$sideLen) {
	  //x,y is center of diamond
	  //we must use mod and add DATA_SIZE for subtraction 
	  //so that we can wrap around the array to find the corners

	  $avg = ($this->dsval($dat,($x-$halfSide+$sz)%$sz,$y) +	//lc
		  $this->dsval($dat,($x+$halfSide)%$sz,$y) +		//rc
		  $this->dsval($dat,$x,($y+$halfSide)%$sz,$y) +		//bc
		  $this->dsval($dat,$x,($y-$halfSide+$sz)%$sz))/4.0;	//ac

	  //new value = average plus random offset
	  //calc random value in the range (-h,+h)
	  $offset = ($this->rnd() * ($h+$h))-$h;
	  $dat[$x][$y] = $avg + $offset;

	  //wrap values on the edges
	  //remove this and adjust loop condition above
	  //for non-wrapping values
	  //if ($x == 0) $data[$DATA_SIZE-1][$y] = $avg;
	  //if ($y == 0) $data[$x][$DATA_SIZE-1] = $avg;
	} //for y
      } //for x
    } //for sideLength
    return $dat;
  }

  public function init(Level $level, Random $random){
    $this->level = $level;
    $this->random = $random;

    if (isset($this->options["dsq"])) {
      $dsq = $this->options["dsq"];
    } else {
      $dsq = array();
    }
    // Define some suitable defaults
    $min = isset($dsq["min"]) ? $dsq["min"] : 32;
    $max = isset($dsq["max"]) ? $dsq["max"] : 120;
    if ($this->dsq["min"] > $dsq["max"])
      list($this->dsq["min"],$this->dsq["max"]) =
	array($this->dsq["max"],$this->dsq["min"]);

    $this->waterLevel = isset($dsq["water"])? $dsq["water"]:$min+($max-$min)/3;
    $off = floatval(isset($dsq["off"]) ? $dsq["off"] : 100);

    console("[INFO]: Generating height map  (off=$off)");
    $this->hmap = $this->DiamondSqr(0.0, self::DSQ_SIZE,$off);

    // Determine min and max values...
    $maxh = $minh = $this->hmap[0][0];
    for ($x = 0 ; $x < self::DSQ_SIZE ; $x++) {
      for ($z = 0; $z < self::DSQ_SIZE ; $z++) {
	if ($this->hmap[$x][$z] > $maxh)
	  $maxh = $this->hmap[$x][$z];
	if ($this->hmap[$x][$z] < $minh)
	  $minh = $this->hmap[$x][$z];
      }
    }

    // Normalize the map
    console("[INFO] Normalizing map...");
    for ($x = 0 ; $x < self::DSQ_SIZE ; $x++) {
      for ($z = 0; $z < self::DSQ_SIZE ; $z++) {
	$this->hmap[$x][$z] =
	  intval(($this->hmap[$x][$z]-$minh)*($max-$min)/($maxh-$minh)+$min);
      }
    }

    // DEBUGGING
    $sum = $count = 0;
    $fp = fopen("x.dat","w");
    $maxh = $minh = $this->hmap[0][0];
    for ($x = 0 ; $x < self::DSQ_SIZE ; $x++) {
      for ($z = 0; $z < self::DSQ_SIZE ; $z++) {
	if ($this->hmap[$x][$z] > $maxh)
	  $maxh = $this->hmap[$x][$z];
	if ($this->hmap[$x][$z] < $minh)
	  $minh = $this->hmap[$x][$z];


	fwrite($fp,implode(" ",array($x,$z,$this->hmap[$x][$z]))."\n");
	$count++;
	$sum += $this->hmap[$x][$x];
      }
      fwrite($fp,"\n");
    }
    fclose($fp);
    console("[DEBUG] sum=$sum count=$count ".($sum/$count));
    console("[DEBUG] maxh=$maxh minh=$minh");
    console("[DEBUG] floorLevel: ".$this->floorLevel);
    console("[DEBUG] waterLevel: ".$this->waterLevel);
  }
  public function pickBlock($x,$y,$z) {
    $h = $this->hmap[$x][$z];
    if ($y > $h) {
      // This is above ground
      if ($y < $this->waterLevel) {
	return STILL_WATER;
      } else {
	return AIR;
      }
    }
    $b = intval($y * $this->floorLevel / $h);
    $q = $b;
    if ($b >= $this->floorLevel) $b = $this->floorLevel - 1;
    return $this->structure[$b];
  }

  public function generateChunk($chunkX, $chunkZ){
    $offX = $chunkX << 4;
    $offZ = $chunkZ << 4;

    for($Y = 0; $Y < 8; ++$Y){
      $chunk = "";
      $startY = $Y << 4;
      $endY = $startY + 16;
      for($z = 0; $z < 16; ++$z){
	for($x = 0; $x < 16; ++$x){
	  $blocks = "";
	  $metas = "";
	  for($y = $startY; $y < $endY; ++$y){
	    $blocks .= chr($this->pickBlock($x+$offX,$y,$z+$offZ));
	    $metas .= "0";
	  }
	  $chunk .= $blocks.Utils::hexToStr($metas)."\x00\x00\x00\x00\x00\x00\x00\x00";
	}
      }
      $this->level->setMiniChunk($chunkX, $chunkZ, $Y, $chunk);
    }
    return;
  }

  public function populateChunk($chunkX, $chunkZ){
    foreach($this->populators as $populator){
      $this->random->setSeed((int) ($chunkX * 0xdead + $chunkZ * 0xbeef) ^ $this->level->getSeed());
      $populator->populate($this->level, $chunkX, $chunkZ, $this->random);
    }
  }

  public function populateLevel(){
    $this->random->setSeed($this->level->getSeed());
    if(isset($this->options["spawn"])){
      $spawn = array(10, new SandstoneBlock());
      if(isset($this->options["spawn"]["radius"])){
	$spawn[0] = intval($this->options["spawn"]["radius"]);
      }
      if(isset($this->options["spawn"]["block"])){
	$spawn[1] = BlockAPI::fromString($this->options["spawn"]["block"])->getBlock();
	if(!($spawn[1] instanceof Block)){
	  $spawn[1] = new SandstoneBlock();
	}
      }

      $start = 128 - $spawn[0];
      $end = 128 + $spawn[0];
      $floor = $this->hmap[128][128];
      if ($floor < $this->waterLevel) {
	$floor = $this->waterLevel;
      }
      for($x = $start; $x <= $end; ++$x){
	for($z = $start; $z <= $end; ++$z){
	  if(floor(sqrt(pow($x - 128, 2) + pow($z - 128, 2))) <= $spawn[0]){
	    $y = $floor;
	    if (($this->level->level->getBlockID($x,$floor,$z)) === AIR) {
	      $y = $this->hmap[$x][$z];
	    }
	    $this->level->setBlockRaw(new Vector3($x, $y, $z), $spawn[1], null);
	  }
	}
      }
    }

    if(isset($this->options["decoration"])){
      $treecount = 80;
      $grasscount = 120;
      if(isset($this->options["decoration"]["treecount"])){
	$treecount = intval($this->options["decoration"]["treecount"]);
      }
      if(isset($this->options["decoration"]["grasscount"])){
	$grasscount = intval($this->options["decoration"]["grasscount"]);
      }
      for($t = 0; $t < $treecount; ++$t){
	$centerX = $this->random->nextRange(0, 255);
	$centerZ = $this->random->nextRange(0, 255);
	$centerY = $this->hmap[$centerX][$centerZ];
	// Don't grow things under water...
	if ($centerY < $this->waterLevel) continue;
	$down = $this->level->level->getBlockID($centerX,$centerY,$centerZ);

	console("[DEBUG] $t:($centerX,$centerY,$centerZ) BLOCK: $down ".Block::$class[$down]);

	if($down === DIRT or $down === GRASS or $down === FARMLAND){
	  TreeObject::growTree($this->level,
			       new Vector3($centerX, $centerY, $centerZ),
			       $this->random, $this->random->nextRange(0,3));
	}
      }
      for($t = 0; $t < $grasscount; ++$t){
	$centerX = $this->random->nextRange(0, 255);
	$centerZ = $this->random->nextRange(0, 255);
	$centerY = $this->hmap[$centerX][$centerZ];
	// Don't grow things under water...
	if ($centerY < $this->waterLevel) continue;

	$down = $this->level->level->getBlockID($centerX, $centerY, $centerZ);
	if($down === GRASS){
	  TallGrassObject::growGrass($this->level,
				     new Vector3($centerX, $centerY, $centerZ),
				     $this->random,
				     $this->random->nextRange(8, 40));
	}
      }
    }
  }

  public function getSpawn(){
    return new Vector3(128, $this->hmap[128][128]+1, 128);
  }
}
