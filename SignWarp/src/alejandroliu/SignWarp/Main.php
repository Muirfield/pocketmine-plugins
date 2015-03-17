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

  const SHORT_WARP = "[SWARP]";
  const LONG_WARP = "[WORLD]";

  protected $teleporters = [];

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

  private function shortWarp(PlayerInteractEvent $event,$sign){
    if(empty($sign[1])){
      $event->getPlayer()->sendMessage("[SignWarp] Missing coordinates");
      return;
    }
    $mv = [];
    if (!$this->check_coords($sign[1],$mv)) {
      $event->getPlayer()->sendMessage("[SignWarp] Invalid coordinates ".$sign[1]);
      return;
    }
    if(!$event->getPlayer()->hasPermission("signwarp.touch.sign")) {
      $event->getPlayer()->sendMessage("Nothing happens...");
      return;
    }
    list($x,$y,$z) = $mv;
    $this->teleporters[$event->getPlayer()->getName()] = time();
    $event->getPlayer()->sendMessage("Warping to $x,$y,$z...");
    $event->getPlayer()->teleport(new Vector3($x,$y,$z));
    Server::getInstance()->broadcastMessage($event->getPlayer()->getName()." teleported!");
  }
  private function longWarp(PlayerInteractEvent $event,$sign){
    if(empty($sign[1])){
      $event->getPlayer()->sendMessage("[SignWarp] Missing world name");
      return;
    }
    if (!$this->getServer()->isLevelGenerated($sign[1])) {
      $event->getPlayer()->sendMessage("[SignWarp] World \"".$sign[1]."\" does not exist!");
      return;
    }
    if(!$event->getPlayer()->hasPermission("signwarp.touch.sign")) {
      $event->getPlayer()->sendMessage("Nothing happens...");
      return;
    }
    $level = $sign[1];
    if (!$this->getServer()->isLevelLoaded($level)) {
      $event->getPlayer()->sendMessage("[SignWarp] Preparing world \"$level\"");
      if (!$this->getServer()->loadLevel($level)) {
	$event->getPlayer()->sendMessage("[SignWarp] Unable to load World \"$level\"");
	return;
      }
    }
    $mv = [];
    if ($this->check_coords($sign[2],$mv)) {
      list($x,$y,$z) = $mv;
      $mv = new Vector3($x,$y,$z);
    } else {
      $mv = null;
    }
    $event->getPlayer()->sendMessage("Teleporting...");

    $this->teleporters[$event->getPlayer()->getName()] = time();

    if (($mw = $this->getServer()->getPluginManager()->getPlugin("ManyWorlds"))
	!= null) {
      // Using ManyWorlds for teleporting...
      $mw->teleport($event->getPlayer(),$level,$mv);
    } else {
      $world = $this->getServer()->getLevelByName($level);
      $event->getPlayer()->teleport($world->getSafeSpawn($mv));
    }
    $this->getServer()->broadcastMessage($event->getPlayer()->getName()." teleported to $level");
  }
  public function onBlockPlace(BlockPlaceEvent $event){
    $name = $event->getPlayer()->getName();
    if (isset($this->teleporters[$name])) {
      if (time() - $this->teleporters[$name] < 2) $event->setCancelled();
    }
  }
  public function playerBlockTouch(PlayerInteractEvent $event){
    if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
      $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
      if(!($sign instanceof Sign)){
	return;
      }
      $sign = $sign->getText();

      // Check if the user is holding a sign and prevent teleports
      print_r($event->getItem());
      echo $event->getItem()->getID()."\n";
      if ($event->getItem()->getID() == 323) {
	if ($sign[0] == self::SHORT_WARP || $sign[0] == self::LONG_WARP) {
	  $event->getPlayer()->sendMessage("Can not teleport while holding a sign!");
	  return;
	}

	return;
      }
      if($sign[0]== self::SHORT_WARP){
	$this->shortWarp($event,$sign);
      } elseif ($sign[0]== self::LONG_WARP){
	$this->longWarp($event,$sign);
      }
    }
  }
  private function breakSign(SignChangeEvent $event,$msg) {
    $event->getPlayer()->sendMessage("[SignWarp] $msg");
    $event->setLine(0,"[BROKEN]");
    return false;
  }

  private function validateLongWarp(SignChangeEvent $event,$sign) {
    if(!$event->getPlayer()->hasPermission("signwarp.place.sign"))
      return $this->breakSign($event,"You are not allow to make Warp sign");
    if(empty($sign[1]) === true)
      return $this->breakSign($event,"World name not set");
    if (!$this->getServer()->isLevelGenerated($sign[1]))
      return $this->breakSign($event,"World \"".$sign[1]."\" does not exist!");
    $event->getPlayer()->sendMessage("[SignWarp] Portal to world \"".$sign[1]."\" created!");
    return true;
  }
  private function validateShortWarp(SignChangeEvent $event,$sign) {
    if(!$event->getPlayer()->isOp())
      return $this->breakSign($event,"You are not allow to make Warp sign");
    if(empty($sign[1]) === true)
      return $this->breakSign($event,"World name not set");
    $mv = array();
    if (!$this->check_coords($sign[1],$mv))
      return $this->breakSign($event,"Invalid coordinates ".$sign[1]);

    $event->getPlayer()->sendMessage("[SignWarp] Warp to ".implode(',',$mv)." created");
    return true;
  }

  public function tileupdate(SignChangeEvent $event){
    if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
      $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
      if(!($sign instanceof Sign)){
	return true;
      }
      $sign = $event->getLines();
      if($sign[0]==self::SHORT_WARP){
	return $this->validateShortWarp($event,$sign);
      } elseif($sign[0]==self::LONG_WARP){
	return $this->validateLongWarp($event,$sign);
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
	  $sender->sendMessage("You are at ".intval($pos->getX()).",".intval($pos->getY()).",".intval($pos->getZ()));
	} else {
	  $sender->sendMessage("[SignWarp] You do not have permission to do that.");
	}
      } else {
	$sender->sendMessage("[SignWarp] This command may only be used in-game");
      }
      return true;
    }
    return false;
  }

}
