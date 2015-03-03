<?php

namespace alejandroliu\SignWarp;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Vector3;
use pocketmine\tile\Sign;
use pocketmine\event\block\SignChangeEvent;
/** Not currently used but may be later used  */
use pocketmine\level\Position;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\Item;
use pocketmine\tile\Tile;
use pocketmine\Player;

class Main extends PluginBase implements Listener {
  const MAX_COORD = 30000000;
  const MIN_COORD = -30000000;
  const MAX_HEIGHT = 128;
  const MIN_HEIGHT = 0;

  private $api, $server, $path;

  private function check_coords($line,array &$vec) {
    $mv = array();
    if (!preg_match('/^\s*(-?\d+)\s+(-?\d+)\s+(-?\d+)\s*$/',$line,$mv))
      return false;

    list($line,$x,$y,$z) = $mv;

    //$this->getLogger()->info("x=$x y=$y z=$z");

    if ($x <= self::MIN_COORD || $z <= self::MIN_COORD) return false;
    if ($x >= self::MAX_COORD || $z >= self::MAX_COORD) return false;
    if ($y <= self::MIN_HEIGHT || $y >= self::MAX_HEIGHT) return false;
    $vec = [$x,$y,$z];
    return true;
  }
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function playerBlockTouch(PlayerInteractEvent $event){
      if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
	$sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
	if(!($sign instanceof Sign)){
	  return;
	}
	$sign = $sign->getText();
	if($sign[0]=='[WARP]'){
	  if(empty($sign[1]) !== true){
	    $mv = [];
	    if ($this->check_coords($sign[1],$mv)) {
	      list($x,$y,$z) = $mv;
	      $event->getPlayer()->sendMessage("[SignWarp] Warping to $x,$y,$z");
	      $event->getPlayer()->teleport(new Vector3($x,$y,$z));
	    }else{
	      $event->getPlayer()->sendMessage("[SignWarp] Invalid coordinates ".$sign[1]);
	    }
	  }
	}
      }
    }

    /** Stuff for next update once SignChangeEvent is implemented */
    public function tileupdate(SignChangeEvent $event){
      if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
	//Server::getInstance()->broadcastMessage("lv1");
	$sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
	if(!($sign instanceof Sign)){
	  return true;
	}
	$sign = $event->getLines();
	if($sign[0]=='[WARP]'){
	  //Server::getInstance()->broadcastMessage("lv2");
	  if($event->getPlayer()->isOp()){
	    //Server::getInstance()->broadcastMessage("lv3");
	    if(empty($sign[1]) !==true){
	      $mv = array();
	      if ($this->check_coords($sign[1],$mv)) {
		$event->getPlayer()->sendMessage("[SignWarp] Warp to ".implode(',',$mv)." created");
		return true;
	      } else {
		$event->getPlayer()->sendMessage("[SignWarp] Invalid coordinates ".$sign[1]);
		$event->setLine(0,"[BROKEN]");
		return false;
	      }
	    }
	    $event->getPlayer()->sendMessage("[SignWarp] Warp coordinates not set");
	    //Server::getInstance()->broadcastMessage("f3");
	    $event->setLine(0,"[BROKEN]");
	    return false;
	  }
	  $event->getPlayer()->sendMessage("[SignWarp] You must be an OP to make a Warp");
	  //Server::getInstance()->broadcastMessage("f2");
	  $event->setLine(0,"[BROKEN]");
	  return false;
	}
      }
      return true;
    }

    public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
      switch ($cmd->getName()) {
      case "xyz":
	if ($sender instanceof Player) {
	  if ($sender->hasPermission("signwarp.cmd.xyz")) {
	    $pos = $sender->getPosition();
	    $sender->sendMessage("You are at ".$pos->getX().",".$pos->getY().",".$pos->getZ());
	  } else {
	    $sender->sendMessage("You do not have permission to do that.");
	  }
	} else {
	  $sender->sendMessage("[SignWarp] This command may only be used in-game");
	}
	return true;
      }
      return false;
    }

}
