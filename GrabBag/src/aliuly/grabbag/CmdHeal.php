<?php
//= cmd:heal,Trolling
//: Restore health to a player
//> usage: **heal** _[player]_ _[ammount]_
//:
//: Heals a player.  If the amount is positive it will heal, if negative
//: the player will be hurt.  The units are in 1/2 hearts.

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;
use aliuly\grabbag\common\PermUtils;

class CmdHeal extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.heal", "heal players", "op");
		$this->enableCmd("heal",
							  ["description" => mc::_("heal player"),
								"usage" => mc::_("/heal [player] [amount]"),
								"aliases" => ["cure"],
								"permission" => "gb.cmd.heal"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "heal") return false;
		if (count($args) == 0) {
			if (!MPMU::inGame($sender)) return true;
			$sender->setHealth($sender->getMaxHealth());
			$sender->sendMessage(mc::_("You have been healed"));
			return true;
		}
		$patient = $this->owner->getServer()->getPlayer($args[0]);
		if ($patient == null) {
			$sender->sendMessage(mc::_("%1% not found.",$args[0]));
			return true;
		}
		if (isset($args[1]) && is_numeric($args[1])) {
			$health = $patient->getHealth() + intval($args[1]);
			if ($health > $patient->getMaxHealth()) $health = $patient->getMaxHealth();
		} else {
			$health = $patient->getMaxHealth();
		}
		$patient->setHealth($health);
		$sender->sendMessage(mc::_("%1% was healed.",$args[0]));
		return true;
	}
}
