<?php
namespace aliuly\grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class JoinMgr implements Listener {
	public $owner;
	protected $admjoin;
	protected $srvmotd;
	static $delay = 15;

	public function __construct(Plugin $plugin,$cfg) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->admjoin = $cfg["adminjoin"];
		$this->srvmotd = $cfg["servermotd"];
	}
	public function onPlayerJoin(PlayerJoinEvent $e) {
		$pl = $e->getPlayer();
		if ($pl == null) return;
		if ($this->srvmotd) {
			$pl->sendMessage($this->owner->getServer()->getMotd());
		}
		if ($this->admjoin && $pl->isOp()) {
			$pn = $pl->getDisplayName();
			$this->owner->getServer()->broadcastMessage("Server op $pn joined.");
		}
	}
}
