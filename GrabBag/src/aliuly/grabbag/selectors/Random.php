<?php
namespace aliuly\grabbag\selectors;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use aliuly\grabbag\CmdSelMgr as CmdSelModule;

class Random implements BaseSelector {
  static public function select(CmdSelModule $owner, CommandSender $sender, array $args) {
    $result = [];

    if (!isset($args["type"])) $args["type"] = "player";

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
    if (!isset($args["c"])) $args["c"] = 1;
    $c = [];
    $n = intval($args["c"]);
    while ($n-- > 0 && count($result)) {
      $i = array_rand($result);
      $c[] = $result[$i];
      unset($result[$i]);
    }
    return $c;
  }
}
