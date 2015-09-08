<?php
//= cmd:setarmor,Inventory_Management
//: Sets armor (even in creative)
//> usage: **setarmor** _[player]_ _[part]_ _<quality>_
//:
//: This command lets you armor up.  It can armor up creative players too.
//: If no **player** is given, the player giving the command will be armored.
//:
//: Part can be one of **head**, **body**, **legs**, or **boots**.
//:
//: Quality can be one of **none**, **leather**, **chainmail**, **iron**,
//: **gold** or **diamond**.
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\utils\TextFormat;
use pocketmine\item\Item;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;
use aliuly\grabbag\common\ArmorItems;
use aliuly\grabbag\common\PermUtils;

class CmdSetArmor extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.setarmor", "Configure armor", "op");
		PermUtils::add($this->owner, "gb.cmd.setarmor.others", "Configure other's armor", "op");

		$this->enableCmd("setarmor",
							  ["description" => mc::_("Set armor (even in creative)"),
								"usage" => mc::_("/setarmor [player] [piece] <quality>"),
								"permission" => "gb.cmd.setarmor"]);
	}

	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (count($args) == 0) return false;
		$i = array_pop($args);
		if (($type = ArmorItems::str2quality($i)) == ArmorItems::ERROR) {
			$sender->sendMessage(mc::_("Unknown armor quality %1%",$i));
			return false;
		}
		$slots = [0,1,2,3]; // All slots
		if (count($args)) {
			$i = ArmorItems::str2part($args[count($args)-1]);
			if ($i != ArmorItems::ERROR) {
				$slots = [ $i ];
				array_pop($args);
			}
		}
		$pl = $sender;
		if (count($args)) {
			$i = $this->owner->getServer()->getPlayer($args[count($args)-1]);
			if ($i) {
				$pl = $i;
				if (!MPMU::access($sender,"gb.cmd.setarmor.others")) return true;
				array_pop($args);
			}
		}
		if (count($args)) return false;
		if (!MPMU::inGame($pl)) return true;
		foreach($slots as $i) {
			$pl->getInventory()->setArmorItem($i,new Item(ArmorItems::getItemId($type,$i),0,1));
		}
		if ($type == ArmorItems::NONE)
			$sender->sendMessage(mc::_("Amouring down %1%",$pl->getName()));
		else
			$sender->sendMessage(mc::_("Amouring up %1%",$pl->getName()));
		// Make sure inventory is updated...
		$pl->getInventory()->sendArmorContents($pl);
		return true;
	}
}
