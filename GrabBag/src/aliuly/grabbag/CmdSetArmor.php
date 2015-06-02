<?php
/**
 ** OVERVIEW:Inventory Management
 **
 ** COMMANDS
 **
 ** * setarmor : Sets armor (even in creative)
 **   usage: **setarmor** _[player]_ _[piece]_ _<type>_
 **
 **   This command lets you armor up.  It can armor up creative players too.
 **   If no `player` is given, the player giving the command will be armored.
 **
 **   Piece can be one of `head`, `body`, `legs`, or `boots`.
 **
 **   Type can be one of `leather`, `chainmail`, `iron`, `gold` or `diamond`.
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

class CmdSetArmor extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("setarmor",
							  ["description" => mc::_("Set armor (even in creative)"),
								"usage" => mc::_("/setarmor [player] [piece] <type>"),
								"permission" => "gb.cmd.setarmor"]);
	}
	private function armorSlot($str) {
		switch(strtolower(substr($str,0,1))) {
			case "h"://ead
				return 0;
			case "b":
				switch(strtolower(substr($str,0,3))) {
					case "bod"://y
						return 1;
					case "boo"://ts
						return 3;
				}
				break;
			case "l"://egs
				return 2;
		}
		return -1;
	}
	private function armorType($str) {
		switch(strtolower(substr($str,0,1))) {
			case "l"://eather
				return 298;
			case "c"://hainmail
				return 302;
			case "i"://ron
				return 306;
			case "g"://old
				return 314;
			case "d"://iamond
				return 310;
			case "n"://one
				return 0;
		}
		return -1;
	}

	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (count($args) == 0) return false;
		$i = array_pop($args);
		if (($type = $this->armorType($i)) == -1) {
			$sender->sendMessage(mc::_("Unknown armor type %1%",$i));
			return false;
		}
		$slots = [0,1,2,3]; // All slots
		if (count($args)) {
			$i = $this->armorSlot($args[count($args)-1]);
			if ($i != -1) {
				$slots = [ $i ];
				array_pop($args);
			}
		}
		$pl = $sender;
		if (count($args)) {
			$i = $this->owner->getServer()->getPlayer($args[count($args)-1]);
			if ($i) {
				$pl = $i;
				if (!$this->access($sender,"gb.cmd.setarmor.others")) return true;
				array_pop($args);
			}
		}
		if (count($args)) return false;
		if (!$this->inGame($pl)) return true;
		foreach($slots as $i) {
			$pl->getInventory()->setArmorItem($i,
														 new Item($type == 0 ? 0 :$type+$i,
																	 0,1));
		}
		if ($type == 0)
			$sender->sendMessage(mc::_("Amouring down %1%",$pl->getName()));
		else
			$sender->sendMessage(mc::_("Amouring up %1%",$pl->getName()));
		// Make sure inventory is updated...
		$pl->getInventory()->sendArmorContents($pl);
		return true;
	}
}
