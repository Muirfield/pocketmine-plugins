<?php
/**
 ** OVERVIEW:Trolling
 **
 ** COMMANDS
 **
 ** * throw : Throw a player in the air
 **   usage: **throw** _<player>_ _[force]_
 **/
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\math\Vector3;

class CmdThrow extends BaseCommand {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("throw",
							  ["description" => "Throw player up in the air",
								"usage" => "/throw <player> [force]",
								"permission" => "gb.cmd.throw"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "throw") return false;
		if (count($args) > 2 || count($args) == 0) return false;
		$pl = $this->owner->getServer()->getPlayer($args[0]);
		if (!$pl) {
			$sender->sendMessage("$args[0] not found");
			return true;
		}
		$force = 64;
		if (isset($args[1])) $force = intval($args[1]);
		if ($force <= 4) $force = 64;

		$pl->setMotion(new Vector3(0,$force,0));

		return true;
	}
}
