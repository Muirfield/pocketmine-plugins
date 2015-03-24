<?php
namespace pocketmine\block;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\math\Vector3;

class Rails extends Transparent{
  const STRAIGHT_NS = 0;
  const STRAIGHT_EW = 1;
  const SLOPE_W = 2;
  const SLOPE_E = 3;
  const SLOPE_N = 4;
  const SLOPE_S = 5;
  const CURVE_SE = 6;
  const CURVE_SW = 7;
  const CURVE_NW = 8;
  const CURVE_NE = 9;
  const ABOVE = 8;

  protected $id = self::RAILS;
  public function __construct($meta = 0){
    $this->meta = $meta;
  }
  public function getHardness(){
    return 8;
  }
  public function getName(){
    return "Rails";
  }
  public function getBreakTime(Item $item){
    switch($item->isPickaxe()){
    case 5:
      return 0.1312;
    case 4:
      return 0.175;
    case 3:
      return 0.2625;
    case 2:
      return 0.0875;
    case 1:
      return 0.525;
    default:
      return 1.05;
    }
  }
  public function canBend() {
    return true;
  }
  public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
    $below = $this->getSide(Vector3::SIDE_DOWN);
    // Can not place above a transparent block
    if ($below->isTransparent() !== false) return false;

    $cube = [];
    $exits = 0;
    foreach ([Vector3::SIDE_NORTH,Vector3::SIDE_SOUTH,
	      Vector3::SIDE_EAST,Vector3::SIDE_WEST] as $d) {
      $cube[$d] = [$this->getSide($d),-1];
      $cube[-$d] =[$cube[$d]->getSide(Vector3::SIDE_DOWN),-1];
      $cube[$d+self::ABOVE] = [$cube[$d]->getSide(Vector3::SIDE_UP),-1];
    }
    foreach ($cube as $d => &$b) {
      if ($b[0] instanceof Rails) {
	$b[1] = $b[0]->getDamage();
	++$exits;
      }
    }
    /*
     * Trivial cases...
     */
    if ($exits == 0) {
      // No adjacent exists...
      $this->getLevel()->setBlock($this,$this,true,true);
      return true;
    }
    /*
     * Only ONE adjacent rail...
     */
    if ($exits == 1) {
      foreach ($cube as $d => &$b) {
	if ($b[0] instanceof Rails) {
	  $exit_dir = $d;
	  break;
	}
      }
      // Vector3::SIDE_NORTH;
      if ($exit_dir == Vector3::SIDE_NORTH) {
	// No need to modify...
	if (in_array($cube[Vector3::SIDE_NORTH][1],
		     [self::SLOPE_N, self::STRAIGHT_NS, self::CURVE_SE,
		      self::CURVE_SW])) {
	  $this->meta = self::STRAIGHT_NS;
	  $this->getLevel()->setBlock($this,$this,true,true);
	  return true;
	}
	// Need to modify...
	if ($cube[Vector3::SIDE_NORTH][0]->canChange()) {
	  if ($cube[Vector3::SIDE_NORTH][1] == self::STRAIGHT_EW) {
	    
	  }
	  if ($cube[Vector3::SIDE_NORTH][1] == self::SLOPE_S) {
	    
	  }
	  if ($cube[Vector3::SIDE_NORTH][1] == self::CURVE_NW) {
	  }
	  if ($cube[Vector3::SIDE_NORTH][1] == self::CURVE_NE) {
	  }
	}
	$this->getLevel()->setBlock($this,$this,true,true);
	return true;
      }

      Vector3::SIDE_SOUTH;
      Vector3::SIDE_WEST;
      Vector3::SIDE_EAST;
      Vector3::SIDE_NORTH+self::ABOVE;
      Vector3::SIDE_SOUTH+self::ABOVE;
      Vector3::SIDE_WEST+self::ABOVE;
      Vector3::SIDE_EAST+self::ABOVE;
      -Vector3::SIDE_NORTH;
      -Vector3::SIDE_SOUTH;
      -Vector3::SIDE_WEST;
      -Vector3::SIDE_EAST;

    }


      /*
       * Do the ones that do not require changes
       */
      if (in_array($cube[Vector3::SIDE_EAST][1],
		   [self::STRAIGHT_EW, self::SLOPE_E, self::CURVE_NW,
		    self::CURVE_SW]) ||
	  in_array($cube[Vector3::SIDE_WEST][1],
		   [self::STRAIGHT_EW, self::SLOPE_E

    //self::SLOPE_W;
    //self::SLOPE_E;
    //self::SLOPE_N;
    //self::SLOPE_S;
    //self::STRAIGHT_NS;
    //self::STRAIGHT_EW;
    //self::CURVE_SE;
    //self::CURVE_SW;
    //self::CURVE_NW;
    //self::CURVE_NE;

    }

    /*
     * figure out configuration that do not require changes
     * to adjacent blocks
     */
    if (in_array($cube[Vector3::SIDE_WEST+self::ABOVE][1],
		 [self::STRAIGHT_EW, self::SLOPE_W, self::CURVE_SE,
		  self::CURVE_NE]) &&
	(in_array($cube[Vector3::SIDE_EAST][1],
		  [self::STRAIGHT_EW,self::SLOPE_E,self::CURVE_SW,
		   self::CURVE_NW,-1]) ||
	 $cube[-Vector3::SIDE_WEST][1] == self::SLOPE_W)) {
      $this->meta = self::SLOPE_W;
      $this->getLevel()->setBlock($this,$this,true,true);
      return true;
    }

    if (in_array($cube[Vector3::SIDE_EAST+self::ABOVE][1],
		 [self::STRAIGHT_EW, self::SLOPE_E, self::CURVE_SW,
		  self::CURVE_NW]) &&
	(in_array($cube[Vector3::SIDE_WEST][1],
		  [self::STRAIGHT_EW,self::SLOPE_W,self::CURVE_SE,
		   self::CURVE_NE,-1]) ||
	 $cube[-Vector3::SIDE_WEST][1] == self::SLOPE_E)) {
      $this->meta = self::SLOPE_E;
      $this->getLevel()->setBlock($this,$this,true,true);
      return true;
    }

    if (in_array($cube[Vector3::SIDE_NORTH+self::ABOVE][1],
		 [self::STRAIGHT_NS, self::SLOPE_N, self::CURVE_SW,
		  self::CURVE_SE]) &&
	(in_array($cube[Vector3::SIDE_SOUTH][1],
		  [self::STRAIGHT_NS,self::SLOPE_N,self::CURVE_NW,
		   self::CURVE_NE,-1]) ||
	 $cube[-Vector3::SIDE_SOUTH][1] == self::SLOPE_N)) {
      $this->meta = self::SLOPE_N;
      $this->getLevel()->setBlock($this,$this,true,true);
      return true;
    }

    if (in_array($cube[Vector3::SIDE_SOUTH+self::ABOVE][1],
		 [self::STRAIGHT_NS, self::SLOPE_S, self::CURVE_NW,
		  self::CURVE_NE]) &&
	(in_array($cube[Vector3::SIDE_NORTH][1],
		  [self::STRAIGHT_NS,self::SLOPE_S,self::CURVE_SW,
		   self::CURVE_SE,-1]) ||
	 $cube[-Vector3::SIDE_SOUTH][1] == self::SLOPE_S)) {
      $this->meta = self::SLOPE_S;
      $this->getLevel()->setBlock($this,$this,true,true);
      return true;
    }

    if ((in_array($cube[Vector3::SIDE_NORTH][1],
		  [self::STRAIGHT_NS, self::SLOPE_N, self::CURVE_SW,
		   self::CURVE_SE]) ||
	 $cube[-Vector3::SIDE_NORTH][1] == self::SLOPE_S) &&
	(in_array($cube[Vector3::SIDE_SOUTH][1],
		  [self::STRAIGHT_NS, self::SLOPE_S, self::CURVE_NW,
		   self::CURVE_NE])||
	 $cube[-Vecto3::SIDE_SOUTH][1] == self::SLOPE_N)) {
      $this->meta = self::STRAIGHT_NS;
      $this->getLevel()->setBlock($this,$this,true,true);
      return true;
    }

    if ((in_array($cube[Vector3::SIDE_EAST][1],
		  [self::STRAIGHT_EW, self::SLOPE_E, self::CURVE_NW,
		   self::CURVE_SW]) ||
	 $cube[-Vector3::SIDE_EAST][1] == self::SLOPE_W) &&
	(in_array($cube[Vector3::SIDE_WEST][1],
		  [self::STRAIGHT_EW, self::SLOPE_W, self::CURVE_NE,
		   self::CURVE_SE]) ||
	 $cube[-Vecto3::SIDE_WEST][1] == self::SLOPE_E)) {
      $this->meta = self::STRAIGHT_EW;
      $this->getLevel()->setBlock($this,$this,true,true);
      return true;
    }

    //self::CURVE_NE;
    if ((in_array($cube[Vector3::SIDE_NORTH][1],
		  [self::STRAIGHT_NS, self::SLOPE_N, self::CURVE_SE,
		   self::CURVE_SW]) ||
	 $cube[-Vector3::SIDE_NORTH][1] == self::SLOPE_S) &&
	(in_array($cube[Vector3::SIDE_EAST][1],
		  [self::STRAIGHT_EW, self::SLOPE_E, self::CURVE_NW,
		   self::CURVEW_SW]) ||
	 $cube[-Vector3::SIDE_WEST][1] == self::SLOPE_W)) {
      $this->meta = self::CURVE_NE;
      $this->getLevel()->setBlock($this,$this,true,true);
      return true;
    }
    //self::CURVE_NW;
    if ((in_array($cube[Vector3::SIDE_NORTH][1],
		  [self::STRAIGHT_NS, self::SLOPE_N, self::CURVE_SE,
		   self::CURVE_SW]) ||
	 $cube[-Vector3::SIDE_NORTH][1] == self::SLOPE_S) &&
	(in_array($cube[Vector3::SIDE_WEST][1],
		  [self::STRAIGHT_EW, self::SLOPE_W, self::CURVE_NE,
		   self::CURVEW_SE]) ||
	 $cube[-Vector3::SIDE_WEST][1] == self::SLOPE_E)) {
      $this->meta = self::CURVE_NW;
      $this->getLevel()->setBlock($this,$this,true,true);
      return true;
    }
    //self::CURVE_SE;
    if ((in_array($cube[Vector3::SIDE_SOUTH][1],
		  [self::STRAIGHT_NS, self::SLOPE_S, self::CURVE_NE,
		   self::CURVE_NW]) ||
	 $cube[-Vector3::SIDE_SOUTH][1] == self::SLOPE_N) &&
	(in_array($cube[Vector3::SIDE_EAST][1],
		  [self::STRAIGHT_EW, self::SLOPE_E, self::CURVE_NW,
		   self::CURVE_SW]) ||
	 $cube[-Vector3::SIDE_WEST][1] == self::SLOPE_W)) {
      $this->meta = self::CURVE_SE;
      $this->getLevel()->setBlock($this,$this,true,true);
      return true;
    }
    //self::CURVE_SW;
    if ((in_array($cube[Vector3::SIDE_SOUTH][1],
		  [self::STRAIGHT_NS, self::SLOPE_S, self::CURVE_NE,
		   self::CURVE_NW]) ||
	 $cube[-Vector3::SIDE_SOUTH] == self::SLOPE_N) &&
	(in_array($cube[Vector3::SIDE_WEST][1],
		  [self::STRAIGHT_EW, self::SLOPE_W, self::CURVE_NE,
		   self::CURVE_SE]) ||
	 $cube[-Vector3::SIDE_WEST][1] == self::SLOPE_E)) {
      $this->meta = self::CURVE_SW;
      $this->getLevel()->setBlock($this,$this,true,true);
      return true;
    }

    /*
     * Check if we can modify adjacent blocks
     */
    //self::SLOPE_W;
    //self::SLOPE_E;
    //self::SLOPE_N;
    //self::SLOPE_S;
    //self::STRAIGHT_NS;
    //self::STRAIGHT_EW;
    //self::CURVE_SE;
    //self::CURVE_SW;
    //self::CURVE_NW;
    //self::CURVE_NE;

    /*
     * Check if we can 
     */
    //self::SLOPE_W;
    //self::SLOPE_E;
    //self::SLOPE_N;
    //self::SLOPE_S;
    //self::STRAIGHT_NS;
    //self::STRAIGHT_EW;
    //self::CURVE_SE;
    //self::CURVE_SW;
    //self::CURVE_NW;
    //self::CURVE_NE;


//////////////////////////////////////////////////////////////////////
    if (($cube[Vector3::SIDE_NORTH][1] != -1
	 || $cube[-Vector3::SIDE_NORTH][1] == self::SLOPE_S)
	&& ($cube[Vector3::SIDE_WEST][1] != -1
	    || $cube[-Vector3::SIDE_WEST][1] == self::SLOPE_E) {
	  if ((in_array($cube[Vector3::SIDE_NORTH][1],
		       [self::STRAIGHT_NS, self::SLOPE_N, self::CURVE_SE,
			self::CURVE_SW])
	       || $cube[-Vector3::SIDE_NORTH][1] == self::SLOPE_S)
	      && (in_array($cube[Vector3::SIDE_WEST

      self::STRAIGHT_EW;
      self::SLOPE_W;
      self::CURVE_SE;
      self::CURVE_NE;


      self::STRAIGHT_EW;
      self::SLOPE_E;
      self::SLOPE_S;
      self::CURVE_NW;
      self::CURVE_NE;

      $this->meta = self::CURVE_NW;
      $this->getLevel()->setBlock($this,$this,true,true);
      return true;
    }



    



    $cube[Vector3::SIDE_NORTH] = [$this->getSide(Vecto3::SIDE
	const SIDE_DOWN = 0;

	const SIDE_UP = 1;
	const SIDE_NORTH = 2;
	const SIDE_SOUTH = 3;
	const SIDE_WEST = 4;
	const SIDE_EAST = 5;

    $cube[Vector::SIDE_WEST] = [$this->getSide(Vector3::SIDE_WEST)];

			       



      self::STRAIGHT_EW;


    $west = $this->getSide(Vector3::SIDE_WEST);
    if ($west instanceof Rails) {
      $westmode = $west->getDamage();
      if ($westmode == self::STRAIGHT_EW || $westmode == self::SLOPE_W
	  || $westmode == self::CURVE_NE || $westmode == self::CURVE_SE) {
	$this->meta = self::STRAIGHT_EW;
	$this->getLevel()->setBlock($this,$this,true,true);
	return true;
      }
      if ($westmode == self::SLOPE_E) {
      }
      if ($westmode == self::STRAIGHT_NS) {
	// Bend it!
	$nw = $west->getSide(Vector3::SIDE_NORTH);
	if ($nw instanceof Rails) {
	  $nwmode = $nw->getDamage();
	  if ($nwmode == self::STRAIGHT_NS || $nwmode == self::CURVE_SE ||
	      $nwmode == self::CURVE_SW || $nwmode == self::SLOPE_N) {
	    $west->setDamage(self::CURVE_NE);
	    $this->meta = self::STRAIGHT_EW;
	    $this->getLevel()->setBlock($west,$west,true,true);
	    $this->getLevel()->setBlock($this,$this,true,true);
	    return true;
	  }
	}
	$sw = $west->getSide(Vector3::SIDE_SOUTH);
	if ($sw instanceof Rails) {
	  $swmode = $sw->getDamage();
	  if ($swmode == self::STRAIGHT_NS || $swmode == self::CURVE_NE ||
	      $swmode == self::CURVE_NW || $swmode == self::SLOPE_S) {
	    $west->setDamage(self::CURVE_SE);
	    $this->meta = self::STRAIGHT_EW;
	    $this->getLevel()->setBlock($west,$west,true,true);
	    $this->getLevel()->setBlock($this,$this,true,true);
	    return true;
	  }
	}
	$west->setDamage(self::STRAIGHT_EW);
	$this->meta = self::STRAIGHT_EW;
	$this->getLevel()->setBlock($west,$west,true,true);
	$this->getLevel()->setBlock($this,$this,true,true);
	return true;
      }
      if ($westmode == self::CURVE_SW || $westmode == self::CURVE_NW) {
	$ww = $west->getSide(Vector3::SIDE_WEST);
	if ($ww instanceof Rails) {
	  $wwmode = $ww->getDamage();
	  if ($wwmode == self::STRAIGHT_EW || $wwmode == self::SLOPE_W
	      || $wwmode == self::CURVE_SE || $wwmode == self::CURVE_NE) {
	    $west->setDamage(self::STRAIGHT_EW);
	    $this->meta = self::STRAIGHT_EW;
	    $this->getLevel()->setBlock($west,$west,true,true);
	    $this->getLevel()->setBlock($this,$this,true,true);
	    return true;
	  }
	}
      }
    }
    $westup = $west->getSide(Vector3::SIDE_UP);
    if ($westup instanceof Rails) {
      $wupmode = $westup->getDamage();
      if ($wupmode == self::STRAIGHT_EW || $wupmode == self::SLOPE_E
	  || $wupmode == self::CURVE_NE || $wupmode == self::CURVE_SE) {
	$this->meta = self::SLOPE_E;
	$this->getLevel()->setBlock($this,$this,true,true);
	return true;
      }
    }
    $westdn = $west->getSide(Vector3::SIDE_DOWN);
    if ($westdn instanceof Rails) {
      $wdnmode = $westdn->getDamage();
      if ($wdnmode == self::SLOPE_E) {
	$this->meta = self::STRAIGHT_EW;
	$this->getLevel()->setBlock($this,$this,true,true);
	return true;
      }
    }

    $east = $this->getSide(Vector3::SIDE_EAST);
    if ($east instanceof Rails) {
      $eastmode = $east->getDamage();
      if ($eastmode == self::STRAIGHT_EW || $eastmode == self::SLOPE_E
	  || $eastmode == self::CURVE_NW || $eastmode == self::CURVE_SW) {
	$this->meta = self::STRAIGHT_EW;
	$this->getLevel()->setBlock($this,$this,true,true);
	return true;
      }
      if ($eastmode == self::SLOPE_W) {
	$this->meta = self::STRAIGHT_EW;
	$this->getLevel()->setBlock($this,$this,true,true);
	return true;
      }
      if ($eastmode == self::STRAIGHT_NS) {
	// Bend it!
	$ne = $east->getSide(Vector3::SIDE_NORTH);
	if ($ne instanceof Rails) {
	  $nemode = $ne->getDamage();
	  if ($nemode == self::STRAIGHT_NS || $nemode == self::CURVE_SW ||
	      $nemode == self::CURVE_SE || $nemode == self::SLOPE_N) {
	    $east->setDamage(self::CURVE_NW);
	    $this->meta = self::STRAIGHT_EW;
	    $this->getLevel()->setBlock($east,$east,true,true);
	    $this->getLevel()->setBlock($this,$this,true,true);
	    return true;
	  }
	}
	$se = $east->getSide(Vector3::SIDE_SOUTH);
	if ($se instanceof Rails) {
	  $semode = $se->getDamage();
	  if ($semode == self::STRAIGHT_NS || $semode == self::CURVE_NW ||
	      $semode == self::CURVE_NE || $semode == self::SLOPE_S) {
	    $east->setDamage(self::CURVE_SW);
	    $this->meta = self::STRAIGHT_EW;
	    $this->getLevel()->setBlock($east,$east,true,true);
	    $this->getLevel()->setBlock($this,$this,true,true);
	    return true;
	  }
	}
	$east->setDamage(self::STRAIGHT_EW);
	$this->meta = self::STRAIGHT_EW;
	$this->getLevel()->setBlock($east,$east,true,true);
	$this->getLevel()->setBlock($this,$this,true,true);
	return true;
      }
      if ($eastmode == self::CURVE_SE || $eastmode == self::CURVE_NE) {
	$ee = $east->getSide(Vector3::SIDE_EAST);
	if ($ee instanceof Rails) {
	  $eemode = $ee->getDamage();
	  if ($eemode == self::STRAIGHT_EW || $eemode == self::SLOPE_E
	      || $eemode == self::CURVE_SW || $eemode == self::CURVE_NW) {
	    $east->setDamage(self::STRAIGHT_EW);
	    $this->meta = self::STRAIGHT_EW;
	    $this->getLevel()->setBlock($east,$east,true,true);
	    $this->getLevel()->setBlock($this,$this,true,true);
	    return true;
	  }
	}
      }
    }
    $eastup = $east->getSide(Vector3::SIDE_UP);
    if ($eastup instanceof Rails) {
      $eupmode = $eastup->getDamage();
      if ($eupmode == self::STRAIGHT_EW || $eupmode == self::SLOPE_W
	  || $eupmode == self::CURVE_NW || $eupmode == self::CURVE_SW) {
	$this->meta = self::SLOPE_W;
	$this->getLevel()->setBlock($this,$this,true,true);
	return true;
      }
    }
    $eastdn = $east->getSide(Vector3::SIDE_DOWN);
    if ($eastdn instanceof Rails) {
      $wdnmode = $eastdn->getDamage();
      if ($wdnmode == self::SLOPE_W) {
	$this->meta = self::STRAIGHT_EW;
	$this->getLevel()->setBlock($this,$this,true,true);
	return true;
      }
    }
    /*

      self::STRAIGHT_NS;
      self::STRAIGHT_EW;
      self::STRAIGHT_EW;
      self::SLOPE_W;
      self::SLOPE_E;
      self::SLOPE_N;
      self::SLOPE_S;
      self::CURVE_SE;
      self::CURVE_SW;
      self::CURVE_NW;
      self::CURVE_NE;
    */
    

    //print_r([$item,$block,$target,$face,$fx,$fy,$fz,$player]);
    return parent::place($item,$block,$target,$face,$fx,$fy,$fz,$player);
    //return true;
  }

  /*
  public function canConnect(Block $block){
    print_r($block);
    return (!($block instanceof Rails) and !($block instanceof PoweredRails)) ? $block->isSolid() : true;
    }*/


}
