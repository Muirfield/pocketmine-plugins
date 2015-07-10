<?php
namespace aliuly\grabbag\selectors;

use pocketmine\command\CommandSender;
use aliuly\grabbag\CmdSelMgr as CmdSelModule;

interface BaseSelector {
  static public function select(CmdSelModule $owner, CommandSender $sender, array $args);
}
