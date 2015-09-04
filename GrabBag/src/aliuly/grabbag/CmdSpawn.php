<?php
//= cmd:spawn,Teleporting
//: Teleport player to spawn point
//> usage: **spawn**
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;
use aliuly\grabbag\common\PermUtils;

class CmdSpawn extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.spawn", "Teleport to spawn", "true");
		$this->enableCmd("spawn",
							  ["description" => mc::_("Teleport to spawn location"),
								"usage" => mc::_("/spawn"),
								"permission" => "gb.cmd.spawn"]);
	}
	public function tpSpawn($pl) {
		$pos = $pl->getLevel()->getSafeSpawn();
		$pl->teleport($pos);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "spawn") return false;
		if (count($args) != 0) return false;
		if (!MPMU::inGame($sender)) return true;
		$sender->sendMessage("Teleporting to spawn...");
		$this->tpSpawn($sender);
		return true;
	}
}
