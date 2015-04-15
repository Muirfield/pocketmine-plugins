<?php
namespace grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;

class MuteMgr implements Listener {
	public $owner;
	protected $mutes;
	public function __construct(Plugin $plugin) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->mutes = [];
	}
	public function mute($name) {
		if (isset($this->mutes[$name])) {
			return "$name is already muted!";
		}
		$this->mutes[$name] = $name;
		return "";
	}
	public function unmute($name) {
		if (!isset($this->mutes[$name])) {
			return "$name is not muted!";
		}
		unset($this->mutes[$name]);
		return "";

	}
	public function getMutes() {
		return $this->mutes;
	}
	public function onChat(PlayerChatEvent $ev) {
		if ($ev->isCancelled()) return;
		$p = $ev->getPlayer();
		if (isset($this->mutes[$p->getName()])) {
			$p->sendMessage("You have been muted!");
			$ev->setCancelled();
		}
	}
}
