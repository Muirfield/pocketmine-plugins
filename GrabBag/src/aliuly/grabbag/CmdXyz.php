<?php
//= cmd:xyz,Informational
//: shows the players position and bearing
//>  usage: **xyz** _[player]_
namespace aliuly\grabbag;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;

use aliuly\common\mc;
use aliuly\common\BasicCli;
use aliuly\common\PermUtils;
use aliuly\common\ExpandVars;
use aliuly\common\MPMU;

class CmdXyz extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
    PermUtils::add($this->owner, "gb.cmd.xyz", "xyz command", "true");
		PermUtils::add($this->owner, "gb.cmd.xyz.others", "xyz command on others", "op");
    $this->enableCmd("xyz",
							  ["description" => mc::_("displays location and bearing"),
								"usage" => mc::_("/xyz [player]"),
								"permission" => "gb.cmd.xyz"]);
	}
  public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
    if (strtolower($cmd->getName()) != "xyz") return false;
		if (count($args) == 0) {
			if (!MPMU::inGame($sender)) return true;
			$sender->sendMessage(mc::_("You are at %1%,%2%,%3% in world %4%, heading %5%", (int)$sender->getX(),(int)$sender->getY(),(int)$sender->getZ(),$sender->getLevel()->getName(), ExpandVars::bearing($sender->getYaw())));
		} else {
			if (count($args) != 1) return false;
			if (!MPMU::access($sender,"gb.cmd.xyz.others")) return true;
			if (($player = MPMU::getPlayer($sender,$args[0])) === null) return true;
			$sender->sendMessage(mc::_("%6% is at %1%,%2%,%3% in world %4%, heading %5%", (int)$player->getX(),(int)$player->getY(),(int)$player->getZ(),$player->getLevel()->getName(), ExpandVars::bearing($player->getYaw()), $player->getDisplayName() ));
		}
    return true;
	}
}
