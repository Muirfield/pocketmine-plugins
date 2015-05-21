<?php
/**
 ** OVERVIEW:Player Management
 **
 ** COMMANDS
 **
 ** * fly : Toggle flying
 **   usage: **fly**
 **
 **/
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\utils\TextFormat;

class CmdFly extends BaseCommand {

	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("fly",
							  ["description" => "Allow flying",
								"usage" => "/fly",
								"permission" => "gb.cmd.fly"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (!$this->inGame($sender)) return true;
		if ($cmd->getName() != "fly") return false;
		if ($sender->getAllowFlight()) {
			$sender->sendMessage("Disabling flight mode");
			$sender->setAllowFlight(false);
		} else {
			$sender->sendMessage("Enabling flight mode");
			$sender->setAllowFlight(true);
		}
		return true;
	}
}
