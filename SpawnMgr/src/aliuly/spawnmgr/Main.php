<?php
namespace aliuly\spawnmgr;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;

use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use aliuly\spawnmgr\common\PluginCallbackTask;

class Main extends PluginBase implements Listener {
	protected $items;
	protected $armor;
	protected $pvp;
	protected $tnt;
	protected $spawnmode;
	protected $deathinv;
	protected $cmd;
	protected $reserved;

	public function onEnable(){
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		$defaults = [
			"version" => $this->getDescription()->getVersion(),
			"settings" => [
				"tnt" => true,
				"pvp" => true,
				"reserved" => false,
				"spawn-mode" => "default",
				"on-death-inv" => false,
				"home-cmd" => "/home",
			],
			"armor"=>[
				"chain_chestplate",
				"leather_pants",
				"leather_boots",
			],
			"items"=>[
				"STONE_SWORD,1",
				"WOOD,16",
				"COOKED_BEEF,5",
			],
			"nest-egg"=>[
				"GOLD_INGOT,64",
			],
		];
		if (file_exists($this->getDataFolder()."config.yml")) {
			unset($defaults["items"]);
			unset($defaults["armor"]);
			unset($defaults["nest-egg"]);
		}
		$cfg=(new Config($this->getDataFolder()."config.yml",
							  Config::YAML,$defaults))->getAll();
		if (version_compare($cfg["version"],"1.1.0") <= 0) {
			$this->getLogger()->warning(TextFormat::RED."CONFIG FILE FORMAT CHANGED");
			$this->getLogger()->warning(TextFormat::RED."Please review your settings");
		}
		$this->tnt = $cfg["settings"]["tnt"];
		$this->pvp = $cfg["settings"]["pvp"];
		$this->reserved = $cfg["settings"]["reserved"];
		switch(strtolower($cfg["settings"]["spawn-mode"])) {
			case "home":
			case "default":
			case "world":
			case "always":
				$this->spawnmode = strtolower($cfg["settings"]["spawn-mode"]);
				break;
			default:
				$this->spawnmode = "default";
				$this->getLogger()->error("Invalid spawn-mode setting!");
		}
		$this->cmd = $cfg["settings"]["home-cmd"];
		switch(strtolower($cfg["settings"]["on-death-inv"])) {
			case "keep":
			case "clear":
			case "perms":
				$this->deathinv = strtolower($cfg["settings"]["on-death-inv"]);
				break;
			default:
				if ($cfg["settings"]["on-death-inv"] === false ||
					 $cfg["settings"]["on-death-inv"] === "") {
					$this->deathinv = false;
				} else {
					$this->deathinv = false;
					$this->getLogger()->error("Invalid on-death-inv setting!");
				}
		}
		$this->armor = isset($cfg["armor"]) ? $cfg["armor"] : [];
		$this->items = isset($cfg["items"]) ? $cfg["items"] : [];
		if (isset($cfg["nest-egg"]) && count($cfg["nest-egg"])) {
			$sa = $this->getServer()->getPluginManager()->getPlugin("SimpleAuth");
			if ($sa !== null && $sa->isEnabled()) {
				$this->getServer()->getPluginManager()->registerEvents(new SimpleAuthMgr($this,$cfg["nest-egg"]),$this);
			}
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function itemName(Item $item) {
		$items = [];
		$constants = array_keys((new \ReflectionClass("pocketmine\\item\\Item"))->getConstants());
		foreach ($constants as $constant) {
			$id = constant("pocketmine\\item\\Item::$constant");
			$constant = str_replace("_", " ", $constant);
			$items[$id] = $constant;
		}
		$n = $item->getName();
		if ($n != "Unknown") return $n;
		if (isset($items[$item->getId()])) return $items[$item->getId()];
		return $n;
	}


	public function mwteleport($pl,$pos) {
		if (($pos instanceof Position) &&
			 ($mw = $this->owner->getServer()->getPluginManager()->getPlugin("ManyWorlds")) != null) {
			// Using ManyWorlds for teleporting...
			$mw->mwtp($pl,$pos);
		} else {
			$pl->teleport($pos);
		}
	}
	public function onPlayerKick(PlayerKickEvent $event){
		if (!$this->reserved) return;
		if ($event->getReason() == "server full" &&
			 $event->getReason() == "disconnectionScreen.serverFull") {
			if (!$event->getPlayer()->hasPermission("spawnmgr.reserved"))
				return;
			if($this->reserved !== true) {
				// OK, we do have a limit...
				if(count($this->getServer()->getOnlinePlayers()) >
					$this->getServer()->getMaxPlayers() + $this->reserved) return;
			}
			$ev->setCancelled();
			return;
		}
		// Not server full message...
	}
	public function onDeath(PlayerDeathEvent $e) {
		switch($this->deathinv) {
			case "keep":
				if (!$e->getEntity()->hasPermission("spawnmgr.keepinv")) return;
				$e->setKeepInventory(true);
				$e->setDrops([]);
				break;
			case "clear":
				if (!$e->getEntity()->hasPermission("spawnmgr.nodrops")) return;
				$e->setKeepInventory(false);
				$e->setDrops([]);
				break;
			case "perms":
				if ($e->getEntity()->hasPermission("spawnmgr.keepinv")) {
					$e->setKeepInventory(true);
				}
				if ($e->getEntity()->hasPermission("spawnmgr.nodrops")) {
					$e->setDrops([]);
				}
				break;
		}
	}
	public function onJoinLater($pn,$mode) {
		$pl = $this->getServer()->getPlayer($pn);
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if ($pl == null) return;
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		switch($this->spawnmode) {
			case "world":
				echo __METHOD__.",".__LINE__."\n";//##DEBUG
				$pl->teleport($pl->getLevel()->getSafeSpawn());
				break;
			case "always":
				echo __METHOD__.",".__LINE__."\n";//##DEBUG
				$pl->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
				break;
			case "home":
				echo __METHOD__.",".__LINE__."\n";//##DEBUG
				$this->getServer()->dispatchCommand($pl,$this->cmd);
				break;
		}
	}

	public function onJoin(PlayerJoinEvent $e) {
		$pl = $e->getPlayer();
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if (!$pl->hasPermission("spawnmgr.spawnmode")) return;
		$this->getServer()->getScheduler()->scheduleDelayedTask(new PluginCallbackTask($this,[$this,"onJoinLater"],[$pl->getName()]), 10);
	}
	private function giveArmor($pl) {
		if (!$pl->hasPermission("spawnmgr.receive.armor")) return;

		$slot_map = [ "helmet" => 0, "chestplate" => 1, "leggings" => 2,
						  "boots" => 3 , "cap" => 0, "tunic" => 1,
						  "pants" => 2 ];
		$inventory = [];
		foreach ($this->armor as $j) {
			$item = Item::fromString($j);
			$itemName = explode(" ",strtolower($this->itemName($item)),2);
			if (count($itemName) != 2) {
				$this->getLogger()->error("Invalid armor item: $j");
				continue;
			}
			list($material,$type) = $itemName;
			if (!isset($slot_map[$type])) {
				$this->getLogger()->error("Invalid armor type: $type for $material");
				continue;
			}
			$slot = $slot_map[$type];
			$inventory[$slot] = $item;
		}
		foreach ($inventory as $slot => $item) {
			if ($pl->getInventory()->getArmorItem($slot)->getID()!=0) continue;
			$pl->getInventory()->setArmorItem($slot,clone $item);
		}
	}
	private function giveItems($pl) {
		if (!$pl->hasPermission("spawnmgr.receive.items")) return;
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

	public function onRespawn(PlayerRespawnEvent $e) {
		$pl = $e->getPlayer();
		if (!($pl instanceof Player)) return;
		if ($pl->isCreative()) return;
		$this->giveItems($pl);
		$this->giveArmor($pl);
	}
	public function onPvP(EntityDamageEvent $ev) {
		if ($ev->isCancelled()) return;
		if ($this->pvp) return;
		if(!($ev instanceof EntityDamageByEntityEvent)) return;
		$et = $ev->getEntity();
		if(!($et instanceof Player)) return;
		$sp = $et->getLevel()->getSpawnLocation();
		$dist = $sp->distance($et);
		if ($dist > $this->getServer()->getSpawnRadius()) return;
		$ev->setCancelled();
	}
	public function onExplode(EntityExplodeEvent $ev){
		if ($ev->isCancelled()) return;
		if ($this->tnt) return;
		$et = $ev->getEntity();
		$sp = $et->getLevel()->getSpawnLocation();
		$dist = $sp->distance($et);
		if ($dist > $this->getServer()->getSpawnRadius()) return;
		$ev->setCancelled();
	}
}
