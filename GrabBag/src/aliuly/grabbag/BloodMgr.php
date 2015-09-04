<?php
//= module:blood-particles
//: Display particles when a player gets hit

namespace aliuly\grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\math\Vector3;

use aliuly\grabbag\common\mc;

class BloodMgr implements Listener {
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
	public function onDamage(EntityDamageEvent $ev) {
		if ($ev->isCancelled()) return;
		$damage = $ev->getDamage();
		$player = $ev->getEntity();
		if (!($player instanceof Player)) return;

		for ($i=0;$i<$damage;$i++) {
			$player->getLevel()->addParticle(new RedstoneParticle(self::randVector($player),(mt_rand()/mt_getrandmax())*2));
		}
	}
	/**
	 * @priority MONITOR
	 */
	public function onDeath(PlayerDeathEvent $ev) {
		$player = $ev->getEntity();
		if (!($player instanceof Player)) return;
		for ($i=0;$i<20;$i++) {
			$player->getLevel()->addParticle(new DustParticle(self::randVector($player),(mt_rand()/mt_getrandmax())*2,0,64,0));
		}
	}

}
