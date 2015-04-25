<?php
/**
 ** OVERVIEW:Trolling
 **
 ** COMMANDS
 **
 ** * burn : Burns the specified player
 **   usage: **burn** _<player>_ _[secs]_
 **
 **   Sets `player` on fire for the specified number of seconds.
 **   Default is 15 seconds.
 **
 **/
namespace aliuly\grabbag;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;

class CmdBurn extends BaseCommand {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("burn",
							  ["description" => "Set player on fire",
								"usage" => "/burn <player> [secs]",
								"permission" => "gb.cmd.burn"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "burn") return false;
		if (count($args) > 2 || count($args) == 0) return false;
		$pl = $this->owner->getServer()->getPlayer($args[0]);
		if (!$pl) {
			$sender->sendMessage("$args[0] not found");
			return true;
		}
		$secs = 15;
		if (isset($args[1])) $secs = intval($args[1]);
		if ($secs <= 1) $secs = 15;
		$pl->setOnFire($secs);
		return true;
	}
}
