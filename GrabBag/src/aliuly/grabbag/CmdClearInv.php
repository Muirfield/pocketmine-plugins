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
 ** * rminv : Remove item from player's Inventory
 **   usage: **rminv** _[player]_ _<item>_ _[quantity]_
 **
 **/
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;
use aliuly\grabbag\common\ItemName;

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
    $k = 0;
		foreach ($pl->getInventory()->getContents() as $slot => &$inv) {
			if ($inv->getId() != $item->getId()) continue;
			if ($count !== null) {
				if ($item->getCount() > $count) {
					$k += $count;
					$inv->setCount($inv->getCount()-$count);
					$target->getInventory()->setItem($slot,clone $inv);
					break;
				}
				$count -= $inv->getCount();
			}
			$k += $inv->getCount();
			$target->getInventory()->clear($slot);
			if ($count === 0) break;
		}
		if ($k) {
		  $sender->sendMessage(mc::n(mc::_("one item of %1% removed",ItemName::str($item)),
																 mc::_("%2% items of %1% removed",ItemName::str($item),$k)));
			if ($other)
				$target->sendMessage(mc::n(mc::_("%2% took one item of %1% from you",ItemName::str($item),$sender->getDisplayName()),
																 	 mc::_("%3% took %2% items of %1% from you",ItemName::str($item),$k,$sender->getDisplayName())));
		} else {
			$sender->sendMessage()
		}
		return true;
	}
}
