<?php
/**
 ** OVERVIEW:Inventory Management
 **
 ** COMMANDS
 **
 ** * get : obtain an item
 **   usage: **get** _<item>_ _[count]_
 **
 **   This is a shortcut to `/give` that lets player get items for
 **   themselves.
 **
 ** * gift : give an item to a player
 **   usage: **gift** _[player]_ _<item>_ _[count]_
 **
 **   This is a re-implementation of `/give`.
 **
 **/
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

class CmdGet extends BasicCli implements CommandExecutor {
	// Override the MaxStacks counter...
	static $stacks = [ Item::MINECART => 1, Item::BOOK => 1, Item::COMPASS => 1,
							 Item::CLOCK => 1 ];

	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("get",
							  ["description" => mc::_("Shortcut to /give me"),
								"usage" => mc::_("/get <item[:damage]> [amount]"),
								"permission" => "gb.cmd.get"]);
		$this->enableCmd("gift",
							  ["description" => mc::_("Alternate /give implementation"),
								"usage" => mc::_("/gift <player <item[:damage]>> [amount]"),
								"permission" => "gb.cmd.get"]);

	}
	public function cmdGift(CommandSender $c,$args) {
		if (($receiver = $this->owner->getPlayer($args[0])) == null) {
			if (MPMU::inGame($c)) return true;
			$receiver = $c;
		} else {
			array_shift($args);
		}
		if (!count($args)) return false;
		if ($receiver->isCreative()) {
			$sender->sendMessage(mc::_("%1% is in creative mode", $receiver->getDisplayName()));
			return true;
		}
		$item = Item::fromString($args[0]);
		if ($item->getId() == 0) {
			$sender->sendMessage(TextFormat::RED.
										mc::_("There is no item called %1%",$args[0]));
			return true;
		}
		if (isset($args[1])) {
			$item->setCount((int)$args[1]);
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
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (!isset($args[0])) return false;
		if ($cmd->getName() == "gift") return cmdGift($sender,$args);
		if ($cmd->getName() != "get") return false;
		if ($sender->isCreative()) {
			$sender->sendMessage(mc::_("You are in creative mode"));
			return true;
		}
		$item = Item::fromString($args[0]);
		if ($item->getId() == 0) {
			$sender->sendMessage(TextFormat::RED.
										mc::_("There is no item called %1%",$args[0]));
			return true;
		}

		if (isset($args[1])) {
			$item->setCount((int)$args[1]);
		} else {
			if (isset(self::$stacks[$item->getId()])) {
				$item->setCount(self::$stacks[$item->getId()]);
			} else {
				$item->setCount($item->getMaxStackSize());
			}
		}
		$sender->getInventory()->addItem(clone $item);
		$this->owner->getServer()->broadcastMessage(
			mc::_("%1% got %2% of %3% (%4%:%5%)",
					$sender->getDisplayName(),
					$item->getCount(),ItemName::str($item),
					$item->getId(),$item->getDamage()));
		return true;
	}
}
