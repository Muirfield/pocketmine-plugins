<?php
namespace aliuly\scorched;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandExecutor;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;

use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\EnumTag;
use pocketmine\nbt\tag\FloatTag;

use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\level\Explosion;
use pocketmine\level\Position;
use pocketmine\block\Block;
use pocketmine\entity\Projectile;
use pocketmine\math\Vector3;

class Main extends PluginBase implements CommandExecutor, Listener {
	protected $shooters;
	protected $dumdums;
	protected $presets;
	protected $cfg;
	protected $mine;

	// Access and other permission related checks
	private function inGame(CommandSender $sender,$msg = true) {
		if ($sender instanceof Player) return true;
		if ($msg) $sender->sendMessage("You can only use this command in-game");
		return false;
	}

	// Event handlers
	public function checkMove(Position $p) {
		if (!$this->cfg["mines"]) return false;
		$bl = [];
		for ($i =1; $i < 4;$i++) {
			$bl[$i] = $p->getLevel()->getBlockIdAt($p->getX(),$p->getY()-$i,$p->getZ());
		}
		for ($i=1;$i < 3; $i++) {
			if ($bl[$i] == $this->mine["block1"]) {
				if (isset($this->mine["block2"])
					 && $this->mine["block2"] != $bl[$i+1]) continue;
				// explode!
				$p->getLevel()->setBlockIdAt($p->getX(),
													  $p->getY()-$i,
													  $p->getZ(),0);
				$this->scorchit(new Position($p->getX()+0.5,
													  $p->getY()-$i+0.5,
													  $p->getZ()+0.5,$p->getLevel()),
									 new Vector3(0,0,0),1);
				return false;
			}
		}
		return false;
	}
	public function onMove(PlayerMoveEvent $ev) {
		if ($ev->isCancelled()) return;
		if ($this->checkMove($ev->getTo())) {
			$ev->setCancelled();
		}
		return;
	}
	public function onEntityMotion(EntityMotionEvent $ev) {
		if ($ev->isCancelled()) return;
		$et = $ev->getEntity();
		if (!($et instanceof Living)) return;
		if ($et instanceof Player) return; // Handled through PlayerMoveEvent
		$this->checkMove($et);
	}

	public function onQuit(PlayerQuitEvent $e) {
		$n = $e->getPlayer()->getName();
		if (!isset($this->shooters[$n])) unset($this->shooters[$n]);
		if (!isset($this->dumdums[$n])) unset($this->dumdums[$n]);
	}

	public function onItemHeld(PlayerItemHeldEvent $e) {
		if ($e->isCancelled()) return;
		$p = $e->getPlayer();
		$n = $p->getName();
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if ($e->getItem()->getID() == Item::BOW) return;
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if (isset($this->shooters[$n])) {
			unset($this->shooters[$n]);
			$p->sendMessage("Disarming RPG");
		}
		if (isset($this->dumdums[$n])) {
			unset($this->dumdums[$n]);
			$p->sendMessage("Unloading dumdums");
		}
	}
	public function breakBow($n) {
		$p = $this->getServer()->getPlayer($n);
		if (!$p) return;
		if (!$p->isOnline()) return;
		$hand = $p->getInventory()->getItemInHand();
		if ($hand->getID() != Item::BOW) return;
		$hand->setDamage($hand->getDamage() + $this->cfg["usage"]);
		$p->getInventory()->setItemInHand($hand);
	}
	private function misfire($bow) {
		$dam = $bow->getDamage();
		echo "DAM=$dam\n"; //## DEBUG
		if (mt_rand(0,$this->cfg["failure"]) < $dam &&
			 (mt_rand()/mt_getrandmax()) < $this->cfg["rate"]) {
			// Oh no... failed!
			return true;
		}
		return false;
	}

	public function onArrowHit(ProjectileHitEvent $ev) {
		//if ($ev->isCancelled()) return; // NOT CANCELLABLE!
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$et = $ev->getEntity();
		if (!$et->namedtag) return;
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$c = explode(":",$et->namedtag->getName());
		if (count($c) != 3) return;
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if ($c[0] != "dumdum") return;
		echo __METHOD__.",".__LINE__."\n";//##DEBUG

		$this->getServer()->getPluginManager()->callEvent($cc = new ExplosionPrimeEvent($et, intval($c[1])));
		if ($cc->isCancelled()) return;

		$explosion = new Explosion($et,intval($c[1]),$et);//,$source);
		if (!$c[2]) $explosion->explodeA();
		$explosion->explodeB();
	}
	public function onShoot(EntityShootBowEvent $e) {
		if ($e->isCancelled()) return;
		$p = $e->getEntity();
		if (!($p instanceof Player)) return;

		$n = $p->getName();
		if (isset($this->dumdums[$n])) {
			if ($this->misfire($e->getBow())) {
				$e->setCancelled(); // Bow broke!
				$p->sendMessage("Dumdum failure!");

				$this->getServer()->getPluginManager()->callEvent($cc = new ExplosionPrimeEvent($p, 4));
				if ($cc->isCancelled()) return;

				$explosion = new Explosion($p,$this->dumdums[$n][0]);
				if (!$this->dumdums[$n][1]) $explosion->explodeA();
				$explosion->explodeB();
				return;
			}
			$arrow = $e->getProjectile();
			$arrow->namedtag->setName("dumdum:".implode(":",$this->dumdums[$n]));
			return;
		}

		if (!isset($this->shooters[$n])) return;
		if (!isset($this->shooters[$n])) return;
		$e->setCancelled(); // Disable it and replace it with our own

		if (!$p->isCreative()) {
			if (!$this->checkAmmo($p,true)) {
				$p->sendMessage("You are out of grenades");
				$p->sendMessage("RPG disarmed");
				unset($this->shoters[$n]);
				return;
			}
			if ($this->misfire($e->getBow())) {
				$p->sendMessage("RPG misfired!");
				$this->fire($p,mt_rand(1,$this->shooters[$n][0]),0.01);
				return;
			}
			// Since we are cancelling the event, we change the damage later
			$this->getServer()->getScheduler()->scheduleDelayedTask(new PluginCallbackTask($this,[$this,"breakBow"],[$n]),5);
		}
		//echo "FORCE: ". $e->getForce()."\n"; //## DEBUG
		$this->fire($p,$this->shooters[$n][0],$this->shooters[$n][1]);
	}
	public function readyToExplode(ExplosionPrimeEvent $e) {
		if ($e->isCancelled()) return;

		$g = $e->getEntity();
		if (!$g->namedtag) return;
		if ($g->namedtag->getName() != "Scorched") return;
		if ((mt_rand()/mt_getrandmax()) < $this->cfg["rpg-noexplode"]) {

			$e->setCancelled();
			return;
		}
		$e->setForce($this->cfg["rpg-yield"]);
		if ($this->cfg["rpg-magic"]) $ev->setBlockBreaking(false);
	}

	// Standard call-backs
	public function onEnable(){
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		$defaults = [
			"version" => $this->getDescription()->getVersion(),
			"presets" => [
				"short" => [ 30, 0.5 ],
				"long" => [ 80, 1.0 ],
				"fast" => [ 20, 1.0 ],
			],
			"mines" => [
				"block1" => Block::TNT,
				"block2" => Block::REDSTONE_WIRE,
			],
			"settings" => [
				"failure" => 385,
				"rate" => 0.5,
				"usage" => 5,
				"max-speed" => 4.0,
				"min-speed" => 0.5,
				"max-fuse" => 120,
				"min-fuse" => 10,
				"rpg-yield" => 4,
				"rpg-magic" => false,
				"rpg-noexplode" => 0.10,
				"default-yield" => 2,
				"default-magic" => false,
				"forced-magic" => false,
				"no-magic" => false,
				"max-yield" => 5,
				"mines" => true,
			],
		];
		$cfg = (new Config($this->getDataFolder()."config.yml",
								 Config::YAML,$defaults))->getAll();
		$this->presets = $cfg["presets"];
		$this->cfg = $cfg["settings"];
		$this->mine = $cfg["mines"];
		$this->shooters = [];
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		switch($cmd->getName()) {
			case "dumdum":
				return $this->cmdDumDums($sender,$args);
			case "rpg":
				return $this->cmdRpg($sender,$args);
			case "fire":
				return $this->cmdFire($sender,$args);
			case "akira":
				return $this->cmdExplode($sender,$args);
		}
		return false;
	}
	private function checkAmmo(Player $p,$adj=false) {
		foreach ($p->getInventory()->getContents() as $slot=>$item) {
			if ($item->getID() != Item::TNT || $item->getCount() == 0) continue;

			if ($adj) {
				$count = $item->getCount();
				if ($count == 1) {
					// The last one...
					$p->getInventory()->clear($slot);
				} else {
					$item->setCount($count-1);
					$p->getInventory()->setItem($slot,$item);
				}
			}
			return true;
		}
		// Run out of grenades, disarm ...
		if (isset($this->shooters[$p->getName()])) {
			unset($this->shooters[$p->getName()]);
		}
		return false;
	}

	// Command implementations
	private function cmdExplode(CommandSender $c,$args) {
		if (!$this->inGame($c)) return false;
		$magic = false;
		$delay = 20;
		$yield = 5;
		foreach ($args as $i) {
			$i = strtolower($i);
			if ($i == "magic") {
				$magic = true;
			} elseif ($i == "no-magic" || $i == "nomagic") {
				$magic = false;
			} elseif (substr($i,0,strlen("yield=")) == "yield=") {
				$yield = intval(substr($i,strlen("yield=")));
			} elseif (substr($i,0,strlen("delay=")) == "delay=") {
				$delay = intval(substr($i,strlen("delay=")));
			} else {
				return false;
			}
		}
		$this->getServer()->getScheduler()->scheduleDelayedTask(new PluginCallbackTask($this,[$this,"akira"],[$c->getName(),new Position($c->getX(),$c->getY(),$c->getZ(),$c->getLevel()),$magic,$yield]),$delay);
		$c->sendMessage("GET THE HECK OUT OF HERE!");
		if ($delay > 40) {
			for ($i=1;$i<4;$i++) {
				$this->getServer()->getScheduler()->scheduleDelayedTask(new PluginCallbackTask($this,[$this,"countdown"],[(4-$i)."..."]),($delay/4)*$i);
			}
		}
		return true;
	}
	public function countdown($i) {
		$this->getServer()->broadcastMessage($i);
	}
	public function akira($n,$pos,$magic,$yield) {
		$this->getServer()->broadcastMessage("AAAAKIIIIIRAAAAA!!!!!");
		$source = $this->getServer()->getPlayer($n);

		$this->getServer()->getPluginManager()->callEvent($cc = new ExplosionPrimeEvent($source, $yield));
		if ($cc->isCancelled()) {
			//echo("Cancelled\n");//##DEBUG
			return;
		}

		$explosion = new Explosion($pos,$yield);
		if (!$magic) $explosion->explodeA();
		$explosion->explodeB();
	}

	// Command implementations
	private function cmdFire(CommandSender $c,$args) {
		if (!$this->inGame($c)) return false;

		$fuse = 40;
		$speed = 0.75;

		if (count($args)) {
			if (isset($this->presets[$args[0]])) $args = $this->presets[$args[0]];
			if (count($args) != 2) return false;
			$fuse = (int)$args[0];
			$speed = (float)$args[1];
			if ($speed > 4.0) $speed = 4.0;
			if ($speed < 0.5) $speed = 0.5;
			if ($fuse > 120) $fuse = 120;
			if ($fuse < 10) $fuse = 10;
		}

		if (!$c->isCreative()) {
			// Not in creative, we need to check inventories...
			if (!$this->checkAmmo($c,true)) {
				$c->sendMessage("Unable to fire RPG, you are out of grenades");
				return true;
			}
		}
		$c->sendMessage("Firing...");
		$this->fire($c,$fuse,$speed);
		return true;
	}
	private function cmdDumDums(CommandSender $c,$args) {
		if (!$this->inGame($c)) return false;
		$n = $c->getName();
		if (count($args) == 0) {
			$yield = $this->cfg["default-yield"];
			$magic = $this->cfg["default-magic"];
		} elseif (count($args) == 1 && $args[0] == "off") {
			if (!isset($this->dumdums[$n])) {
				unset($this->dumdums[$n]);
				$c->sendMessage("Turning off dumdums");
			} else {
				$c->sendMessage("Dumdums are off");
			}
			return true;
		} elseif (count($args) == 1 && is_numeric($args[0])) {
			$yield = intval($args[0]);
			$magic = false;
		} elseif (count($args) == 2 && is_numeric($args[0])) {
			$yield = intval($args[0]);
			$magic = true;
		} else {
			return false;
		}
		if ($yield > $this->cfg["max-yield"]) $yield = $this->cfg["max-yield"];
		if ($this->cfg["forced-magic"]) $magic = true;
		if ($this->cfg["no-magic"]) $magic = false;

		$hand = $c->getInventory()->getItemInHand();
		if ($hand->getID() != Item::BOW) {
			$c->sendMessage("Unable to load DumDums,\nyou must be holding a Bow");
			return true;
		}

		if (!isset($this->shooters[$n])) unset($this->shooters[$n]);

		$this->dumdums[$n] = [ $yield,$magic ];
		$c->sendMessage("Loaded dumdums. yield=$yield".($magic? " magical" : ""));
		return true;
	}

	private function cmdRpg(CommandSender $c,$args) {
		if (!$this->inGame($c)) return false;
		$n = $c->getName();
		if (count($args) == 0) {
			if (isset($this->shooters[$n])) {
				$c->sendMessage("RPG armed");
				$c->sendMessage("- Fuse:  ".$this->shooters[$n][0]);
				$c->sendMessage("- Speed: ".$this->shooters[$n][1]);
			} else {
				$c->sendMessage("RPG not armed");
			}
			return true;
		}
		if ($args[0] == "disarm" || $args[0] == "off") {
			if (isset($this->shooters[$n])) {
				unset($this->shooters[$n]);
				$c->sendMessage("Disarming RPG");
				return true;
			}
			$c->sendMessage("RPG disarmed");
			return true;
		}
		if (!$c->isCreative()) {
			// Not in creative, we need to check inventories...
			if (!$this->checkAmmo($c)) {
				$c->sendMessage("Unable to arm RPG, you are out of grenades");
				return true;
			}
		}
		$hand = $c->getInventory()->getItemInHand();
		if ($hand->getID() != Item::BOW) {
			$c->sendMessage("Unable to arm RPG, you must be holding a Bow");
			return true;
		}

		if (isset($this->presets[$args[0]])) $args = $this->presets[$args[0]];
		if (count($args) != 2) return false;
		$fuse = (int)$args[0];
		$speed = (float)$args[1];
		if ($speed > $this->cfg["max-speed"]) $speed = $this->cfg["max-speed"];
		if ($speed < $this->cfg["min-speed"]) $speed = $this->cfg["min-speed"];
		if ($fuse > $this->cfg["max-fuse"]) $fuse = $this->cfg["max-fuse"];
		if ($fuse < $this->cfg["min-fuse"]) $fuse = $this->cfg["min-fuse"];

		$this->shooters[$n] = [(int)$fuse,(float)$speed];
		$c->sendMessage("RPG armed! fuse=$fuse speed=$speed");
		if (!isset($this->dumdums[$n])) unset($this->dumdums[$n]);
		return true;
	}


	private function fire(Player $c,$fuse,$speed) {
		$pos = $c->getPosition();
		$pos->y += $c->getEyeHeight();

		$dir = $c->getDirectionVector();
		$dir->x = $dir->x * $speed;
		$dir->y = $dir->y * $speed;
		$dir->z = $dir->z * $speed;
		$this->scorchit($pos,$dir,$fuse);
	}
	private function scorchit($pos,$dir,$fuse) {
		$nbt =
			  new CompoundTag("",
								["Pos" => new EnumTag("Pos",
														 [new DoubleTag("", $pos->x),
														  new DoubleTag("", $pos->y),
														  new DoubleTag("", $pos->z)]),
								 "Motion" => new EnumTag("Motion",
															 [new DoubleTag("",$dir->x),
															  new DoubleTag("",$dir->y),
															  new DoubleTag("",$dir->z)]),
								 "Rotation" => new EnumTag("Rotation",
																[new FloatTag("", 0),
																 new FloatTag("", 0)]),
								 "Fuse" => new ByteTag("Fuse", $fuse)]);

		$entity = Entity::createEntity("PrimedTNT",
												 $pos->getLevel()->getChunk($pos->x >> 4, $pos->z >> 4),
												 $nbt);
		$entity->namedtag->setName("Scorched");
		$entity->spawnToAll();
		return true;
	}

}
