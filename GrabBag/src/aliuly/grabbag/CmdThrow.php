<?php
//= cmd:throw,Trolling
//: Throw a player in the air
//> usage: **throw** _<player>_ _[force]_

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\math\Vector3;

use aliuly\common\BasicCli;
use aliuly\common\mc;
use aliuly\common\MPMU;
use aliuly\common\PermUtils;

class CmdThrow extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.throw", "Troll players", "op");

		$this->enableCmd("throw",
							  ["description" => mc::_("Throw player up in the air"),
								"usage" => mc::_("/throw <player> [force]"),
								"permission" => "gb.cmd.throw"]);
	}
	public function throwPlayer($pl) {
		if (MPMU::apiVersion("1.12.0")) {
			$pl->teleport(new Vector3($pl->getX(),128,$pl->getZ()));
		} else {
			$force = 64;
			if (isset($args[1])) $force = intval($args[1]);
			if ($force <= 4) $force = 64;

			$pl->setMotion(new Vector3(0,$force,0));
		}

	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "throw") return false;
		if (count($args) > 2 || count($args) == 0) return false;
		if (($pl = MPMU::getPlayer($sender,$args[0])) === null) return true;
		$this->throwPlayer($pl);
		return true;
	}
}
