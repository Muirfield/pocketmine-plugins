<?php
/**
 **
 ** CONFIG:settings
 **
 ** This section you can enable/disable modules.
 ** You do this in order to avoid conflicts between different
 ** PocketMine-MP plugins.  It has one line per feature:
 **
 **    feature: true|false
 **
 ** If `true` the feature is enabled.  if `false` the feature is disabled.
 **
 **/
namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\level\Level;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\level\LevelUnloadEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;

class Main extends PluginBase implements Listener, CommandExecutor {
	protected $modules;
	protected $scmdMap;
	protected $wcfg;
	protected $spam;
	const SPAM_DELAY = 5;

	public function onEnable(){
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		$this->scmdMap = [
			"mgrs" => [],
			"help" => [],
			"usage" => [],
			"alias" => [],
			"permission" => [],
		];

		$mods = [
			"max-players" => [ "MaxPlayerMgr", false ],
			"protect" => [ "WpProtectMgr", true ],
			"border" => [ "WpBordersMgr", true ],
			//"pvp" => [ "WpPvpMgr", true ],
			//"motd" => [ "WpMotdMgr", false ],
			//"no-explode" => [ "WpNoExplodeMgr", false ],
			//"unbreakable" => [ "WpUnbreakableMgr",false ],
		];
		$defaults = [
			"version" => $this->getDescription()->getVersion(),
			"features" => [],
		];
		foreach ($mods as $i => $j) {
			$defaults["features"][$i] = $j[1];
		}
		$defaults["features"]["max-players"] = false;
		$cfg=(new Config($this->getDataFolder()."config.yml",
									  Config::YAML,$defaults))->getAll();

		$this->modules = [];
		foreach ($cfg["features"] as $i=>$j) {
			if (!isset($mods[$i])) {
				$this->getLogger()->info("Unknown feature \"$i\" ignored.");
				continue;
			}
			if (!$j) continue;
			$class = $mods[$i][0];
			if(strpos($class,"\\") === false) $class = __NAMESPACE__."\\".$class;
			if (isset($cfg[$i]))
				$this->modules[$i] = new $class($this,$cfg[$i]);
			else
				$this->modules[$i] = new $class($this);
		}
		if (count($this->modules)) {
			$this->state = [];
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
			$this->getLogger()->info("enabled ".count($this->modules)." features");
		} else {
			$this->getLogger()->info("NO features enabled");
			return;
		}
		$this->modules[] = new WpHelp($this);
		$this->wcfg = [];
		foreach ($this->getServer()->getLevels() as $level) {
			$this->loadCfg($level);
		}
		$this->spam = [];
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Save/Load configurations
	//
	//////////////////////////////////////////////////////////////////////
	public function loadCfg($world) {
		if ($world instanceof Level) $world = $world->getName();
		if (isset($this->wcfg[$world])) return true; // world is already loaded!
		if (!$this->getServer()->isLevelGenerated($world)) return false;
		if (!$this->getServer()->isLevelLoaded($world)) {
			$path = $this->getServer()->getDataPath()."worlds/".$world."/";
		} else {
			$level = $this->getServer()->getLevelByName($world);
			if (!$level) return false;
			$path = $level->getProvider()->getPath();
		}
		$path .= "wpcfg.yml";
		if (is_file($path)) {
			$this->wcfg[$world] = (new Config($path,Config::YAML,[]))->getAll();
			foreach ($this->modules as $i=>$mod) {
				echo "i=$i - ".get_class($mod)."\n";//##DEBUG
				if (isset($this->wcfg[$world][$i])) {
					$mod->setCfg($world,$this->wcfg[$world][$i]);
				} else {
					$mod->unsetCfg($world);
				}
			}
		} else {
			$this->wcfg[$world] = [];
			foreach ($this->modules as $i=>$mod) {
				$mod->unsetCfg($world);
			}
		}
		return true;
	}
	public function saveCfg($world) {
		if ($world instanceof Level) $world = $world->getName();
		if (!isset($this->wcfg[$world])) return false; // Nothing to save!
		if (!$this->getServer()->isLevelGenerated($world)) return false;
		if (!$this->getServer()->isLevelLoaded($world)) {
			$path = $this->getServer()->getDataPath()."worlds/".$world."/";
		} else {
			$level = $this->getServer()->getLevelByName($world);
			if (!$level) return false;
			$path = $level->getProvider()->getPath();
		}
		$path .= "wpcfg.yml";
		if (count($this->wcfg[$world])) {
			$yaml = new Config($path,Config::YAML,[]);
			$yaml->setAll($this->wcfg[$world]);
			$yaml->save();
		} else {
			unlink($path);
		}
		return true;
	}
	public function unloadCfg($world) {
		if ($world instanceof Level) $world = $world->getName();
		if (isset($this->wcfg[$world])) unset($this->wcfg[$world]);
		foreach ($this->modules as $i=>$mod) {
			$mod->unsetCfg($world);
		}
	}
	public function getCfg($world,$key,$default) {
		if ($world instanceof Level) $world = $world->getName();
		if ($this->getServer()->isLevelLoaded($world))
			$unload = false;
		else {
			$unload = true;
			if (!$this->loadCfg($world)) return $default;
		}
		if (isset($this->wcfg[$world]) && isset($this->wcfg[$world][$key])) {
			$res = $this->wcfg[$world][$key];
		} else {
			$res = $default;
		}
		if ($unload) $this->unloadCfg($world);
		return $res;
	}
	public function setCfg($world,$key,$value) {
		if ($world instanceof Level) $world = $world->getName();
		if ($this->getServer()->isLevelLoaded($world))
			$unload = false;
		else {
			$unload = true;
			if (!$this->loadCfg($world)) return false;
		}
		if (!isset($this->wcfg[$world]) || !isset($this->wcfg[$world][$key]) ||
			 $value !== $this->wcfg[$world][$key]) {
			if (!isset($this->wcfg[$world])) $this->wcfg[$world] = [];
			$this->wcfg[$world][$key] = $value;
			$this->saveCfg($world);
		}
		if (isset($this->modules[$key]))
			$this->modules[$key]->setCfg($world,$value);

		if ($unload) $this->unloadCfg($world);
		return true;
	}
	public function unsetCfg($world,$key) {
		if ($world instanceof Level) $world = $world->getName();
		if ($this->getServer()->isLevelLoaded($world))
			$unload = false;
		else {
			$unload = true;
			if (!$this->loadCfg($world)) return false;
		}
		if (isset($this->wcfg[$world])) {
			if (isset($this->wcfg[$world][$key])) {
				unset($this->wcfg[$world][$key]);
				$this->saveCfg($world);
			}
		}
		if (isset($this->modules[$key]))
			$this->modules[$key]->unsetCfg($world);
		if ($unload) $this->unloadCfg($world);
	}

	//////////////////////////////////////////////////////////////////////
	//
	// Event handlers
	//
	//////////////////////////////////////////////////////////////////////
	public function onPlayerQuit(PlayerQuitEvent $e) {
		$n = strtolower($e->getPlayer()->getName());
		if (isset($this->spam[$n])) unset($this->spam[$n]);
	}
	public function onLevelLoad(LevelLoadEvent $e) {
		$this->loadCfg($e->getLevel());
	}
	public function onLevelUnload(LevelUnloadEvent $e) {
		$this->unloadCfg($e->getLevel());
	}

	//////////////////////////////////////////////////////////////////////
	//
	// Command dispatcher
	//
	//////////////////////////////////////////////////////////////////////
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		if ($cmd->getName() != "worldprotect") return false;
		if ($sender instanceof Player) {
			$world = $sender->getLevel()->getName();
		} else {
			$level = $this->getServer()->getDefaultLevel();
			if ($level) {
				$world = $level->getName();
			} else {
				$world = null;
			}
		}
		if (isset($args[0]) && $this->getServer()->isLevelGenerated($args[0])) {
			$world = array_shift($args);
		}
		if ($world === null) {
			$sender->sendMessage("[WP] Must specify a world");
			return false;
		}
		if (count($args) == 0) {
			$sender->sendMessage("[WP] No subcommand specified");
			return false;
		}
		$scmd = strtolower(array_shift($args));
		if (isset($this->scmdMap["alias"][$scmd])) {
			$scmd = $this->scmdMap["alias"][$scmd];
		}
		if (!isset($this->scmdMap["mgrs"][$scmd])) {
			$sender->sendMessage("[WP] Unknown sub-command (try /wp help)");
			return false;
		}
		if (isset($this->scmdMapd["permission"][$scmd])) {
			if (!$sender->hasPermission($this->scmdMapd["permission"][$scmd])) {
				$sender->sendMessage("You are not allowed to do this");
				return true;
			}
		}
		if (!$this->isAuth($sender,$world)) return true;
		$callback = $this->scmdMap["mgrs"][$scmd];
		if ($callback($sender,$cmd,$scmd,$world,$args)) return true;
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if (isset($this->scmdMap["mgrs"]["help"])) {
			echo __METHOD__.",".__LINE__."\n";//##DEBUG
			$callback = $this->scmdMap["mgrs"]["help"];
			return $callback($sender,$cmd,$scmd,$world,["usage"]);
		}
		return false;
	}
	public function getScmdMap() {
		return $this->scmdMap;
	}
	public function registerScmd($cmd,$callable,$opts) {
		$cmd = strtolower($cmd);
		$this->scmdMap["mgrs"][$cmd] = $callable;

		foreach (["help","usage","permission"] as $p) {
			if(isset($opts[$p])) {
				$this->scmdMap[$p][$cmd] = $opts[$p];
			}
		}
		if (isset($opts["aliases"])) {
			foreach ($opts["aliases"] as $alias) {
				$this->scmdMap["alias"][$alias] = $cmd;
			}
		}
	}
	public function canPlaceBreakBlock(Player $c,$world) {
		$pname = strtolower($c->getName());
		if (isset($this->wcfg[$world]["auth"])
			 && count($this->wcfg[$world]["auth"])) {
			// Check if user is in auth list...
			if (isset($this->wcfg[$world]["auth"][$pname])) return true;
			return false;
		}
		if ($c->hasPermission("wp.cmd.protect.auth")) return true;
		return false;
	}

	public function isAuth($c,$world) {
		if (!($c instanceof Player)) return true;
		if (!isset($this->wcfg[$world])) return true;
		if (!isset($this->wcfg[$world]["auth"])) return true;
		if (!count($this->wcfg[$world]["auth"])) return true;
		$iusr = strtolower($c->getName());
		if (isset($this->wcfg[$world][$iusr])) return true;
		$c->sendMessage("[WP] You are not allowed to do this");
		return false;
	}
	public function authAdd($world,$usr) {
		$auth = $this->getCfg($world,"auth",[]);
		if (isset($auth[$usr])) return;
		$auth[$usr] = $usr;
		$this->setCfg($world,"auth",$auth);
	}
	public function authCheck($world,$usr) {
		$auth = $this->getCfg($world,"auth",[]);
		return isset($auth[$usr]);
	}
	public function authRm($world,$usr) {
		$auth = $this->getCfg($world,"auth",[]);
		if (!isset($auth[$usr])) return;
		unset($auth[$usr]);
		if (count($auth)) {
			$this->setCfg($world,"auth",$auth);
		} else {
			$this->unsetCfg($world,"auth");
		}
	}

	public function msg($pl,$txt) {
		$n = strtolower($pl->getName());
		if (isset($this->spam[$n])) {
			// Check if we are spamming...
			if (time() - $this->spam[$n][0] < self::SPAM_DELAY
				 && $this->spam[$n][1] == $txt) return;
		}
		$this->spam[$n] = [ time(), $txt ];
		$pl->sendMessage($txt);
	}
}
//		echo __METHOD__.",".__LINE__."\n";//##DEBUG
