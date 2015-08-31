<?php
namespace aliuly\common\selectors;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;

/**
 * Implements @e command selector
 */
class AllEntity extends BaseSelector {
  static public function select(Server $srv, CommandSender $sender, array $args) {
    $result = [];
    foreach($srv->getLevels() as $l) {
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
