<?php
namespace grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;

class FreezeMgr implements Listener {
	static protected $spamDelay = 30;
	public $owner;
	protected $frosties;
	protected $nospam;
	protected $hard;
	public function __construct(Plugin $plugin,$hard) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->frosties = [];
		$this->nospam = [];
		$this->hard = $hard;
	}
	public function freeze($name) {
		if (isset($this->frosties[$name])) {
			return "$name is already frozen!";
		}
		$this->frosties[$name] = $name;
		return "";
	}
	public function thaw($name) {
		if (isset($this->nospam[$name])) unset($this->nospam[$name]);
		if (!isset($this->frosties[$name])) {
			return "$name is not frozen!";
		}
		unset($this->frosties[$name]);
		return "";

	}
	public function getFrosties() {
		return $this->frosties;
	}
	public function onMove(PlayerMoveEvent $ev) {
		if ($ev->isCancelled()) return;
		$p = $ev->getPlayer();
		$n = $p->getName();
		if (isset($this->frosties[$n])) {
			if ($this->hard) {
				$ev->setCancelled();
			} else {
				// Lock position but still allow to turn around
				$to = clone $ev->getFrom();
				$to->yaw = $ev->getTo()->yaw;
				$to->pitch = $ev->getTo()->pitch;
				$ev->setTo($to);
			}
			if (isset($this->nospam[$n])) {
				if (time() - $this->nospam[$n] < self::$spamDelay) {
					return;
				}
			}
			$this->nospam[$n] = time();
			$p->sendMessage("You have been frozen!");
		}
	}
}
