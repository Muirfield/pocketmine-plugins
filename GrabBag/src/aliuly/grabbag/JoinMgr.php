<?php
//= module:join-mgr
//: Customize players when they joins the server
//:
//: This module does the following:
//:
//: - Broadcast a message when an op joins
//: - Show the server's motd on connect.
//: - Keeps slots reserved for Ops (or VIPs)
//: - Players can start with equipment
//: - Always Spawn functionality

namespace aliuly\grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\item\Item;

use aliuly\common\PermUtils;
use aliuly\common\mc;
use aliuly\common\PluginCallbackTask;
use aliuly\common\ArmorItems;

class JoinMgr implements Listener {
	public $owner;
	protected $admjoin;
	protected $srvmotd;
	protected $reserved;
	protected $items;
	protected $armor;
	protected $spawn;

	static public function defaults() {
		//= cfg:join-mgr
		return [
			"# adminjoin" => "broadcast whenever an op joins",
			"adminjoin" => true,
			"# servermotd" => "show the server's motd when joining",
			"servermotd" => true,
			"# reserved" => "Number of reserved slots (0 to disable)",
			"reserved" => 0,
			"# spawn-items" => "List of items to include when joining",
			"join-items" => [],
			"# spawn-armor" => "List of armor to include when joining",
			"join-armor" => [],
			"# spawn" => "default, always, world, home, perms",
			"spawn" => "default",
		];
	}


	public function __construct(Plugin $plugin,$cfg) {
		$this->owner = $plugin;
		PermUtils::add($this->owner, "gb.join.reserved", "players with this permission can use reserved slots", "op");
		PermUtils::add($this->owner, "gb.join.giveitems", "receive items on join", "true");
		PermUtils::add($this->owner, "gb.join.givearmor", "receive armor on join", "true");

		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);

		$this->admjoin = $cfg["adminjoin"];
		$this->srvmotd = $cfg["servermotd"];
		$this->reserved = $cfg["reserved"];
		$this->items = $cfg["join-items"];
		$this->armor = $cfg["join-armor"];
		$this->spawn = $cfg["spawn"];
		if ($this->spawn == "perms") {
			PermUtils::add($this->owner, "gb.join.spawn.default", "Players with this permission join according to PocketMine defaults", "false");
			PermUtils::add($this->owner, "gb.join.spawn.always", "Players with this permission will always spawn on the default world on join", "false");
			PermUtils::add($this->owner, "gb.join.spawn.world", "Players with this permission will spawn in the last world on join", "false");
			PermUtils::add($this->owner, "gb.join.spawn.home", "Players with this permission will join in their Home location", "false");
		}
	}
	public function onPlayerJoin(PlayerJoinEvent $e) {
		$pl = $e->getPlayer();
		if ($pl == null) return;
		if ($this->reserved > 0 && !$pl->hasPermission("gb.join.reserved")) {
			// Check if we should kick this player...
			if (count($this->owner->getServer()->getOnlinePlayers())+$this->reserved >= $this->owner->getServer()->getMaxPlayers()) {
				$this->owner->getServer()->getScheduler()->scheduleDelayedTask(
					new PluginCallbackTask($this->owner,[$this,"serverFull"],[$pl->getName()]), 5
				);
				return;
			}
		}
		if ($this->srvmotd) {
			$pl->sendMessage($this->owner->getServer()->getMotd());
		}
		if ($this->admjoin && $pl->isOp()) {
			$pn = $pl->getDisplayName();
			$this->owner->getServer()->broadcastMessage(mc::_("Server op $pn joined."));
		}
		if (!pl->isCreative()) {
			if (count($this->items) && $pl->hasPermission("gb.join.giveitems")) $this->giveItems($pl);
			if (count($this->armor) && $pl->hasPermission("gb.join.givearmor")) $this->giveArmor($pl);
		}
		$this->alwaysSpawn($pl);
	}
	public function alwaysSpawn(Player $pl) {
		$spawn = $this->spawn;
		if ($spawn == "perms") {
			foreach(["always","world","home","default"] as $spawn) {
				if ($pl->hasPermission("gb.join.spawn.".$spawn)) break;
			}
		}
		$this->owner->getServer()->getScheduler()->scheduleDelayedTask(
			new PluginCallbackTask($this->owner,[$this,"delayedSpawn"],[$pl->getName(),$spawn]), 5
		);
	}
	public function delayedSpawn($n,$spawn) {
		$pl = $this->owner->getServer()->getPlayer($n);
		if ($pl === null) return;
		switch ($spawn) {
			case "always":
				$pos = $this->owner->getServer()->getDefaultLevel()->getSafeSpawn();
				break;
			case "world":
				$pos = $pl->getLevel()->getSafeSpawn();
				break;
			case "home":
				$pos = $this->owner->api->getHome($pl,$pl->getLevel());
				if ($pos === null) return;
				break;
			default:
				return;
		}
		$pl->teleport($pos);
	}

	public function serverFull($n) {
		$pl = $this->owner->getServer()->getPlayer($n);
		if ($pl === null) return;
		$pl->kick("disconnectionScreen.serverFull",false);
	}
	public function giveItems(Player $pl) {
		// Figure out if the inventory is empty...
		$cnt = 0;
		$max = $pl->getInventory()->getSize();
		foreach ($pl->getInventory()->getContents() as $slot => &$item) {
			if ($slot < $max) ++$cnt;
		}
		if ($cnt) return;
		// This player has nothing... let's give them some to get started...
		foreach ($this->items as $i) {
			$r = explode(",",$i);
			if (count($r) != 2) continue;
			$item = Item::fromString($r[0]);
			$item->setCount(intval($r[1]));
			$pl->getInventory()->addItem($item);
		}
	}
	public function giveArmor(Player $pl) {
		$armor = [];
		foreach ($this->armor as $j) {
			$item = Item::fromString($j);
			$slot = ArmorItems::getArmorPart($item->getId());
			if ($slot == ArmorItems::ERROR) {
				$this->getLogger()->error(mc::_("Invalid armor item: %1%",$j));
				continue;
			}
			$armor[$slot] = $item;
		}
		foreach ($armor as $slot => $item) {
			if ($pl->getInventory()->getArmorItem($slot)->getID()!=0) continue;
			$pl->getInventory()->setArmorItem($slot,clone $item);
		}
	}
}
