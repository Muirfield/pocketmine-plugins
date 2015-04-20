<?php
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\utils\TextFormat;

class CmdSlay extends BaseCommand implements Listener {

	public function __construct($owner) {
		parent::__construct($owner);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->enableCmd("slay",
							  ["description" => "kill a player with optional message",
								"usage" => "/slay <player> [message]",
								"permission" => "gb.cmd.slay"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "slay") return false;
		if (!isset($args[0])) {
			$sender->sendMessage("Must specify a player to slay");
			return false;
		}
		$victim = $this->owner->getServer()->getPlayer($n = array_shift($args));
		if ($victim == null) {
			$sender->sendMessage("Player $n was not found!");
			return true;
		}
		if (count($args)) {
			$this->setState($victim,[time(),implode(" ",$args)]);
		} else {
			$this->unsetState($victim);
		}
		$victim->setHealth(0);
		$sender->sendMessage(TextFormat::RED.$victim->getName()." has been slain.");
		return true;
	}
	/**
	 * @priority LOW
	 */
	public function onPlayerDeath(PlayerDeathEvent $e) {
		list($timer,$msg) = $this->getState($e->getEntity(),[0,""]);
		if (time() - $timer > 1) return;
		$e->setDeathMessage($msg);
		$this->unsetState($e->getEntity());
	}
}
