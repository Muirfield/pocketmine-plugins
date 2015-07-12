<?php
namespace aliuly\grabbag\selectors;

use pocketmine\command\CommandSender;
use aliuly\grabbag\CmdSelMgr as CmdSelModule;

class All implements BaseSelector {
  static public function select(CmdSelModule $owner, CommandSender $sender, array $args) {
    $result = [];
    foreach ($owner->getServer()->getOnlinePlayers() as $p) {
      if (count($args) && !$owner->checkSelectors($args,$sender,$p)) continue;
      $result[] = $p->getName();
    }
    return $result;
  }
}
