<?php
/**
 ** MODULE:join-mgr
 ** Announce joining ops, and show server motd
 **
 ** This listener module will broadcast a message for ops joining
 ** a server.
 **
 ** Also, it will show the server's motd on connect.
 **
 ** CONFIG:join-mgr
 **
 ** * adminjoin - broadcast whenever an op joins
 ** * servermotd - show the server's motd when joining
 **/



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
