<?php
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;

class CmdFreezeMgr extends BaseCommand implements Listener {
	protected $frosties;
	protected $hard;

	public function __construct($owner,$hard) {
		parent::__construct($owner);
		$this->hard = $hard;
		$this->enableCmd("freeze",
							  ["description" => "freeze player",
								"usage" => "/freeze [player]",
								"permission" => "gb.cmd.freeze"]);
		$this->enableCmd("thaw",
							  ["description" => "thaw player",
								"usage" => "/thaw [player]",
								"aliases" => ["unfreeze"],
								"permission" => "gb.cmd.freeze"]);
		$this->frosties = [];
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (count($args) == 0) {
			$sender->sendMessage("Frozen: ".count($this->frosties));
			if (count($this->frosties))
				$sender->sendMessage(implode(", ",$this->frosties));
			return true;
		}
		switch ($cmd->getName()) {
			case "freeze":
				foreach ($args as $n) {
					$player = $this->owner->getServer()->getPlayer($n);
					if ($player) {
						$this->frosties[strtolower($player->getName())] = $player->getName();
						$player->sendMessage("You have been frozen by ".
													$sender->getName());
						$sender->sendMessage("$n is frozen.");
					} else {
						$sender->sendMessage("$n not found.");
					}
				}
				return true;
			case "thaw":
				foreach ($args as $n) {
					if (isset($this->frosties[strtolower($n)])) {
						unset($this->frosties[strtolower($n)]);
						$player = $this->owner->getServer()->getPlayer($n);
						if ($player) {
							$player->sendMessage("You have been thawed by ".
														$sender->getName());
						}
						$sender->sendMessage("$n is thawed");
					} else {
						$sender->sendMessage("$n not found or not thawed");
					}
				}
				return true;
		}
		return false;
	}
	public function onMove(PlayerMoveEvent $ev) {
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if ($ev->isCancelled()) return;
		$p = $ev->getPlayer();
		if (isset($this->frosties[strtolower($p->getName())])) {
			if ($this->hard) {
				$ev->setCancelled();
			} else {
				// Lock position but still allow to turn around
				$to = clone $ev->getFrom();
				$to->yaw = $ev->getTo()->yaw;
				$to->pitch = $ev->getTo()->pitch;
				$ev->setTo($to);
			}
		}
	}
}
