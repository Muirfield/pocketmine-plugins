<?php
namespace aliuly\common\selectors;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class All extends BaseSelector {
  static public function select(Server $srv, CommandSender $sender, array $args) {
    $result = [];
    foreach ($srv->getOnlinePlayers() as $p) {
      if (count($args) && !self::checkSelectors($args,$sender,$p)) continue;
      $result[] = $p->getName();
    }
    return $result;
  }
}
