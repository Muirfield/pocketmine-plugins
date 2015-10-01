<?php
//= module:broadcast-tp
//: Broadcast player teleports
//:
//: This listener module will broadcast when a player teleports to
//: another location.  It also generates some smoke and plays a sound.
//:

namespace aliuly\grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\sound\FizzSound;

use aliuly\common\mc;
use aliuly\common\MPMU;

class BcTpMgr implements Listener {
	public $owner;
	protected $world;
	protected $local;

	static public function defaults() {
		//= cfg:broadcast-tp
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
		//echo __METHOD__.",".__LINE__."\n"; //##DEBUG
	}
	private function addFx($xpos) {
		if (!MPMU::apiVersion("1.12.0")) return;
		foreach ($xpos as $pos) {
			for ($i=0;$i<20;$i++) {
				$pos->getLevel()->addParticle(new DustParticle(self::randVector($pos),(mt_rand()/mt_getrandmax())*2,255,255,255));
			}
			$pos->getLevel()->addSound(new FizzSound($pos));
		}
	}
  /**
	 * @priority MONITOR
	 */
  public function onRespawnEvent(PlayerRespawnEvent $ev) {
		$pl = $ev->getPlayer();
		$this->addFx([$pl]);
	}

	/**
	 * @priority MONITOR
	 */
	public function onTeleport(EntityTeleportEvent $ev) {
		if ($ev->isCancelled()) return;
		$pl = $ev->getEntity();
		if (!($pl instanceof Player)) return;
		$from = $ev->getFrom();
		if (!$from->getLevel()) $from->setLevel($pl->getLevel());
		$to = $ev->getTo();
		if (!$to->getLevel()) $to->setLevel($pl->getLevel());

		if ($from->getLevel()->getName() != $to->getLevel()->getName()) {
			if ($this->world) {
				$this->addFx([$from,$to]);
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
			$this->addFx([$from,$to]);
			$this->owner->getServer()->broadcastMessage(
				mc::_("%1% teleported away!",$pl->getName()));
		}
	}
}
