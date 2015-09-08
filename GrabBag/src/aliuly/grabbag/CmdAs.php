<?php
//= cmd:as,Player_Management
//: run command as somebody else
//> usage: **as** _<player>_ _<command>_
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;
use aliuly\grabbag\common\PermUtils;

use pocketmine\Player;
use pocketmine\event\player\PlayerChatEvent;

class CmdAs extends BasicCli implements CommandExecutor {

	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.sudo", "Run command as another user", "op");
		$this->enableCmd("as",
							  ["description" => mc::_("execute command as somebody else"),
								"usage" => mc::_("/as <player> <cmd>"),
								"aliases" => ["sudo"],
								"permission" => "gb.cmd.sudo"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "as") return false;
		if (count($args) < 2) {
			$sender->sendMessage(mc::_("Must specified a player and a command"));
			return false;
		}
		$player = $this->owner->getServer()->getPlayer($n = array_shift($args));
		if (!$player) {
			$sender->sendMessage(mc::_("Player %1% not found",$n));
			return true;
		}
		if ($args[0] == 'chat' || $args[0] == 'say') {
			array_shift($args);
			$chat = implode(" ",$args);
			$this->owner->getServer()->getPluginManager()->callEvent($ev = new PlayerChatEvent($player,$chat));
			if (!$ev->isCancelled()) {
				if (MPMU::apiVersion("1.12.0")) {
					$s = $this->owner->getServer();
					$s->broadcastMessage($s->getLanguage()->translateString(
						$ev->getFormat(),
						[$ev->getPlayer()->getDisplayName(), $ev->getMessage()]),
												$ev->getRecipients());
				} else {
					$this->owner->getServer()->broadcastMessage(sprintf(
						$ev->getFormat(),
						$ev->getPlayer()->getDisplayName(),
						$ev->getMessage()),$ev->getRecipients());
				}
			}
		} else {
			$cmdline = implode(' ',$args);
			$sender->sendMessage(mc::_("Running command as %1%",$n));
			$this->owner->getServer()->dispatchCommand($player,$cmdline);
		}
		return true;
	}
}
