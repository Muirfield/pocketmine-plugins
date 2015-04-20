<?php
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

class CmdHeal extends BaseCommand {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("heal",
							  ["description" => "heal player",
								"usage" => "/heal [player] [amount]",
								"aliases" => ["cure"],
								"permission" => "gb.cmd.heal"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "heal") return false;
		if (count($args) == 0) {
			if (!$this->inGame($sender)) return true;
			$sender->setHealth($sender->getMaxHealth());
			$sender->sendMessage("You have been healed");
			return true;
		}
		$patient = $this->owner->getServer()->getPlayer($args[0]);
		if ($patient == null) {
			$sender->sendMessage("$args[0] was not found");
			return true;
		}
		if (isset($args[1]) && is_numeric($args[1])) {
			$health = $patient->getHealth() + intval($args[1]);
			if ($health > $patient->getMaxHealth()) $health = $patient->getMaxHealth();
		} else {
			$health = $patient->getMaxHealth();
		}
		$patient->setHealth($health);
		$sender->sendMessage("$args[0] was healed.");
		return true;
	}
}
