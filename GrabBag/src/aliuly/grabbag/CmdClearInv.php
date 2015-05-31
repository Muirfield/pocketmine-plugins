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
use aliuly\common\BasicCli;
use aliuly\common\mc;

class CmdClearInv extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("clearinv",
							  ["description" => mc::_("Clear player's inventory"),
								"usage" => mc::_("/clearinv [player]"),
								"permission" => "gb.cmd.clearinv"]);
		$this->enableCmd("clearhotbar",
							  ["description" => mc::_("Clear player's hotbar"),
								"usage" => mc::_("/clearhotbar [player]"),
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
				$sender->sendMessage(mc::_("%1% can not be found.",$args[0]));
				return true;
			}
			$other = true;
		}
		switch ($cmd->getName()) {
			case "clearinv":
				$target->getInventory()->clearAll();
				if ($other) $target->sendMessage(mc::_("Your inventory has been cleared by %1%", $sender->getName()));
				$sender->sendMessage(mc::_("%1%'s inventory cleared",$target->getName()));
				return true;
			case "clearhotbar":
				$inv = $target->getInventory();
				for ($i=0;$i < $inv->getHotbarSize(); $i++) {
					$inv->setHotbarSlotIndex($i,-1);
				}
				if ($other) $target->sendMessage(mc::_("Your hotbar has been cleared by %1%", $sender->getName()));
				$sender->sendMessage(mc::_("%1%'s hotbar cleared",$target->getName()));
				// Make sure inventory is updated...
				$inv->sendContents($target);
				return true;
		}
		return false;
	}
}
