<?php
/**
 ** CONFIG:main
 **/
namespace aliuly\spawnmgr;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;

use pocketmine\command\CommandExecutor;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use pocketmine\level\Position;

use aliuly\spawnmgr\common\PluginCallbackTask;
use aliuly\spawnmgr\common\MPMU;
use aliuly\spawnmgr\common\mc;

class Main extends PluginBase implements Listener,CommandExecutor {
	protected $items;
	protected $armor;
	protected $pvp;
	protected $tnt;
	protected $spawnmode;
	protected $deathinv;
	protected $cmd;
	protected $reserved;
	protected $nest_egg;
	protected $deathpos;

	public function onEnable(){
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		mc::plugin_init($this,$this->getFile());

		$defaults = [
			"version" => $this->getDescription()->getVersion(),
			"# settings" => "Tunable parameters",
			"settings" => [
				"# tnt" => "true, allows explosion in spawn, false disallows it",
				"tnt" => true,
				"# pvp" => "true, allows pvp in spawn, false disallows it",
				"pvp" => true,
				"# reserved" => "number of reserved vip slots, false to disable",
				"reserved" => false,
				"# spawn-mode" => "default|world|always|home",
				"spawn-mode" => "default",
				"# on-death-inv" => "false|keep|clear|perms",
				"on-death-inv" => false,
				"# home-cmd" => "command to use when spawn-mode is home",
				"home-cmd" => "/home",
				"# death-pos" => "Save death location",
				"death-pos" => true,
			],
			"# armor" => "List of armor elements",
			"armor"=>[
				"chain_chestplate",
				"leather_pants",
				"leather_boots",
			],
			"# items" => "List of initial inventory",
			"items"=>[
				"STONE_SWORD,1",
				"WOOD,16",
				"COOKED_BEEF,5",
			],
			"# nest-egg" => "List for nest-egg",
			"nest-egg"=>[],
			//"GOLD_INGOT,64",
		];
		if (file_exists($this->getDataFolder()."config.yml")) {
			unset($defaults["items"]);
			unset($defaults["armor"]);
			unset($defaults["nest-egg"]);
		}
		$cfg=(new Config($this->getDataFolder()."config.yml",
							  Config::YAML,$defaults))->getAll();
		if (version_compare($cfg["version"],"1.3.0") < 0) {
			$this->getLogger()->warning(TextFormat::RED.mc::_("CONFIG FILE FORMAT CHANGED"));
			$this->getLogger()->warning(TextFormat::RED.mc::_("Please review your settings"));
		}
		$this->tnt = $cfg["settings"]["tnt"] ? null : new TntListener($this);
		$this->pvp = $cfg["settings"]["pvp"] ? null : new PvpListener($this);
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
				$this->getLogger()->error(mc::_("Invalid spawn-mode setting!"));
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
					$this->getLogger()->error(mc::_("Invalid on-death-inv setting!"));
				}
		}
		$this->armor = isset($cfg["armor"]) ? $cfg["armor"] : [];
		$this->items = isset($cfg["items"]) ? $cfg["items"] : [];
		if (isset($cfg["nest-egg"]) && count($cfg["nest-egg"])) {
			$sa = $this->getServer()->getPluginManager()->getPlugin("SimpleAuth");
			if ($sa !== null && $sa->isEnabled()) {
				$this->nest_egg = new SimpleAuthMgr($this,$cfg["nest-egg"]);;
			} else {
				$this->getLogger()->warning(mc::_("Missing SimpleAuth, nest-egg not enabled"));
			}
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->deathpos = $cfg["settings"]["death-pos"] ? [] : null;
	}

	public function onDeath(PlayerDeathEvent $e) {
		$p = $e->getEntity();
		if (!($p instanceof Player)) return;

		if ($this->deathpos !== null) {
			$n = strtolower($p->getName());
			$this->deathpos[$n] = new Position($p->getX(),$p->getY(),$p->getZ(),
														  $p->getLevel());
		}
		switch($this->deathinv) {
			case "keep":
				if (!$p->hasPermission("spawnmgr.keepinv")) return;
				$e->setKeepInventory(true);
				$e->setDrops([]);
				break;
			case "clear":
				if (!$p->hasPermission("spawnmgr.nodrops")) return;
				$e->setKeepInventory(false);
				$e->setDrops([]);
				break;
			case "perms":
				if ($p->hasPermission("spawnmgr.keepinv")) {
					$e->setKeepInventory(true);
				}
				if ($p->hasPermission("spawnmgr.nodrops")) {
					$e->setDrops([]);
				}
				break;
		}
	}
	public function onJoinLater($pn,$mode) {
		$pl = $this->getServer()->getPlayer($pn);
		if ($pl == null) return;
		switch($this->spawnmode) {
			case "world":
				$pl->teleport($pl->getLevel()->getSafeSpawn());
				break;
			case "always":
				$pl->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
				break;
			case "home":
				$this->getServer()->dispatchCommand($pl,$this->cmd);
				break;
		}
	}
	public function onQuit(PlayerQuitEvent $e) {
		if ($this->deathpos === null) return;
		$n = strtolower($e->getPlayer()->getName());
		if (isset($this->deathpos[$n])) unset($this->deathpos[$n]);
	}
	public function onJoin(PlayerJoinEvent $e) {
		$pl = $e->getPlayer();
		if ($this->reserved) {
			// Reserved slots!
			if (!$pl->hasPermission("spawnmgr.reserved")) {
				// Check if we have exceed non-reserved slots...
				$cnt = count($this->getServer()->getOnlinePlayers());
				if ($cnt > ($this->getServer()->getMaxPlayers() - $this->reserved)){
					$pl->kick("disconnectionScreen.serverFull", false);
					return;
				}
			}
		}

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
			$itemName = explode(" ",strtolower(MPMU::itemName($item)),2);
			if (count($itemName) != 2) {
				$this->getLogger()->error(mc::_("Invalid armor item: %1%",$j));
				continue;
			}
			list($material,$type) = $itemName;
			if (!isset($slot_map[$type])) {
				$this->getLogger()->error(mc::_("Invalid armor type: %1% for %2%",$type,$material));
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
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		switch ($cmd->getName()) {
			case "back":
				if ($this->deathpos === null) {
					$sender->sendMessage(TextFormat::RED.
												mc::_("/back command was disabled"));
					return true;
				}
				if (!MPMU::inGame($sender)) return true;
				$n = strtolower($sender->getName());
				if (!isset($this->deathpos[$n])) {
					$sender->sendMessage(TextFormat::RED.
												mc::_("You need to die first"));
					return true;
				}
				$sender->sendMessage(TextFormat::GREEN.
											mc::_("Teleporting you back to death location!"));
				$sender->teleport($this->deathpos[$n]);
				unset($this->deathpos[$n]);
				return true;
		}
		return false;
	}
}
