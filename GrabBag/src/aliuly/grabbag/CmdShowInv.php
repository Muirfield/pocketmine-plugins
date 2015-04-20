<?php
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\utils\TextFormat;
use pocketmine\item\Item;

class CmdShowInv extends BaseCommand {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("seeinv",
							  ["description" => "show player's inventory",
								"usage" => "/seeinv <player>",
								"permission" => "gb.cmd.seeinv"]);
		$this->enableCmd("seearmor",
							  ["description" => "show player's armor",
								"usage" => "/seearmor <player>",
								"permission" => "gb.cmd.seearmor"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		$pageNumber = $this->getPageNumber($args);
		if (count($args) != 1) {
			$sender->sendMessage("You must specify a player's name");
			return false;
		}
		$target = $this->owner->getServer()->getPlayer($args[0]);
		if($target == null) {
			$sender->sendMessage($args[0]." can not be found.");
			return true;
		}
		if ($cmd->getName() == "seeinv") {
			$tab= [[$args[0],"Count","Damage"]];
			$max = $target->getInventory()->getSize();
			foreach ($target->getInventory()->getContents() as $slot => &$item) {
				if ($slot >= $max) continue;
				$tab[] = [$this->itemName($item)." (".$item->getId().")",
							 $item->getCount(),$item->getDamage() ];
			}
			if (count($tab) == 1) {
				$sender->sendMessage("The inventory for $args[0] is EMPTY");
				return true;
			}
		}elseif ($cmd->getName() == "seearmor") {
			$tab= [["Armor for",TextFormat::RED.$args[0]]];
			foreach ([0=>"head",1=>"body",2=>"legs",3=>"boots"] as $slot=>$attr) {
				$item = $target->getInventory()->getArmorItem($slot);
				if ($item->getID() == 0) continue;
				$tab[]=[$attr.TextFormat::BLUE,
						  $this->itemName($item)." (" .$item->getId().":".$item->getDamage().")"];
			}
		}
		return $this->paginateTable($sender,$pageNumber,$tab);
	}
}
