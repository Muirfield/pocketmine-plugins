<?php
//= cmd:seeinv,Inventory_Management
//: Show player's inventory
//> usage: **seeinv** _<player>_
//= cmd:seearmor,Inventory_Management
//: Show player's armor
//> usage: **seearmor** _<player>_
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\utils\TextFormat;
use pocketmine\item\Item;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\ItemName;
use aliuly\grabbag\common\PermUtils;

class CmdShowInv extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.seearmor", "View armor", "op");
		PermUtils::add($this->owner, "gb.cmd.seeinv", "View inventory", "op");
		$this->enableCmd("seeinv",
							  ["description" => mc::_("show player's inventory"),
								"usage" => mc::_("/seeinv <player>"),
								"aliases" => ["invsee"],
								"permission" => "gb.cmd.seeinv"]);
		$this->enableCmd("seearmor",
							  ["description" => mc::_("show player's armor"),
								"usage" => mc::_("/seearmor <player>"),
								"aliases" => ["armorsee"],
								"permission" => "gb.cmd.seearmor"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		$pageNumber = $this->getPageNumber($args);
		if (count($args) != 1) {
			$sender->sendMessage(mc::_("You must specify a player's name"));
			return false;
		}
		$target = $this->owner->getServer()->getPlayer($args[0]);
		if($target == null) {
			$sender->sendMessage(mc::_("%1% can not be found.",$args[0]));
			return true;
		}
		if ($cmd->getName() == "seeinv") {
			$tab= [[$args[0],mc::_("Count"),mc::_("Damage")]];
			$max = $target->getInventory()->getSize();
			foreach ($target->getInventory()->getContents() as $slot => &$item) {
				if ($slot >= $max) continue;
				$tab[] = [ItemName::str($item)." (".$item->getId().")",
							 $item->getCount(),$item->getDamage() ];
			}
			if (count($tab) == 1) {
				$sender->sendMessage(mc::_("The inventory for %1% is EMPTY",$args[0]));
				return true;
			}
		}elseif ($cmd->getName() == "seearmor") {
			$tab= [[mc::_("Armor for"),TextFormat::RED.$args[0]]];
			foreach ([0=>"head",1=>"body",2=>"legs",3=>"boots"] as $slot=>$attr) {
				$item = $target->getInventory()->getArmorItem($slot);
				if ($item->getID() == 0) continue;
				$tab[]=[$attr.TextFormat::BLUE,
						  ItemName::str($item)." (" .$item->getId().":".$item->getDamage().")"];
			}
		}
		return $this->paginateTable($sender,$pageNumber,$tab);
	}
}
