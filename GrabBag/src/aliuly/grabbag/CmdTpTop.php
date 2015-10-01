<?php
//= cmd:top,Teleporting
//: Teleport player to the top
//> usage: **top** _[player]_

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\math\Vector3;

use aliuly\common\BasicCli;
use aliuly\common\mc;
use aliuly\common\MPMU;
use aliuly\common\PermUtils;

class CmdTpTop extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.top", "top commnad", "op");
    PermUtils::add($this->owner, "gb.cmd.top.others", "top others commnad", "op");

		$this->enableCmd("top",
							  ["description" => mc::_("Teleport player to top most block"),
								"usage" => mc::_("/top [player]"),
								"permission" => "gb.cmd.top"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "top") return false;
		switch (count($args)) {
      case 0:
        if (!MPMU::inGame($sender)) return true;
        $pl = $sender;
        break;
      case 1:
        if (!MPMU::access($sender,"gb.cmd.top.others")) return true;
				if (($pl = MPMU::getPlayer($sender,$args[0])) === null) return true;
        break;
      default:
        return false;
    }
    $y = $pl->getLevel()->getHighestBlockAt($pl->getX(),$pl->getZ())+1;
    $pl->teleport(new Vector3($pl->getX(),$y,$pl->getZ()));
    return true;
	}
}
