<?php
//= cmd:shield,Player_Management
//: player is protected from taking damage
//> usage: **shield**
//:
//: This will toggle your shield status.

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;
use aliuly\grabbag\common\PermUtils;

class CmdShieldMgr extends BasicCli implements Listener,CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);

		PermUtils::add($this->owner, "gb.cmd.shield", "Allow players to become invulnverable", "op");
		$this->enableCmd("shield",
							  ["description" => mc::_("makes player invulnerable"),
								"usage" => mc::_("/shield"),
								"permission" => "gb.cmd.shield"]);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function isShielded($player) {
		return $this->getState($player,false);
	}
	public function setShield($player, $mode) {
		if ($mode) {
			$this->setState($player,true);
		} else {
			$this->unsetState($player);
		}
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (count($args) !== 0) return false;
		if ($cmd->getName() != "shield") return false;
		if (!MPMU::inGame($sender)) return true;
		$state = $this->getState($sender,false);
		if ($state) {
			$sender->sendMessage(mc::_("Shields DOWN"));
			$this->unsetState($sender);
		} else {
			$sender->sendMessage(mc::_("Shields UP"));
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
