<?php
namespace aliuly\livesigns;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\utils\Config;
use pocketmine\Player;
use aliuly\livesigns\common\PluginCallbackTask;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\math\Vector3;

//echo __METHOD__.",".__LINE__."\n";//##DEBUG

class ParticleTxt implements Listener{
	protected $owner;
	protected $particles;
	protected $cfgtxt;

	public function __construct(Plugin $owner,$ticks){
		$this->owner = $owner;
		$this->particles = [];
		$this->cfgtxt = [];
		$this->loadFloats();
		$owner->getServer()->getPluginManager()->registerEvents($this,$owner);
		$owner->getServer()->getScheduler()->scheduleRepeatingTask(
			new PluginCallbackTask($owner,[$this,"updateTimer"],[]),$ticks
		);
		$this->factory();
	}
	public function factory() {
		foreach ($this->cfgtxt as $world=>$plst) {

			if (!isset($this->particles[$world])) $this->particles[$world] = [];
			$level = $this->owner->getServer()->getLevelByName($world);

			foreach($plst as $id=>$item) {
				if (isset($this->particles[$world][$id])) {
					// Already exists... check if the text has changed...
					$text = $this->owner->getLiveText($item["text"],$item["opts"]);
					if ($text === null) continue;
					$text = implode("\n",$text);
					if ($text == $this->particles[$world][$id]["text"]) continue;
					$pp = $this->particles[$world][$id]["particle"];
					$pp->setText($text);
					if ($level) $level->addParticle($pp);
				} else {
					$text = $this->owner->getLiveText($item["text"],$item["opts"]);
					if ($text === null) continue;
					$text = implode("\n",$text);
					list($x,$y,$z) = $item["pos"];
					if ($y < 0) { // Use height map...
						if (!$level) continue;
						$y = $level->getHighestBlockAt($x,$z) - $y;
					}
					$pp = new FloatingTextParticle(new Vector3($x,$y,$z),"",$text);
					if($level) $level->addParticle($pp);
					$this->particles[$world][$id] = [
						"particle" => $pp,
						"text" => $text,
					];
				}
			}
		}
		// Remove outdated particles
		foreach (array_keys($this->particles) as $world) {
			$level = $this->owner->getServer()->getLevelByName($world);
			if (isset($this->cfgtxt[$world])) {
				foreach (array_keys($this->particles[$world]) as $id) {
					if (isset($this->cfgtxt[$world][$id])) continue;
					if ($level) {
						$pp = $this->particles[$world][$id]["particle"];
						$pp->setInvisible();
						$level->addParticle($pp);
					}
					unset($this->particles[$world][$id]);
				}
			} else {
				if ($level) {
					foreach ($this->particles[$world] as $id=>$pp) {
						$pp["particle"]->setInvisible();
						$level->addParticle($pp["particle"]);
					}
				}
				unset($this->particles[$world]);
			}
		}
	}

	public function loadFloats() {
		$path = $this->owner->getDataFolder()."floats.yml";
		if (!file_exists($path)) return;
		$cf = (new Config($path,Config::YAML))->getAll();
		$this->cfgtxt = [];
		foreach ($cf as $world=>$txts) {
			$this->cfgtxt[$world] = [];
			foreach ($txts as $item) {
				if (!isset($item["pos"])) continue;
				if (!isset($item["text"])) continue;
				$pos = array_map("floatval",array_map("trim",explode(":",$item["pos"])));
				if (count($pos) != 3) continue;
				$id = implode(":",[$pos[0],$pos[1],$pos[2],$item["text"]]);
				$this->cfgtxt[$world][$id] = [
					"pos" => $pos,
					"text" => trim($item["text"]),
					"opts" => null,
				];
				if (isset($item["opts"]))
					$this->cfgtxt[$world][$id]["opts"] = $item["opts"];
			}
		}
	}
	public function saveFloats() {
		$path = $this->owner->getDataFolder()."floats.yml";
		$cf = [];
		foreach ($this->cfgtxt as $world => $txts) {
			$cf[$world] = [];
			foreach ($txts as $item) {
				$cf[$world][] = [
					"pos" => implode(":",$item["pos"]),
					"text" => $item["text"],
				];
				if ($item["opts"] !== null)
					$cf[$world][count($cf[$world])-1]["opts"] = $item["opts"];
			}
		}
		$yml = new Config($path,Config::YAML,[]);
		$yml->setAll($cf);
		$yml->save();
	}
	public function updateTimer() {
		$this->factory();
	}

	public function onTeleport(EntityTeleportEvent $ev) {
		if ($ev->isCancelled()) return;
		$pl = $ev->getEntity();
		if (!($pl instanceof Player)) return;
		$to = $ev->getTo();
		if (!$to->getLevel()) return;
		$level = $to->getLevel();
		if (!$this->particles[$level->getName()]) return;
		foreach ($this->particles[$level->getName()] as $id=>$ppt) {
			$level->addParticle($ppt["particle"]);
		}
	}

	public function addFloat($location,$text,$opts) {
		$this->loadFloats();
		$world = $location->getLevel()->getName();
		if (!isset($this->cfgtxt[$world])) $this->cfgtxt[$world] = [];
		$id = implode(":",[
			$location->getX(),$location->getY(),$location->getZ(),$text
		]);
		$this->cfgtxt[$world][$id] = [
			"pos" => [$location->getX(),$location->getY(),$location->getZ()],
			"text" => $text,
			"opts" => $opts,
		];
		$this->saveFloats();
		$this->factory();
	}
	public function rmFloat($world,$id) {
		$this->loadFloats();
		if(!isset($this->cfgtxt[$world])) return false;
		if(!isset($this->cfgtxt[$world][$id])) return false;
		unset($this->cfgtxt[$world][$id]);
		if (count($this->cfgtxt[$world]) == 0) unset($this->cfgtxt[$world]);
		$this->saveFloats();
		$this->factory();
	}
	public function getCfg() { return $this->cfgtxt; }
	public function getParticles() { return $this->particles; }
}
