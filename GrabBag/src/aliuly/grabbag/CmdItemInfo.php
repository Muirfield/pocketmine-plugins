<?php
//= cmd:iteminfo,Inventory_Management
//: Show additional info on item held
//> usage: **iteminfo**
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\item\Item;

use aliuly\common\BasicCli;
use aliuly\common\ItemName;
use aliuly\common\MPMU;
use aliuly\common\PermUtils;
use aliuly\common\mc;

class CmdItemInfo extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.iteminfo", "get info on item held", "true");
		PermUtils::add($this->owner, "gb.cmd.iteminfo.other", "item info of others", "op");
		$this->enableCmd("iteminfo",
							  ["description" => mc::_("Get info on held item"),
								"usage" => mc::_("/iteminfo"),
								"permission" => "gb.cmd.iteminfo"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		switch (count($args)) {
			case 0:
				if (!MPMU::inGame($sender)) return true;
				$target = $sender;
				$other = false;
				break;
			case 1:
				if (($target = MPMU::getPlayer($sender,$args[0])) === null) return true;
				$other = true;
				break;
			default:
				return false;
		}
		$item = clone $target->getInventory()->getItemInHand();
		if ($item->getId() == Item::AIR) {
			if ($other) {
				$sender->sendMessage(mc::_("%1% is holding nothing!",$target->getDisplayName()));
			} else {
				$sender->sendMessage(mc::_("You are holding nothing!"));
			}
			return true;
		}

		$sender->sendMessage(TextFormat::BLUE.mc::_("Item: ").TextFormat::WHITE.ItemName::str($item));
		$sender->sendMessage(TextFormat::BLUE.mc::_("ItemId: ").TextFormat::WHITE.$item->getId());
		$sender->sendMessage(TextFormat::BLUE.mc::_("Count: ").TextFormat::WHITE.$item->getCount());
		$sender->sendMessage(TextFormat::BLUE.mc::_("Damage: ").TextFormat::WHITE.$item->getDamage());
		return true;
	}
}
