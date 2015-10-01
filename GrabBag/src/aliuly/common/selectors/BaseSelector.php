<?php
namespace aliuly\common\selectors;

use pocketmine\command\CommandSender;
use pocketmine\Server;

/**
 * Base class for command selectors
 */
abstract class BaseSelector {
  /**
   * Main entry point for command selectors
   */
  static public function select(Server $srv, CommandSender $sender, array $args) {
    throw \RuntimeException("Unimplemented select");
  }
  /**
   * Implement selectors like m, name, etc...
   */
  static public function checkSelectors(array $args,CommandSender $sender, Entity $item) {
    foreach($args as $name => $value){
      switch($name){
        case "m":
          $mode = intval($value);
          if($mode === -1) break;
          // what is the point of adding this (in PC) when they can just safely leave this out?
          if(($item instanceof Player) && ($mode !== $item->getGamemode())) return false;
          break;
        case "name":
          if ($value{0} === "!") {
            if(substr($value,1) === strtolower($item->getName())) return false;
          } else {
            if($value !== strtolower($item->getName())) return false;
          }
          break;
        case "w":
          // Non standard
          if ($value{0} === "!") {
            if(substr($value,1) === strtolower($item->getLevel()->getName())) return false;
          } else {
            if($value !== strtolower($item->getLevel()->getName())) return false;
          }
          break;
        case "type":
          if ($item instanceof Player) {
            $type = "player";
          } else {
            $type = strtolower($item->getSaveId());
          }
          if ($value{0} === "!") {
            if(substr($value,1) === $type) return false;
          } else {
            if($value !== $type) return false;
          }
          break;
          // x,y,z
          // r,rm
          // c
          // dx,dy,dz
          // rx,rxm
          // ry,rym
      }
    }
    return true;
  }

}
