<?php
//= cmd:clearinv,Inventory_Management
//: Clear player's inventory
//> usage: **clearinv** _[player]_

//= cmd:clearhotbar,Inventory_Management
//: Clear player's hotbar
//> usage: **clearhotbar** _[player]_

//= cmd:rminv,Inventory_Management
//: Remove item from player's Inventory
//> usage: **rminv** _[player]_ _<item>_ _[quantity]_

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;
use aliuly\grabbag\common\ItemName;
use aliuly\grabbag\common\PermUtils;
use aliuly\grabbag\common\InvUtils;

use pocketmine\item\Item;

class CmdClearInv extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);

		PermUtils::add($this->owner, "gb.cmd.clearinv", "clear player's inventory", "true");
		PermUtils::add($this->owner, "gb.cmd.clearinv.others", "clear other's inventory", "op");
		PermUtils::add($this->owner, "gb.cmd.rminv", "remove item from inventory", "true");
		PermUtils::add($this->owner, "gb.cmd.rminv.others", "remove item from other's inventory", "op");
		PermUtils::add($this->owner, "gb.cmd.clearhotbar", "clear player's hotbar", "true");
		PermUtils::add($this->owner, "gb.cmd.clearhotbar.others", "clear other's hotbar", "op");

		$this->enableCmd("clearinv",
							  ["description" => mc::_("Clear player's inventory"),
								"usage" => mc::_("/clearinv [player]"),
								"permission" => "gb.cmd.clearinv"]);
		$this->enableCmd("clearhotbar",
							  ["description" => mc::_("Clear player's hotbar"),
								"usage" => mc::_("/clearhotbar [player]"),
								"aliases" => ["chb"],
								"permission" => "gb.cmd.clearhotbar"]);
		$this->enableCmd("rminv",
										  ["description" => mc::_("Remove item from player's inventory"),
											"usage" => mc::_("/rminv [player] <item> [quantity]"),
											"permission" => "gb.cmd.rminv"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() == "rminv") return $this->rmInvItem($sender,$args);
		if (count($args) > 1) return false;
		if (count($args) == 0) {
			if (!MPMU::inGame($sender)) return true;
			$target = $sender;
			$other = false;
		} else {
			if (!MPMU::access($sender,"gb.cmd.".$cmd->getName().".others")) return true;
			$target = $this->owner->getServer()->getPlayer($args[0]);
			if ($target === null) {
				$sender->sendMessage(mc::_("%1% can not be found.",$args[0]));
				return true;
			}
			$other = true;
		}
		switch ($cmd->getName()) {
			case "clearinv":
				InvUtils::clearInventory($target);
				if ($other) $target->sendMessage(mc::_("Your inventory has been cleared by %1%", $sender->getName()));
				$sender->sendMessage(mc::_("%1%'s inventory cleared",$target->getName()));
				return true;
			case "clearhotbar":
				InvUtils::clearHotbar($target);
				if ($other) $target->sendMessage(mc::_("Your hotbar has been cleared by %1%", $sender->getName()));
				$sender->sendMessage(mc::_("%1%'s hotbar cleared",$target->getName()));
				return true;
		}
		return false;
	}
	private function rmInvItem(CommandSender $sender, array $args) {
		if (count($args) == 0) return false;
		if (($target = $this->owner->getServer()->getPlayer($args[0])) === null) {
			if (!MPMU::inGame($sender)) return true;
			$target = $sender;
			$other = false;
		} else {
			if (!MPMU::access($sender,"gb.cmd.rminv.others")) return true;
			array_shift($args);
			$other= true;
		}
		if (count($args) == 0) return false;
		if ($target->isCreative() || $target->isSpectator()) {
			$sender->sendMessage(mc::_("%1% is in %2% mode", $target->getDisplayName(),
														MPMU::gamemodeStr($target->getGamemode())));
			return true;
		}

		$count = null;
		if (count($args) > 1 && is_numeric($args[count($args)-1])) {
			$count = array_pop($args);
		}
		$args = strtolower(implode("_",$args));
		if ($args == "hand") {
			$item = clone $target->getInventory()->getItemInHand();
			if ($item->getId() == 0) {
				$sender->sendMessage(mc::_("Must be holding something"));
				return true;
			}
		} else {
			$item = Item::fromString($args);
			if ($item->getId() == 0) {
				$sender->sendMessage(mc::_("There is no item called %1%",$args));
				return true;
			}
		}
		$k = InvUtils::rmInvItem($target,$item,$count);
		if ($k) {
			$sender->sendMessage(mc::n(mc::_("one item of %1% removed",ItemName::str($item)),
						 mc::_("%2% items of %1% removed",ItemName::str($item),$k),$k));
			if ($other)
				$target->sendMessage(mc::n(mc::_("%2% took one item of %1% from you",ItemName::str($item),$sender->getName()),
																 	 mc::_("%3% took %2% items of %1% from you",ItemName::str($item),$k,$sender->getName()),$k));
		} else {
			$sender->sendMessage(mc::_("No items were removed!"));
		}
		return true;
	}
}
