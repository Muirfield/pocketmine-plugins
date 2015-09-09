<?php
//= cmd:get,Inventory_Management
//: obtain an item
//> usage: **get** _<item>_ _[count]_
//:
//: This is a shortcut to **give** that lets player get items for
//: themselves.  You can replace **item** with **more** and the
//: current held item will be duplicated.

//= cmd:gift,Inventory_Management
//: give an item to a player
//> usage: **gift** _[player]_ _<item>_ _[count]_
//:
//: This is a re-implementation of **give** command.

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\utils\TextFormat;
use pocketmine\item\Item;
use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\ItemName;
use aliuly\grabbag\common\MPMU;
use aliuly\grabbag\common\PermUtils;

class CmdGet extends BasicCli implements CommandExecutor {
	// Override the MaxStacks counter...
	static $stacks = [
		Item::MINECART => 1, Item::BOOK => 1, Item::COMPASS => 1,
		Item::CLOCK => 1, Item::SPAWN_EGG => 1, Item::FURNACE => 1,
		Item::CHEST => 16, Item::TORCH => 16, Item::NETHER_REACTOR => 16,
	];

	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.get", "get blocks", "op");
		$this->enableCmd("get",
							  ["description" => mc::_("Shortcut to /give me"),
								"usage" => mc::_("/get <item[:damage]> [amount]"),
								"permission" => "gb.cmd.get"]);
		$this->enableCmd("gift",
							  ["description" => mc::_("Alternate /give implementation"),
								"usage" => mc::_("/gift <player <item[:damage]>> [amount]"),
								"permission" => "gb.cmd.get"]);

	}

	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (!isset($args[0])) return false;
		if ($cmd->getName() == "gift") {
			if (($receiver = $this->owner->getServer()->getPlayer($args[0])) == null) {
				if (!MPMU::inGame($sender)) return true;
				$receiver= $sender;
			} else {
				array_shift($args);
			}
		} else {
			if (!MPMU::inGame($sender)) return true;
			$receiver = $sender;
		}

		if ($receiver->isCreative()) {
			if ($receiver === $sender)
				$receiver->sendMessage(mc::_("You are in creative mode"));
			else
				$sender->sendMessage(mc::_("%1% is in creative mode", $receiver->getDisplayName()));
			return true;
		}

		if (count($args) > 1 && is_numeric($args[count($args)-1])) {
			$amt = (int)array_pop($args);
		} else {
			$amt = -1;
		}

		$args = strtolower(implode("_",$args));
		if ($args == "more") {
			$item = clone $receiver->getInventory()->getItemInHand();
			if ($item->getId() == 0) {
				$sender->sendMessage(TextFormat::RED.
															mc::_("Must be holding something"));
				return true;
			}
		} else {
			$item = Item::fromString($args);
			if ($item->getId() == 0) {
				$sender->sendMessage(TextFormat::RED.
										mc::_("There is no item called %1%",$args));
				return true;
			}
		}

		if ($amt != -1) {
			$item->setCount($amt);
		} else {
			if (isset(self::$stacks[$item->getId()])) {
				$item->setCount(self::$stacks[$item->getId()]);
			} else {
				$item->setCount($item->getMaxStackSize());
			}
		}
		$receiver->getInventory()->addItem(clone $item);
		$this->owner->getServer()->broadcastMessage(
			mc::_("%1% got %2% of %3% (%4%:%5%)",
					$receiver->getDisplayName(),
					$item->getCount(),ItemName::str($item),
					$item->getId(),$item->getDamage()));
		return true;
	}
}
