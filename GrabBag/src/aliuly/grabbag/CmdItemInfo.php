<?php
//= cmd:iteminfo,Inventory_Management
//: Show additional info on item held
//> usage: **iteminfo**
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;

//use pocketmine\item\Item;
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
				break;
			case 1:
				if (($target = $this->owner->getServer()->getPlayer($args[0])) == null) {
					$sender->sendMessage(mc::_("%1% not found", $args[0]));
					return true;
				}
				break;
			default:
				return false;
		}
		$item = clone $target->getInventory()->getItemInHand();

		$sender->sendMesage(TextFormat::BLUE.mc::_("Item: ").TextFormat::WHITE.ItemName::str($item));
		$sender->sendMesage(TextFormat::BLUE.mc::_("ItemId: ").TextFormat::WHITE.$item->getId());
		$sender->sendMesage(TextFormat::BLUE.mc::_("Count: ").TextFormat::WHITE.$item->getCount());
		$sender->sendMesage(TextFormat::BLUE.mc::_("Damage: ").TextFormat::WHITE.$item->getDamage());
		return true;
	}
}
