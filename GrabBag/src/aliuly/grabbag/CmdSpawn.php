<?php
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\server\RemoteServerCommandEvent;
use pocketmine\event\server\ServerCommandEvent;

class CmdSpawn extends BaseCommand {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("spawn",
							  ["description" => "Teleport to spawn location",
								"usage" => "/spawn",
								"permission" => "gb.cmd.spawn"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "spawn") return false;
		if (count($args) != 0) return false;
		if (!$this->inGame($sender)) return true;
		$pos = $sender->getLevel()->getSafeSpawn();
		$sender->sendMessage("Teleporting to spawn...");
		$this->mwteleport($sender,$pos);
		return true;
	}
}
