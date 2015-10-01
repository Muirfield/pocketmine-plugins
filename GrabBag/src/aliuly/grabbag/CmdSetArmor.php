<?php
//= cmd:setarmor,Inventory_Management
//: Sets armor (even in creative)
//> usage: **setarmor** _[player]_ _<quality|item>_
//:
//: This command lets you armor up.  It can armor up creative players too.
//: If no **player** is given, the player giving the command will be armored.
//:
//: Quality can be one of **none**, **leather**, **chainmail**, **iron**,
//: **gold** or **diamond**.  This will make all armor components of that
//: quality.
//:
//: Otherwise you can specify an armor item, and this will be placed in
//: your armor slot.
//:
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\utils\TextFormat;
use pocketmine\item\Item;

use aliuly\common\BasicCli;
use aliuly\common\mc;
use aliuly\common\MPMU;
use aliuly\common\ArmorItems;
use aliuly\common\ItemName;
use aliuly\common\PermUtils;

class CmdSetArmor extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.setarmor", "Configure armor", "op");
		PermUtils::add($this->owner, "gb.cmd.setarmor.others", "Configure other's armor", "op");

		$this->enableCmd("setarmor",
							  ["description" => mc::_("Set armor (even in creative)"),
								"usage" => mc::_("/setarmor [player] <quality|item>"),
								"permission" => "gb.cmd.setarmor"]);
	}

	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (count($args) == 0) return false;
		$pl = $this->owner->getServer()->getPlayer($args[0]);
		if ($pl !== null) {
			array_shift($args);
			if (count($args) == 0) return false;
			if (!MPMU::access($sender,"gb.cmd.setarmor.others")) return true;
			$others = true;
		} else {
			if (!MPMU::inGame($sender)) return true;
			$pl = $sender;
			$others = false;
		}
		$j = implode("_",$args);
		$item = Item::fromString($j);
		$slot = ArmorItems::getArmorPart($item->getId());
		if ($slot != ArmorItems::ERROR) {
			$pl->getInventory()->setArmorItem($slot, clone $item);
			if ($others) {
				$pl->sendMessage(mc::_("You were equiped an %1% by %2%", ItemName::str($item), $sender->getName()));
				$sender->sendMessage(mc::_("Equiping %1% with %2%", $pl->getDisplayName(), ItemName::str($item)));
			} else {
				$sender->sendMessage(mc::_("Equiping armor %1%", ItemName::str($item)));
			}
			$pl->getInventory()->sendArmorContents($pl);
			return true;
		}
		if (($type = ArmorItems::str2quality($j)) == ArmorItems::ERROR) {
			$sender->sendMessage(mc::_("Unknown armor quality %1%",$j));
			return false;
		}
		foreach([0,1,2,3] as $slot) {
			$pl->getInventory()->setArmorItem($slot,new Item(ArmorItems::getItemId($type,$slot),0,1));
		}
		if ($type == ArmorItems::NONE) {
			if ($others) {
				$pl->sendMessage(mc::_("You were armoured down by %1%", $sender->getName()));
				$sender->sendMessage(mc::_("%1% was armoured down", $pl->getDisplayName()));
			} else {
				$sender->sendMessage(mc::_("Armoured down"));
			}
		} else {
			if ($others) {
				$pl->sendMessage(mc::_("You were armoured up by %1%", $sender->getName()));
				$sender->sendMessage(mc::_("%1% was armoured up", $pl->getDisplayName()));
			} else {
				$sender->sendMessage(mc::_("Armoured up"));
			}
		}
		$pl->getInventory()->sendArmorContents($pl);
		return true;
	}
}
