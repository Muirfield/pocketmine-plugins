<?php
//= module:broadcast-ft
//: Broadcast player's using FastTransfer
//:
//: This listener module will broadcast when a player uses FastTransfer

namespace aliuly\grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;

use pocketmine\Player;
use pocketmine\level\Position;
use shoghicp\FastTransfer\PlayerTransferEvent;
use pocketmine\math\Vector3;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\sound\FizzSound;
use aliuly\grabbag\common\mc;

class TransferMgr implements Listener {
	public $owner;

	protected static function randy($p,$r,$o) {
		return $p+(mt_rand()/mt_getrandmax())*$r-$o;
	}
	protected static function randVector(Vector3 $center) {
		return new Vector3(self::randy($center->getX(),0.5,-0.25),
								 self::randy($center->getY(),2,0),
								 self::randy($center->getZ(),0.5,-0.25));
	}

	public function __construct(Plugin $plugin) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	/**
	 * @priority MONITOR
	 */
	public function onTransfer(PlayerTransferEvent $ev) {
		if ($ev->isCancelled()) return;
		$pl = $ev->getPlayer();
		if (!($pl instanceof Player)) return;

		for ($i=0;$i<20;$i++) {
			$pl->getLevel()->addParticle(new DustParticle(self::randVector($pl),(mt_rand()/mt_getrandmax())*2,255,255,255));
		}
		$pl->getLevel()->addSound(new FizzSound($pl));

		$this->owner->getServer()->broadcastMessage(
					mc::_("%1% is transferring to %2%:%3%",
							$pl->getName(), $ev->getAddress(), $ev->getPort())
		);
	}
}
