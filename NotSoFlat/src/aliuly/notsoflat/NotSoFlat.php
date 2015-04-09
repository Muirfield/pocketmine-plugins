<?php
/*
__PocketMine Plugin__
name=NotSoFlat
description=An Alternative World Generator
version=0.3
author=Alex
class=none
apiversion=11,12
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
 ** This is a simple [PocketMine-MP][3] Terrain Generator.  It is based on the
 ** Superflat Generator, but mixes in the [Diamond-Square][1] algorithm
 ** to generate decent looking terrain.
 **
 ** # Usage
 **
 ** Copy to your plugins directory to install.  Use `simpleworlds` to
 ** generate new worlds.
 **
 ** If using `simpleworlds` use the command:
 **
 **        swg 314 NotSoFlat myworld
 **
 ** `314` is the seed, and you can change it to any number you want.
 ** `myworld` is the new world being created, replace as appropriate.
 **
 ** # Configuration
 **
 ** You can configure the generator using the presets string.
 ** The presets strings can be configured in your `server.properties`
 ** file under the key `generator-settings`.
 **
 ** You can either use a predefined config or use a preset string similar
 ** to the one used by [Superflat][2]
 **
 ** The following are the available pre-defined config preset strints:
 **
 ** - overworld
 ** - plains
 ** - ocean
 ** - hills
 ** - mountains
 ** - flatland
 ** - hell
 ** - desert
 ** - mesa
 ** - desert hills
 **
 ** It is possible to control de terrain generation with greater
 ** detail using a preset configuration string:
 **
 **        nsfv1 ; blocks ; biome ; options
 **
 ** Where:
 **
 ** - nsfv1 : it is a preset version string. It is ignored.
 ** - blocks : it is an extended block preset configuration.  You can
 **   especify one or more strata structures by using a "`:`" as
 **   separator.  You can also use a predefined string.  Predefined
 **   block strings:
 **   - temperate : mostly grassy tile set
 **   - arid : a harsh, mostly desert tile set
 **   - hell : nettherrack all around
 ** - biome : this is ignored
 ** - options : additional settings to tweak the terrain generator.
 **
 ** Because some of the code is based on PocketMine-MP Superflat
 ** generator, it recognizes the following options:
 **
 ** - spawn : This will create a circle around the spawn using the
 **   specified block.  The following sub-options are used:
 **   - radius : size of the spawn circle
 **   - block : block type.
 ** - decoration : Will decorate the terrain with the specified objects:
 **   - grasscount : tall grass or flowers
 **   - treecount : trees
 **   - desertplant : _This is an extension to the Superflat generator_  
 **     Place cacti or weeds on top of sand blocks.
 **
 ** The terrain generation itself can be configured using the `dsq` option.  
 ** _(This is an extension to the Superflat generator)_
 **
 ** ## dsq
 **
 ** - min : minimum height of terrain
 ** - max : maximum height of terrain
 ** - water : water level
 ** - waterblock : What block to use instead of water.  Could be used
 **   configure a lava (Block 11) sea or an ice sea.
 ** - off : how quickly terrain will vary
 ** - strata : A list of `:` (colon) separated numbers used to select
 **   the different strata structures.
 ** - dotsz : averages the terrain so it is not as rough.
 ** - fn : Applies a function to the height map.  Available functions:
 **   - exp : Exponential.
 **   - linear : This doesn't do anything
 ** - fndat : Values to pass to the `fn` function.
 ** - hell : If set, it will create a roof of Netherrack and Bedrock at
 **   the top of the world.
 **
 ** # Off-line World Generator
 **
 ** __For Advanced users only__
 **
 ** For convenience (mostly mine) I hacked together a basic world
 ** generator script that can be run from outside [PocketMine-MP][3].
 ** It can be found [here][4].
 **
 ** To install you must copy it to your PocketMine-MP directory (at the same
 ** place you have your `start.sh` script).
 **
 ** To use, you must enter at the command-line in the PocketMin_MP directory:
 **
 **        bin/php5/bin/php -I plugins/NotSoFlat.php --preset=plains 314 NotSoFlat myworld
 **
 ** Where:
 **
 ** - `plugins/NotSoFlat.php` : is the plugin file to load (the `-I` options
 **   multiple times if you need to load mulitple plugins)
 ** - `--preset=plains` : `generator-string` to use.  You can use it to specify
 **   different presets without having to modify your `server.properties` file.
 ** - `314` : Is the seed.  You can use any number you want.
 ** - `NotSoFlat` : The name of this generator.
 ** - `myworld` : The name of the new world to generate.
 **
 ** # Example Presets
 **
 ** - `plains`  
 **    Generate a _plain_ looking terrain.
 ** - `nsfv1;temperate;0;spawn(radius=10 block=24),dsq(min=24 max=90 water=60 strata=30:54:60:74:80 dotsz=0.8),decoration(treecount=80 grasscount=45 desertplant=0)`  
 **    Generate a _plain_ looking terrain.
 ** - `nsfv1;temperate;1;spawn(radius=10 block=48),dsq(min=24 max=90 water=30 strata=25:27:29:32:55 dotsz=1.0 fn=exp fdat=2),decoration(treecount=80 grasscount=45 desertplant=0)`  
 **    Mountain terrain
 ** - `nsfv1;7,59x1,3x3,12:7,59x1,3x3,2;1;spawn(radius=10 block=48),dsq(min=53 max=57 strata=55 water=55 dotsz=0.7),decoration(treecount=100 grasscount=100 desertplant=0)`  
 **    A flatland.
 ** - `nsfv1;hell;8;spawn(radius=10 block=89),dsq(min=40 max=80 water=55 waterblock=11 fn=exp fndat=1.7 hell=1)`  
 **    A possible Netherworld.
 ** - `nsfv1;arid;2;spawn(radius=10 block=24),dsq(min=50 max=70 strata=1:2:52:65:68 water=51 dotsz=0.8),decoration(desertplant=80)`  
 **    Desert world
 ** - `nsfv1;temperate;1;spawn(radius=10 block=48),dsq(min=24 max=90 water=30 strata=25:27:29:32:60 dotsz=0.9 fn=exp fdat=0.5),decoration(treecount=80 grasscount=45 desertplant=0)`  
 **    A mesa with sheer cliffs.
 **
 ** # References
 **
 ** - [Diamond-Square Algorithm][1]
 ** - [Minecraft Superflat generator][2]
 ** - [PocketMine-MP][3]
 ** - [server.properties settings][7]
 ** - [World Generation Script][4]
 ** - [NotSoFlat github page][5]
 ** - [NotSoFlat plugin page][6]
 **
 ** [1]: http://en.wikipedia.org/wiki/Diamond-square_algorithm "Wikipedia"
 ** [2]: http://minecraft.gamepedia.com/Superflat "Superflat Generator"
 ** [3]: http://www.pocketmine.net/ "PocketMine-MP"
 ** [4]: https://raw.github.com/alejandroliu/pocketmine-plugins/master/scripts/GenWorld.php "GenWorld script"
 ** [5]: https://github.com/alejandroliu/pocketmine-plugins/tree/master/NotSoFlat "GitHub page"
 ** [6]: http://forums.pocketmine.net/plugins/notsoflat.385/ "PocketMine-MP Plugins page"
 ** [7]: https://github.com/PocketMine/PocketMine-MP/wiki/server.properties "Server Properties"
 **
 ** # Changes
 **
 ** * 0.1 : Initial release
 ** * 0.2 : Updates
 **   - Updated API level
 **   - Misc typos/bug-fixes
 **   - Fixed tree generation
 **   - tweaked defaults a bit
 ** * 0.3 : New features
 **   - multiple ground configs depending of height
 **   - cactus and weeds generation on sand
 **   - fn and dotsz settings to tweak the look of the environment
 **   - richer set of presets
 **
 ** # TODO
 **
 ** - Add snow depending on temperature/height
 **   - Create a temperature map (using dsq). With seed corners at the north
 **     colder than seed corners at the south
 **   - In pickBlock if $y > $h && $y == $waterlevel && tempmap is cold we
 **     place ice
 **   - In pickBlock if $y == $h+1 and tempmap (+ height) is cold, we add
 **     snow-cover block
 **
 ** # Known Issues
 **
 ** - terrain can be somewhat cracked
 **
 **/

class NotSoFlat implements LevelGenerator{
  private $options;	// Generation options
  private $level;	// level being generated
  private $random;	// random functions
  private $populators;	// standard populators

  private $structure;	// terrain structure
  private $strata;

  private $waterLevel;  // Water tables
  private $waterBlock;

  private $hmap;	// Height map
  private $dotsz;
  private $dotoff;

  const DSQ_SIZE = 257; // 2^8+1

  public function __construct(array $options = array()) {
    $this->options = $options;
  }

  public function parsePreset($preset){
    $PRESETS=
      array("overworld" => "nsfv1;7,59x1,3x3,2;1;". //0
	    "spawn(radius=10 block=89),". 	// Glowstones
	    "dsq(min=50 max=85 water=65 off=100),".
	    "decoration(treecount=80 grasscount=45)",
	    "plains" => "nsfv1;temperate;1;". 	//1
	    'spawn(radius=10 block=48),'.	// Cobblestones...
	    "dsq(min=50 max=70 strata=50:52:54:56:70 water=56 dotsz=0.6),".
	    "decoration(treecount=80 grasscount=45 desertplant=0)",
	    "ocean" => "nsfv1;temperate;0;".	//2
	    'spawn(radius=10 block=24),'.	// sandstones
	    "dsq(min=24 max=90 water=60 strata=30:54:60:74:80 dotsz=0.8),".
	    "decoration(treecount=80 grasscount=45 desertplant=0)",
	    "hills" => "nsfv1;temperate;1;".	//3
	    'spawn(radius=10 block=48),'.	// Cobblestones...
	    "dsq(min=24 max=90 water=30 strata=25:27:29:32:80 dotsz=0.7),".
	    "decoration(treecount=80 grasscount=45 desertplant=0)",
	    "mountains" => "nsfv1;temperate;1;".//4
	    'spawn(radius=10 block=48),'.	// Cobblestones...
	    "dsq(min=24 max=90 water=30 strata=25:27:29:32:55 dotsz=1.0 fn=exp fdat=2),".
	    "decoration(treecount=80 grasscount=45 desertplant=0)",
	    "flatland" => "nsfv1;7,59x1,3x3,12:7,59x1,3x3,2;1;".
	    'spawn(radius=10 block=48),'.	// Cobblestones...
	    "dsq(min=53 max=57 strata=55 water=55 dotsz=0.7),".
	    "decoration(treecount=100 grasscount=100 desertplant=0)",
	    "hell" => "nsfv1;hell;8;".		//6
	    'spawn(radius=10 block=89),'.	// glowstone
	    "dsq(min=40 max=80 water=55 waterblock=11 fn=exp fndat=1.7 hell=1)",
	    "desert" => "nsfv1;arid;2;".	//7
	    'spawn(radius=10 block=24),'.	// Cobblestones...
	    "dsq(min=50 max=70 strata=1:2:52:65:68 water=51 dotsz=0.8),".
	    "decoration(desertplant=80)",
	    "mesa" => "nsfv1;temperate;1;".	//8
	    'spawn(radius=10 block=48),'.	// Cobblestones...
	    "dsq(min=24 max=90 water=30 strata=25:27:29:32:60 dotsz=0.9 fn=exp fdat=0.5),".
	    "decoration(treecount=80 grasscount=45 desertplant=0)",
	    "desert hills" => "nsfv1;arid;2;".	//9
	    'spawn(radius=10 block=24),'.	// Sandstone
	    "dsq(min=30 max=80 strata=1:2:32:60:68 water=32 dotsz=0.8 fn=exp fndat=1.5),".
	    "decoration(desertplant=45)",
	    );
    $STRATA=array("temperate" =>
		  '7,59x1,3x3,2x13:'.	// Bottom ocean GRAVEL
		  '7,59x1,5x3:'.	// Deep ocean DIRT
		  '7,59x1,3x3,2x12:'.	// Shallow water SAND
		  '7,59x1,5x3,2x24,3x12:'.	// Beach SAND
		  '7,59x1,3x3,2,12:'.	// Plains GRASS
		  '7,59x1,3x4,1',	// High mountains STONE
		  "arid" =>
		  '7,59x1,3x3,2x13:'.	// Bottom ocean GRAVEL
		  '7,59x1,5x3:'.		// Deep ocean DIRT
		  '7,59x1,3x3,2x12:'.	// Shallow water SAND
		  '7,3x1,52x24,8x12:'.	// Ground SAND
		  '7,30x1,30x24:'.		// Mountains SANDSTONE
		  '7,30x1,30x13',		// Mountain peaks GRAVEL
		  "hell" =>
		  '7,2x87,10x11,20x87');	// Netherrack

    if (!isset($preset)) {
      $ids = array_keys($PRESETS);
      $preset = $ids[intval($this->rnd()*count($ids))];
      unset($ids);
    }
    console("[DEBUG] preset=$preset");
    $this->preset = $preset;
    if (isset($PRESETS[$preset])) $preset = $PRESETS[$preset];

    // Make sense of preset line...
    $preset = explode(";", $preset);
    $version = (int) $preset[0];
    $blocks = @$preset[1];
    if (isset($STRATA[$blocks])) $blocks = $STRATA[$blocks];
    $biome = isset($preset[2]) ? $preset[2]:1;
    $options = isset($preset[3]) ? $preset[3]:"";

    // Parse block structure
    $this->structure = array();
    $j = 0;
    foreach (explode(":",$blocks) as $blkset) {
      $matches = array();
      preg_match_all('#(([0-9]{0,})x?([0-9]{1,3}:?[0-9]{0,2})),?#',
		     $blkset, $matches);
      $y = 0;
      $this->structure[$j] = array();

      foreach($matches[3] as $i => $b){
	$b = BlockAPI::fromString($b);
	$cnt = $matches[2][$i] === "" ? 1:intval($matches[2][$i]);
	for($cY = $y, $y += $cnt; $cY < $y; ++$cY){
	  $this->structure[$j][$cY] = $b->getID();
	}
      }
      ++$j;
    }

    //////////////////////////////////////////////////////////////////////
    // additional options
    preg_match_all('#(([0-9a-z_]{1,})\(?([0-9a-z_ =:\.]{0,})\)?),?#', $options, $matches);
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
    if (is_array($seed)) {
      list($dat[0][0],$dat[0][$sz-1],$dat[$sz-1][0],$dat[$sz-1][$sz-1]) = $seed;
    } else {
      $dat[0][0] = $dat[0][$sz-1] = $dat[$sz-1][0] = $dat[$sz-1][$sz-1] = $seed;
    }

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

    // Moved from constructor to here...
    $this->populators = array();
    $this->parsePreset(@$this->options["preset"]);

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

    if (isset($this->options["dsq"])) {
      $dsq = $this->options["dsq"];
    } else {
      $dsq = array();
    }
    // Define some suitable defaults
    $min = isset($dsq["min"]) ? intval($dsq["min"]) : 32;
    $max = isset($dsq["max"]) ? intval($dsq["max"]): 120;
    if ($min > $max) {
      list($min,$max) = array($max,$min);
    }

    $this->waterLevel = isset($dsq["water"])?
      intval($dsq["water"]):$min+($max-$min)/3;
    $off = floatval(isset($dsq["off"]) ? intval($dsq["off"]) : 100);
    $this->waterBlock = isset($dsq["waterblock"]) ?
      intval($dsq["waterblock"]) : STILL_WATER;
    $this->dotsz = floatval(isset($dsq["dotsz"]) ? $dsq["dotsz"] : 0.9);
    $this->dotoff= array('x'=>intval((1.0-$this->dotsz)*$this->rnd()
				     *self::DSQ_SIZE),
			 'y'=>intval((1.0-$this->dotsz)*$this->rnd()
				     *self::DSQ_SIZE));

    $fn = isset($dsq["fn"]) ? $dsq["fn"] : "linear";
    $fndat = explode(":",isset($dsq["fdat"]) ? $dsq["fdat"] : 1);

    // Strata map...
    $this->strata = array();
    if (isset($dsq["strata"])) {
      $strata = explode(":",$dsq["strata"]);
    } else {
      $strata = array();
    }
    $i = 0;
    for ($y=0;$y < 128 ; ++$y) {
      if (isset($strata[$i])) {
	if ($strata[$i] > $y) {
	  $this->strata[$y] = $i;
	} else {
	  $this->strata[$y] = ++$i;
	}
      } else {
	$this->strata[$y] = $i;
      }
    }
    // DEBUG
  /*
    file_put_contents("strata.txt",
		      print_r($strata,true)."\n".
		      print_r($this->strata,true));*/


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
	  call_user_func(array($this,"normalize_$fn"),
	       $this->hmap[$x][$z],$maxh,$minh,$max,$min,$fndat);
      }
    }

    /*
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
    console("[DEBUG] waterLevel: ".$this->waterLevel);
    */
  }
  public function normalize_linear($hval,$maxh,$minh,$max,$min,$fndat) {
    return intval(($hval-$minh)*($max-$min)/($maxh-$minh)+$min);
  }
  public function normalize_exp($hval,$maxh,$minh,$max,$min,$fndat) {
    return intval(pow((floatval($hval)-$minh)/($maxh-$minh),$fndat[0])
		  *($max-$min)+$min);
  }

  public function hmap($x,$z) {
    $adjx = intval($x*$this->dotsz)+$this->dotoff['x'];
    $adjy = intval($z*$this->dotsz)+$this->dotoff['y'];
    return $this->hmap[$adjx][$adjy];
  }

  public function pickBlock($x,$y,$z) {
    if (isset($this->options["dsq"]["hell"])) {
      if ($y > 126) return BEDROCK;
      if ($y > 125) return NETHERRACK;
    }

    $h = $this->hmap($x,$z);
    if ($y > $h) {
      // This is above ground
      if ($y < $this->waterLevel) {
	return $this->waterBlock;
      } else {
	return AIR;
      }
    }

    if (!isset($this->structure[$this->strata[$h]])) return DIRT;
    $structure = $this->structure[$this->strata[$h]];

    $floorLevel = count($structure);

    $b = intval($y * ($floorLevel-1) / $h);
    $q = $b;
    if ($b > $floorLevel) $b = $floorLevel - 1;
    return $structure[$b];
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
    //return;    //DEBUG
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
      $floor = $this->hmap(128,128);
      if ($floor < $this->waterLevel) {
	$floor = $this->waterLevel;
      }
      for($x = $start; $x <= $end; ++$x){
	for($z = $start; $z <= $end; ++$z){
	  if(floor(sqrt(pow($x - 128, 2) + pow($z - 128, 2))) <= $spawn[0]){
	    $y = $this->hmap($x,$z);
	    if ($y < $this->waterLevel) {
	      $y = $this->waterLevel;
	    } else if ($y > $floor) {
	      $y = $floor;
	    }
	    $this->level->setBlockRaw(new Vector3($x, $y, $z), $spawn[1], null);
	  }
	}
      }
    }

    if(isset($this->options["decoration"])){
      $treecount = 80;
      $grasscount = 120;
      $desertplant = 0;
      if(isset($this->options["decoration"]["treecount"])){
	$treecount = intval($this->options["decoration"]["treecount"]);
      }
      if(isset($this->options["decoration"]["grasscount"])){
	$grasscount = intval($this->options["decoration"]["grasscount"]);
      }
      if(isset($this->options["decoration"]["desertplant"])){
	$desertplant = intval($this->options["decoration"]["desertplant"]);
      }
      for($t = 0; $t < $treecount; ++$t){
	$centerX = $this->random->nextRange(0, 255);
	$centerZ = $this->random->nextRange(0, 255);
	$centerY = $this->hmap($centerX,$centerZ)+1;
	// Don't grow things under water...
	if ($centerY < $this->waterLevel) continue;
	$down = $this->level->level->getBlockID($centerX,$centerY-1,$centerZ);
	if($down === DIRT or $down === GRASS or $down === FARMLAND){
	  TreeObject::growTree($this->level,
			       new Vector3($centerX, $centerY, $centerZ),
			       $this->random, $this->random->nextRange(0,3));
	}
      }
      for($t = 0; $t < $grasscount; ++$t){
	$centerX = $this->random->nextRange(0, 255);
	$centerZ = $this->random->nextRange(0, 255);
	$centerY = $this->hmap($centerX,$centerZ);
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
      if ($desertplant) {
	$cactus = BlockAPI::get(CACTUS,1);
	$grass = BlockAPI::get(TALL_GRASS,1);

	for($t = 0; $t < $desertplant; ++$t){
	  $centerX = $this->random->nextRange(0, 255);
	  $centerZ = $this->random->nextRange(0, 255);
	  $centerY = $this->hmap($centerX,$centerZ);
	  // Don't grow things under water...
	  if ($centerY < $this->waterLevel) continue;

	  $down = $this->level->level->getBlockID($centerX, $centerY, $centerZ);
	  if ($down === SAND) {
	    // Grow a cactus... 
	    if ($this->rnd() > 0.7) {
	      $this->level->setBlockRaw(new Vector3($centerX,$centerY+1,$centerZ),
					$grass);
	    } else {
	      $this->level->setBlockRaw(new Vector3($centerX,$centerY+1,$centerZ),
					$cactus);
	      if ($this->rnd() > 0.3) {
		$this->level->setBlockRaw(new Vector3($centerX,$centerY+2,$centerZ),
					  $cactus);
	      }
	    }
	  } // if SAND
	} // for ($t)
      } // if ($desertplant)
    }
  }

  public function getSpawn(){
    return new Vector3(128, $this->hmap(128,128)+2, 128);
  }
}
