<?php
/**
 ** OVERVIEW:Inventory Management
 **
 ** COMMANDS
 **
 ** * clearinv : Clear player's inventory
 **   usage: **clearinv** _<player>_
 **
 **/
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

class CmdClearInv extends BaseCommand {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("clearinv",
							  ["description" => "Clear player's inventory",
								"usage" => "/clearinv <player>",
								"permission" => "gb.cmd.clearinv"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "clearinv") return false;
		if (count($args) != 1) {
			$sender->sendMessage("You must specify a player's name");
			return false;
		}
		$target = $this->owner->getServer()->getPlayer($args[0]);
		if($target == null) {
			$sender->sendMessage($args[0]." can not be found.");
			return true;
		}
		$target->getInventory()->clearAll();
		$target->sendMessage("Your inventory has been cleared by ".
									$sender->getName());
		$sender->sendMessage($target->getName()."'s inventory cleared");
		return true;
	}
}
