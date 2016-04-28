<?php
//= cmd:/worldprotect,Main_Commands
//: Main WorldProtect command
//> usage: /worldprotect  _[world]_ _<subcmd>_ _[options]_
//= cfg:features
//: This section you can enable/disable modules.
//: You do this in order to avoid conflicts between different
//: PocketMine-MP plugins.  It has one line per feature:
//:
//:     feature: true|false
//:
//: If **true** the feature is enabled.  if **false** the feature is disabled.
//:
namespace aliuly\worldprotect;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\level\Level;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\level\LevelUnloadEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use aliuly\worldprotect\common\mc;
use aliuly\worldprotect\common\MPMU;
use aliuly\worldprotect\common\BasicPlugin;

class Main extends BasicPlugin implements CommandExecutor,Listener {
	protected $wcfg;
	const SPAM_DELAY = 5;

	public function onEnable() {
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		mc::plugin_init($this,$this->getFile());
		$cfg = $this->modConfig(__NAMESPACE__, [
			"max-players" => [ "MaxPlayerMgr", false ],
			"protect" => [ "WpProtectMgr", true ],
			"border" => [ "WpBordersMgr", true ],
			"pvp" => [ "WpPvpMgr", true ],
			"motd" => [ "WpMotdMgr", false ],
			"no-explode" => [ "NoExplodeMgr", false ],
			"unbreakable" => [ "Unbreakable", false ],
			"bancmds" => [ "BanCmd" , false ],
			"banitem" => [ "BanItem", true ],
			"gamemode" => [ "GmMgr", false ],
			"gm-save-inv" => [ "GmSaveInv", false ],
		], [
			"version" => $this->getDescription()->getVersion(),
			"motd" => WpMotdMgr::defaults(),
		],mc::_("/%s [world] %s %s"));
		$this->modules[] = new WpList($this);
		echo __METHOD__.",".__LINE__."\n";//##DEBUG

		// Make sure that loaded worlds are inded loaded...
		foreach ($this->getServer()->getLevels() as $lv) {
			$this->loadCfg($lv);
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

	}

	//////////////////////////////////////////////////////////////////////
	//
	// Save/Load configurations
	//
	//////////////////////////////////////////////////////////////////////
	public function loadCfg($world) {
		echo __METHOD__.",".__LINE__."\n";//##DEBUG

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
				if (!($mod instanceof BaseWp)) continue;
				if (isset($this->wcfg[$world][$i])) {
					$mod->setCfg($world,$this->wcfg[$world][$i]);
				} else {
					$mod->unsetCfg($world);
				}
			}
		} else {
			$this->wcfg[$world] = [];
			foreach ($this->modules as $i=>$mod) {
				if (!($mod instanceof BaseWp)) continue;
				$mod->unsetCfg($world);
			}
		}
		return true;
	}
	public function saveCfg($world) {
		echo __METHOD__.",".__LINE__."\n";//##DEBUG

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
		echo __METHOD__.",".__LINE__."\n";//##DEBUG

		if ($world instanceof Level) $world = $world->getName();
		if (isset($this->wcfg[$world])) unset($this->wcfg[$world]);
		foreach ($this->modules as $i=>$mod) {
			if (!($mod instanceof BaseWp)) continue;
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
		if (isset($this->modules[$key])
			 && ($this->modules[$key] instanceof BaseWp))
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
		if (isset($this->modules[$key])
			 && ($this->modules[$key] instanceof BaseWp))
			$this->modules[$key]->unsetCfg($world);
		if ($unload) $this->unloadCfg($world);
	}

	//////////////////////////////////////////////////////////////////////
	//
	// Event handlers
	//
	//////////////////////////////////////////////////////////////////////
	public function onLevelLoad(LevelLoadEvent $e) {
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$this->loadCfg($e->getLevel());
	}
	public function onLevelUnload(LevelUnloadEvent $e) {
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
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
			$sender->sendMessage(mc::_("[WP] Must specify a world"));
			return false;
		}
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if (!$this->isAuth($sender,$world)) return true;
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		return $this->dispatchSCmd($sender,$cmd,$args,$world);
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
		echo __METHOD__.",".__LINE__."\n";//##DEBUG

		$iusr = strtolower($c->getName());
		if (isset($this->wcfg[$world][$iusr])) return true;
		$c->sendMessage(mc::_("[WP] You are not allowed to do this"));
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
		if (MPMU::apiVersion("2.0.0")) {
			$pl->sendTip($txt);
			return;
		}
		list($time,$otxt)= $this->getState("spam",$pl,[0,""]);
		if (time() - $time < self::SPAM_DELAY && $otxt == $txt) return;
		$this->setState("spam",$pl,[time(),$txt]);
		$pl->sendMessage($txt);
	}
	/**
	 * @API
	 */
	public function getMaxPlayers($world){
		if (isset($this->modules["max-players"]))
			return $this->modules["max-players"]->getMaxPlayers($world);
		return null;
	}
}
