<?php
namespace aliuly\nechest;
use pocketmine\scheduler\PluginTask;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Chest;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\math\Vector3;


class ParticleTask extends PluginTask{
	protected static function randy($p,$r,$o) {
		return $p+(mt_rand()/mt_getrandmax())*$r+$o;
	}
	protected static function randVector(Vector3 $center) {
		return new Vector3(self::randy($center->getX(),2,-0.5),
								 self::randy($center->getY(),0.5,0.5),
								 self::randy($center->getZ(),2,-0.5));
	}
	public function onRun($currentTick){
		if ($this->owner->isDisabled()) return;
		foreach ($this->owner->getServer()->getLevels() as $lv) {
			foreach ($lv->getTiles() as $tile) {
				if (!($tile instanceof Chest)) continue;
				if (!($this->owner->isNeChest($tile->getInventory()))) continue;
				$lv->addParticle(new SmokeParticle(self::randVector($tile)));
			}
		}
	}
}
