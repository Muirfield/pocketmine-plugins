<?php
//= cmd:fly,Player_Management
//: Toggle flying **ONLY FOR PM >1.5**
//> usage: **fly**
//
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\utils\TextFormat;
use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;
use aliuly\grabbag\common\PermUtils;

class CmdFly extends BasicCli implements CommandExecutor {

	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.fly", "flight control", "op");
		$this->enableCmd("fly",
							  ["description" => mc::_("Allow flying"),
								"usage" => mc::_("/fly"),
								"permission" => "gb.cmd.fly"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (!MPMU::inGame($sender)) return true;
		if ($cmd->getName() != "fly") return false;
		if ($sender->getAllowFlight()) {
			$sender->sendMessage(mc::_("Disabling flight mode"));
			$sender->setAllowFlight(false);
		} else {
			$sender->sendMessage(mc::_("Enabling flight mode"));
			$sender->setAllowFlight(true);
		}
		return true;
	}
}
