<?php
/**
 ** OVERVIEW:Trolling
 **
 ** COMMANDS
 **
 ** * throw : Throw a player in the air -- **ONLY FOR PM1.4**
 **   usage: **throw** _<player>_ _[force]_
 **/
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\math\Vector3;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;

class CmdThrow extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("throw",
							  ["description" => mc::_("Throw player up in the air"),
								"usage" => mc::_("/throw <player> [force]"),
								"permission" => "gb.cmd.throw"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "throw") return false;
		if (count($args) > 2 || count($args) == 0) return false;
		$pl = $this->owner->getServer()->getPlayer($args[0]);
		if (!$pl) {
			$sender->sendMessage(mc::_("%1% not found",$args[0]));
			return true;
		}

		if (MPMU::apiVersion("1.12.0")) {
			$force = 64;
			if (isset($args[1])) $force = intval($args[1]);
			if ($force <= 4) $force = 64;


		} else {
			$force = 64;
			if (isset($args[1])) $force = intval($args[1]);
			if ($force <= 4) $force = 64;

			$pl->setMotion(new Vector3(0,$force,0));
		}
		return true;
	}
}
