<?php
/**
 ** MODULE:broadcast-tp
 ** Broadcast player's teleports
 **
 ** This listener module will broadcast when a player teleports to
 ** another location.
 **
 ** CONFIG:broadcast-tp
 **
 ** * world - when true, it will broadcast when player teleport to other worlds
 ** * local - for local teleports, the minimum distance to broadcast
 **/
namespace aliuly\grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\Player;
use pocketmine\level\Position;

class BcTpMgr implements Listener {
	public $owner;
	protected $world;
	protected $local;

	public function __construct(Plugin $plugin,$cfg) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->world = $cfg["world"];
		$this->local = $cfg["local"];
		echo __METHOD__.",".__LINE__."\n"; //##DEBUG
	}
	/**
	 * @priority MONITOR
	 */
	public function onTeleport(EntityTeleportEvent $ev) {
		echo __METHOD__.",".__LINE__."\n"; //##DEBUG
		if ($ev->isCancelled()) return;
		$pl = $ev->getEntity();
		if (!($pl instanceof Player)) return;
		$from = $ev->getFrom();
		if (!$from->getLevel()) $from->setLevel($pl->getLevel());
		$to = $ev->getTo();
		if (!$to->getLevel()) $to->setLevel($pl->getLevel());

		if ($from->getLevel()->getName() != $to->getLevel()->getName()) {
			if ($this->world) {
				$this->owner->getServer()->broadcastMessage($pl->getName().
																		  " teleported to ".
																		  $to->getLevel()->getName());
			}
			return;
		}
		if (!$this->local) return;
		$dist = $from->distance($to);
		if ($dist > $this->local) {
			$this->owner->getServer()->broadcastMessage($pl->getName().
																	  " teleported away!");
		}
	}
}
