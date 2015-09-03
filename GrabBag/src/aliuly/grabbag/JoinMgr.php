<?php
//= module:join-mgr
//: Announce joining ops, and show server motd
//:
//: This listener module will broadcast a message for ops joining
//: a server.
//:
//: Also, it will show the server's motd on connect.

namespace aliuly\grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

use aliuly\grabbag\common\mc;

class JoinMgr implements Listener {
	public $owner;
	protected $admjoin;
	protected $srvmotd;
	static $delay = 15;

	static public function defaults() {
		//= cfg:join-mgr
		return [
			"# adminjoin" => "broadcast whenever an op joins",
			"adminjoin" => true,
			"# servermotd" => "show the server's motd when joining",
			"servermotd" => true,
		];
	}


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
			$this->owner->getServer()->broadcastMessage(mc::_("Server op $pn joined."));
		}
	}
}
