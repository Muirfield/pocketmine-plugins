<?php
/**
 ** OVERVIEW:Inventory Management
 **
 ** COMMANDS
 **
 ** * clearinv : Clear player's inventory
 **   usage: **clearinv** _[player]_
 ** * clearhotbar: Clear player's hotbar
 **   usage: **clearhotbar** _[player]_
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
								"usage" => "/clearinv [player]",
								"permission" => "gb.cmd.clearinv"]);
		$this->enableCmd("clearhotbar",
							  ["description" => "Clear player's hotbar",
								"usage" => "/clearhotbar [player]",
								"aliases" => ["chb"],
								"permission" => "gb.cmd.clearhotbar"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (count($args) > 1) return false;
		if (count($args) == 0) {
			if (!$this->inGame($sender)) return true;
			$target = $sender;
			$other = false;
		} else {
			if (!$this->access($sender,"gb.cmd.".$cmd->getName())) return true;
			$target = $this->owner->getServer()->getPlayer($args[0]);
			if ($target === null) {
				$sender->sendMessage($args[0]." can not be found.");
				return true;
			}
			$other = true;
		}
		switch ($cmd->getName()) {
			case "clearinv":
				$target->getInventory()->clearAll();
				if ($other) $target->sendMessage("Your inventory has been cleared by ". $sender->getName());
				$sender->sendMessage($target->getName()."'s inventory cleared");
				return true;
			case "clearhotbar":
				$inv = $target->getInventory();
				for ($i=0;$i < $inv->getHotbarSize(); $i++) {
					$inv->setHotbarSlotIndex($i,-1);
				}
				if ($other) $target->sendMessage("Your hotbar has been cleared by ". $sender->getName());
				$sender->sendMessage($target->getName()."'s hotbar cleared");
				// Make sure inventory is updated...
				$inv->sendContents($target);
				return true;
		}
		return false;
	}
}
