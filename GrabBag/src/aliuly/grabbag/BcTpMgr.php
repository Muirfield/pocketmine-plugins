<?php
/**
 ** MODULE:broadcast-tp
 ** Broadcast player's teleports
 **
 ** This listener module will broadcast when a player teleports to
 ** another location.
 **
 ** CONFIG:broadcast-tp
 **/
namespace aliuly\grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\level\particle\DustParticle;

use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;

class BcTpMgr implements Listener {
	public $owner;
	protected $world;
	protected $local;

	static public function defaults() {
		return [
			"# world" => "world broadcast setting.", // If true, will broadcast teleports accross worlds.
			"world" => true,
			"# local" => "local broadcast setting.", // This will broadcast teleports that go beyond this number.
			"local" => 500,
		];
	}
	protected static function randy($p,$r,$o) {
		return $p+(mt_rand()/mt_getrandmax())*$r-$o;
	}
	protected static function randVector(Vector3 $center) {
		return new Vector3(self::randy($center->getX(),0.5,-0.25),
								 self::randy($center->getY(),2,0),
								 self::randy($center->getZ(),0.5,-0.25));
	}

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

		if (MPMU::apiVersion("1.12.0")) {
			foreach ([$to,$from] as $pos) {
				for ($i=0;$i<20;$i++) {
					$pos->getLevel()->addParticle(new DustParticle(self::randVector($pos),(mt_rand()/mt_getrandmax())*2,255,255,255));
				}
			}
		}

		if ($from->getLevel()->getName() != $to->getLevel()->getName()) {
			if ($this->world) {
				$this->owner->getServer()->broadcastMessage(
					mc::_("%1% teleported to %2%",
							$pl->getName(),
							$to->getLevel()->getName()));
			}
			return;
		}
		if (!$this->local) return;
		$dist = $from->distance($to);
		if ($dist > $this->local) {
			$this->owner->getServer()->broadcastMessage(
				mc::_("%1% teleported away!",$pl->getName()));
		}
	}
}
