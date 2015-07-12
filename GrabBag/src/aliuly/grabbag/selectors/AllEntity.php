<?php
namespace aliuly\grabbag\selectors;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use aliuly\grabbag\CmdSelMgr as CmdSelModule;

class AllEntity implements BaseSelector {
  static public function select(CmdSelModule $owner, CommandSender $sender, array $args) {
    $result = [];
    foreach($owner->getServer()->getLevels() as $l) {
      foreach($l->getEntities() as $e) {
        if (count($args) && !$owner->checkSelectors($args,$sender,$e)) continue;
        if ($e instanceof Player) {
          $result[] = $e->getName();
        } else {
          $result[] = "e".$e->getId();
        }
      }
    }
    return $result;
  }
}
