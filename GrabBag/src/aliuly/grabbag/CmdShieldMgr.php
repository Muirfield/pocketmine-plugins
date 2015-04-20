<?php
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;

class CmdShieldMgr extends BaseCommand implements Listener {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("shield",
							  ["description" => "makes player invulnerable",
								"usage" => "/shield",
								"permission" => "gb.cmd.shield"]);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (count($args) !== 0) return false;
		if ($cmd->getName() != "shield") return false;
		if (!$this->inGame($sender)) return true;
		$state = $this->getState($sender,false);
		if ($state) {
			$sender->sendMessage("Shields DOWN");
			$this->setState($sender,false);
		} else {
			$sender->sendMessage("Shields UP");
			$this->setState($sender,true);
		}
		return true;
	}

	public function onDamage(EntityDamageEvent $ev) {
		if ($ev->isCancelled()) return;
		if(!($ev instanceof EntityDamageByEntityEvent)) return;
		if (!($ev->getEntity() instanceof Player)) return;
		if (!$this->getState($ev->getEntity(),false)) return;
		$ev->setCancelled();
	}
}
