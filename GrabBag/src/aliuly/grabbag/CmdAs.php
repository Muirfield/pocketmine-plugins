<?php
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\Player;
use pocketmine\event\player\PlayerChatEvent;

class CmdAs extends BaseCommand {

	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("as",
							  ["description" => "execute command as somebody else",
								"usage" => "/as <player> <cmd>",
								"aliases" => ["sudo"],
								"permission" => "gb.cmd.sudo"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "as") return false;
		if (count($args) < 2) {
			$sender->sendMessage("Must specified a player and a command");
			return false;
		}
		$player = $this->owner->getServer()->getPlayer($n = array_shift($args));
		if (!$player) {
			$sender->sendMessage("Player $n not found");
			return true;
		}
		if ($args[0] == 'chat' || $args[0] == 'say') {
			array_shift($args);
			$chat = implode(" ",$args);
			$this->owner->getServer()->getPluginManager()->callEvent($ev = new PlayerChatEvent($player,$chat));
			if (!$ev->isCancelled()) {
				$this->owner->getServer()->broadcastMessage(sprintf($ev->getFormat(),$ev->getPlayer()->getDisplayName(),$ev->getMessage()),$ev->getRecipients());
			}
		} else {
			$cmdline = implode(' ',$args);
			$sender->sendMessage("Running command as $n");
			$this->owner->getServer()->dispatchCommand($player,$cmdline);
		}
		return true;
	}
}
