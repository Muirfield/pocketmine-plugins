<?php
namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandExecutor;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\scheduler\CallbackTask;
use pocketmine\item\Item;

class Main extends PluginBase implements CommandExecutor {
	const MIN_BORDER = 32;
	private $listeners;
	protected $wcfg;
	protected $settings;
	protected $spam;
	const SPAM_DELAY = 5;
	static private $aliases = [
		"ubab" => "unbreakable",
		"bab" => "breakable",
		"unprotect" => "unlock",
		"open" => "unlock",
		"notnt" => "noexplode",
	];

	// Access and other permission related checks
	private function access(CommandSender $sender, $permission) {
		if($sender->hasPermission($permission)) return true;
		$sender->sendMessage("You do not have permission to do that.");
		return false;
	}
	private function inGame(CommandSender $sender,$msg = true) {
		if ($sender instanceof Player) return true;
		if ($msg) $sender->sendMessage("You can only use this command in-game");
		return false;
	}
	// Standard call-backs
	public function onEnable(){
		$defaults = [
			"settings" => [
				"player-limits" => true,
				"world-borders" => true,
				"world-protect" => true,
				"per-world-pvp" => true,
				"motd" => true,
				"no-explode" => true,
				"unbreakable" => true,
			],
		];
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		$cfg = (new Config($this->getDataFolder()."config.yml",
								 Config::YAML,$defaults))->getAll();
		if ($cfg["settings"]["player-limits"] &&
			 $this->getServer()->getPluginManager()->getPlugin("ManyWorlds")==null){
			$this->getLogger()->info(TextFormat::RED.
											 "player-limits functionality ".
											 TextFormat::AQUA."WITHOUT");
			$this->getLogger()->info(TextFormat::RED."the ".
											 TextFormat::WHITE."ManyWorlds".
											 TextFormat::RED." plugin is experimental");
			//$cfg["settings"]["player-limits"] = false;
		}
		$this->listeners = [];
		$this->listeners["main"] = new WpListener($this);
		if ($cfg["settings"]["motd"])
			$this->listeners["motd"] = new WpMotdMgr($this);
		if ($cfg["settings"]["world-protect"] || $cfg["settings"]["unbreakable"])
			$this->listeners["protect"] = new WpProtectMgr($this);
		if ($cfg["settings"]["world-borders"])
			$this->listeners["borders"] = new WpBordersMgr($this);
		if ($cfg["settings"]["per-world-pvp"])
			$this->listeners["pvp"] = new WpPvpMgr($this);
		if ($cfg["settings"]["no-explode"])
			$this->listeners["no-explode"] = new NoExplodeMgr($this);
		if ($cfg["settings"]["player-limits"])
			$this->listeners["player-limits"] = new MaxPlayerMgr($this);

		$this->settings = $cfg["settings"];

		$this->wcfg = [];
		$this->spam = [];
	}
	protected function wpPvpMode($level) {
		if (isset($this->wcfg[$level]["pvp"])) {
			$x = $this->wcfg[$level]["pvp"];
			echo __METHOD__.",".__LINE__."-$x\n";
			if ($x === "spawn") {
				return TextFormat::YELLOW."spawn";
			} elseif ($x) {
				return TextFormat::RED."ON";
			} else {
				return TextFormat::GREEN."OFF";
			}
		}
		return TextFormat::RED."ON";
	}
	////////////////////////////////////////////////////////////////////////
	//
	// Event handlers...
	//
	////////////////////////////////////////////////////////////////////////
	//
	// Load/Unload Worlds
	//
	public function doLoadWorldConfig($sender,$level) {
		if (!$this->loadWorldConfig($level)) {
			$sender->sendMessage("Unable to load configuration for world $level");
			return false;
		}
		return true;
	}
	public function saveWorldConfig($world) {
		$f = $this->getServer()->getDataPath(). "worlds/".$world."/wpcfg.yml";
		$yaml = new Config($f,Config::YAML,[]);
		$yaml->setAll($this->wcfg[$world]);
		$yaml->save();
	}
	public function loadWorldConfig($level) {
		if (isset($this->wcfg[$level])) return true; // world is already loaded!
		// Is this the best way to get the world path?
		$p = $this->getServer()->getDataPath(). "worlds/".$level;
		if (!is_dir($p)) return false;
		$f = $p."/wpcfg.yml";

		$this->wcfg[$level] = (new Config($f,Config::YAML,[]))->getAll();
		return true;
	}
	public function unloadWorldConfig($level) {
		if (!isset($this->wcfg[$level])) return; // This world is not loaded...
		unset($this->wcfg[$level]);
	}
	//
	// Show MOTD messages
	//
	public function showMotd($name,$level) {
		if (!isset($this->wcfg[$level]["motd"])) return;
		$player = $this->getServer()->getPlayer($name);
		if (!$player) return;
		if (!$player->hasPermission("wp.motd")) return;
		if (is_array($this->wcfg[$level]["motd"])) {
			$ticks = 10;
			foreach ($this->wcfg[$level]["motd"] as $ln) {
				$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$player,"sendMessage"],[$ln]),$ticks);
				$ticks += 10;
			}
		} else {
			$player->sendMessage($this->wcfg[$level]["motd"]);
		}
	}
	//
	// Unbreakable
	//
	public function checkUnbreakable($pname,$level,$blkid) {
		if (!isset($this->wcfg[$level]["ubab"])) return false;
		if (!count($this->wcfg[$level]["ubab"])) return false;
		// Check if user is in auth list...
		if (isset($this->wcfg[$level]["auth"])
			 && count($this->wcfg[$level]["auth"])
			 && isset($this->wcfg[$level]["auth"][$pname])) return false;
		$player = $this->getServer()->getPlayer($pname);
		if ($player && $player->hasPermission("wp.cmd.protect.auth"))
			return false;
		if (isset($this->wcfg[$level]["ubab"][$blkid])) return true;
		return false;
	}

	//
	// World protect
	//
	public function checkBlockPlaceBreak($pname,$level) {
		if (!isset($this->wcfg[$level]["protect"])) return true;
		if ($this->wcfg[$level]["protect"] != "protect") return false;
		if (isset($this->wcfg[$level]["auth"])
			 && count($this->wcfg[$level]["auth"])) {
			// Check if user is in auth list...
			if (isset($this->wcfg[$level]["auth"][$pname])) return true;
			return false;
		}
		$player = $this->getServer()->getPlayer($pname);
		if (!$player) return false;
		if ($player->hasPermission("wp.cmd.protect.auth")) return true;
		return false;
	}
	//
	// No explode callback
	//
	public function checkNoExplode($x,$y,$z,$level) {
		if (!isset($this->wcfg[$level]["no-explode"])) return true;
		if ($this->wcfg[$level]["no-explode"] == "world") return false;
		if ($this->wcfg[$level]["no-explode"] != "spawn") return true;
		$lv = $this->getServer()->getLevelByName($level);
		if (!$lv) return true;
		$sp = $lv->getSpawnLocation();
		$dist = $sp->distance(new Vector3($x,$y,$z));
		if ($dist < $this->getServer()->getSpawnRadius()) return false;
		return true;
	}
	//
	// PvP callback
	//
	public function checkPvP($level,$x,$y,$z) {
		if (!isset($this->wcfg[$level]["pvp"])) return true;
		if ($this->wcfg[$level]["pvp"] === "spawn") {
			// Check spawn position
			$lv = $this->getServer()->getLevelByName($level);
			if (!$lv) return true;
			$sp = $lv->getSpawnLocation();
			$dist = $sp->distance(new Vector3($x,$y,$z));
			if ($dist < $this->getServer()->getSpawnRadius()) return false;
			return true;
		}
		return $this->wcfg[$level]["pvp"];
	}
	//
	// Border controls
	//
	public function checkMove($level,$x,$z) {
		if (!isset($this->wcfg[$level]["border"])) return true;
		list($x1,$z1,$x2,$z2) = $this->wcfg[$level]["border"];
		if ($x1 < $x && $x < $x2 && $z1 < $z && $z < $z2) return true;
		return false;
	}
	////////////////////////////////////////////////////////////////////////
	//
	// Utility functions
	//
	////////////////////////////////////////////////////////////////////////
	public function msg($pl,$txt) {
		$n = $pl->getName();
		if (isset($this->spam[$n])) {
			// Check if we are spamming...
			if (time() - $this->spam[$n][0] < self::SPAM_DELAY
				 && $this->spam[$n][1] == $txt) return;
		}
		$this->spam[$n] = [ time(), $txt ];
		$pl->sendMessage($txt);
	}
	private function isAuth(CommandSender $sender,$level) {
		if (!$this->inGame($sender,false)) return true;
		if (!isset($this->wcfg[$level]["auth"])) return true;
		if (!count($this->wcfg[$level]["auth"])) return true;
		if (isset($this->wcfg[$level]["auth"][$sender->getName()])) return true;
		$sender->sendMessage("[WP] You are not allowed to do this");
		return false;
	}

	////////////////////////////////////////////////////////////////////////
	//
	// Command entry point
	//
	////////////////////////////////////////////////////////////////////////
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		switch($cmd->getName()) {
			case "motd":
				return $this->motdCmd($sender,$args);
			case "worldprotect":
				if(isset($args[0])) {
					$scmd = strtolower(array_shift($args));
					if (isset(self::$aliases[$scmd])) $scmd = self::$aliases[$scmd];
					switch ($scmd) {
						case "add":
							if (!$this->access($sender,"wp.cmd.addrm")) return false;
							return $this->worldProtectAdd($sender,$args);
						case "rm":
							if (!$this->access($sender,"wp.cmd.addrm")) return false;
							return $this->worldProtectRm($sender,$args);
						case "unbreakable":
						case "breakable":
							if (!$this->access($sender,"wp.cmd.unbreakable")) return false;
							if (!$this->settings["unbreakable"]) return false;
							return $this->worldUBAB($sender,$scmd,$args);
						case "unlock":
						case "lock":
						case "protect":
							if (!$this->access($sender,"wp.cmd.protect")) return false;
							if (!$this->settings["world-protect"]) return false;
							return $this->worldProtectMode($sender,$scmd,$args);
						case "noexplode":
							if (!$this->access($sender,"wp.cmd.noexplode")) return false;
							if (!$this->settings["no-explode"]) return false;
							return $this->worldNoExplodeMode($sender,$args);
						case "pvp":
							if (!$this->access($sender,"wp.cmd.pvp")) return false;
							if (!$this->settings["per-world-pvp"]) return false;
							return $this->worldPvpMode($sender,$args);
						case "border":
							if (!$this->access($sender,"wp.cmd.border")) return false;
							if (!$this->settings["world-borders"]) return false;
							return $this->worldBorderCmd($sender,$args);
						case "max":
							if (!$this->access($sender,"wp.cmd.limit")) return false;
							if (!$this->settings["player-limits"]) return false;
							return $this->worldLimitCmd($sender,$args);
						case "motd":
							if (!$this->access($sender,"wp.cmd.wpmotd")) return false;
							if (!$this->settings["motd"]) return false;
							return $this->worldMotdCmd($sender,$args);
						case "help":
							return $this->helpCmd($sender,$args);
						case "ls":
						case "ld":
							// These commands actually come from ManyWorlds...
							$pm = $this->getServer()->getPluginManager()->getPlugin("ManyWorlds");
							if ($pm) {
								array_unshift($args,$scmd);
								return $pm->onCommand($sender,$cmd,$label,$args);
							}
						default:
							$sender->sendMessage(TextFormat::RED."Unknown sub command: ".
														TextFormat::RESET.$scmd);
							$sender->sendMessage("Use: ".TextFormat::GREEN." /wp help".
														TextFormat::RESET);
					}
					return true;
				} else {
					$sender->sendMessage("Must specify sub command");
					$sender->sendMessage("Try: ".TextFormat::GREEN." /wp help".
												TextFormat::RESET);
					return false;
				}
		}
		return false;
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Actual commands
	//
	//////////////////////////////////////////////////////////////////////
	private function helpCmd(CommandSender $sender,$args) {
		$cmds = [
			"motd" => ["[level] [text]",
						  "View/Edit the world's motd file"],
			"add" => ["[level] <user>","Add <user> to authorized list"],
			"rm" => ["[level] <user>","Remove <user> from authorized list"],
			"unlock" => ["[level]","Unprotects a world"],
			"lock"=>["[level]","Locked.  Nobody (including op) can build"],
			"protect"=>["[level]","Only authorized people can build"],
			"pvp"=>["[level] [on|off|spawn]","Enable|disable pvp"],
			"noexplode" =>["[level] [off|world|spawn]",
								"Stop explosions in world or spawn area"],
			"border" => ["[level] [x1 z1 x2 z2|none]",
							 "Creates a border in [level] defined by x1,z1 to x2,z2"],
			"max" => ["[level] [value]",
						 "Limits the number of players in a world to [value] use 0 or -1 to remove limits"],
			"unbreakable" => [ "[level] <id> <id>",
									 "Sets blocks to unbreakable status" ],
			"breakable" =>  [ "[level] <id> <id>",
									 "Remove blocks from unbreakable status" ],

		];
		if (count($args)) {
			foreach ($args as $c) {
				if (isset(self::$aliases[$c])) $c = self::$aliases[$c];
				if (isset($cmds[$c])) {
					list($a,$b) = $cmds[$c];
					$sender->sendMessage(TextFormat::RED."Usage: /wp $c $a"
												.TextFormat::RESET);
					$sender->sendMessage($b);
				}
			}
			return true;
		}
		$sender->sendMessage("WorldProtect sub-commands");
		foreach ($cmds as $a => $b) {
			$ln = "- ".TextFormat::GREEN."/wp ".$a;
			foreach (self::$aliases as $i => $j) {
				if ($j == $a) $ln .= "|$i";
			}
			$ln .= TextFormat::RESET." ".$b[0];
			$sender->sendMessage($ln);
		}
		return true;
	}

	private function motdCmd(CommandSender $sender,$args) {
		if (count($args) == 0) {
			if (!$this->inGame($sender)) return true;
			$level = $sender->getLevel()->getName();
		} elseif (count($args) > 1) {
			return $this->helpCmd($sender,["motd"]);
		} else {
			$level = $args[0];
		}
		if (!$this->doLoadWorldConfig($sender,$level)) return true;
		if (!isset($this->wcfg[$level]["motd"])) {
			$sender->sendMessage(TextFormat::RED."Sorry, no \"motd\"".TextFormat::RESET);
			return true;
		}
		if (is_array($this->wcfg[$level]["motd"])) {
			foreach ($this->wcfg[$level]["motd"] as $ln) {
				$sender->sendMessage($ln);
			}
		} else {
			$sender->sendMessage($this->wcfg[$level]["motd"]);
		}
		return true;
	}
	//
	// Modify auth list
	//
	private function worldProtectAdd(CommandSender $sender,$args){
		if (count($args) == 1) {
			if (!$this->inGame($sender,false)) {
				$sender->sendMessage("You need to specify a level");
				return false;
			}
			$user = $args[0];
			$level = $sender->getLevel()->getName();
		} elseif (count($args) == 2) {
			list($level,$user) = $args;
		}
		if (!$this->doLoadWorldConfig($sender,$level)) return true;
		if (!$this->isAuth($sender,$level)) return true;
		if (!isset($this->wcfg[$level]["auth"])) $this->wcfg[$level]["auth"] = [];
		if (isset($this->wcfg[$level]["auth"][$user])) {
			$sender->sendMessage("[WP] $user is already in $level authorized list");
			return true;
		}
		$player = $this->getServer()->getPlayer($user);
		if (!$player) {
			$sender->sendMessage("[WP] $user can not be found.  Are they off-line?");
			return true;
		}
		if (!$player->isOnline()) {
			$sender->sendMessage("[WP] $user is offline!");
			return true;
		}
		$this->wcfg[$level]["auth"][$user] = $user;
		$this->saveWorldConfig($level);
		$sender->sendMessage("[WP] $user added to $level's authorized list");
		$player->sendMessage("[WP] You have been added to $level's authorized list");
		return true;
	}
	private function worldProtectRm(CommandSender $sender,$args) {
		if (count($args) == 1) {
			if (!$this->inGame($sender,false)) {
				$sender->sendMessage("You need to specify a level");
				return false;
			}
			$user = $args[0];
			$level = $sender->getLevel()->getName();
		} elseif (count($args) == 2) {
			list($level,$user) = $args;
		}
		if (!$this->doLoadWorldConfig($sender,$level)) return true;
		if (!$this->isAuth($sender,$level)) return true;

		//print_r($this->wcfg[$level]); //##DEBUG
		if (!isset($this->wcfg[$level]["auth"][$user])) {
			$sender->sendMessage("[WP] $user is not in $level authorized list");
			return true;
		}
		$player = $this->getServer()->getPlayer($user);
		if ($player) {
			if (!$player->isOnline()) $player = null;
		}
		unset($this->wcfg[$level]["auth"][$user]);
		$this->saveWorldConfig($level);
		$sender->sendMessage("[WP] $user removed from $level's authorized list");
		if ($player)
			$player->sendMessage("[WP] You have been removed from $level's authorized list");
		return true;
	}
	//
	// Unbreakable blocks
	//
	private function worldUBAB(CommandSender $sender,$mode,$args) {
		if (count($args) == 0) return false;
		if ($this->getServer()->isLevelGenerated($args[0])) {
			$level = array_shift($args);
		} else {
			if (!$this->inGame($sender)) return true;
			$level = $sender->getLevel()->getName();
		}
		if (!$this->doLoadWorldConfig($sender,$level)) return true;
		if (!$this->isAuth($sender,$level)) return true;
		if(!count($args)) {
			if(isset($this->wcfg[$level]["ubab"])) {
				$lst = "";
				foreach ($this->wcfg[$level]["ubab"] as $b) {
					$lst .= (strlen($lst)>0 ? ", " : "").$b;
				}
				$sender->sendMessage("Unbreakables(".
											count($this->wcfg[$level]["ubab"])."): ".
											$lst);
			} else {
				$sender->sendMessage("No unbreakable blocks in $level");
			}
			return true;
		}
		if ($mode == "breakable") {
			foreach ($args as $i) {
				$item = Item::fromString($i);
				unset($this->wcfg[$level]["ubab"][$item->getId()]);
			}
		} else {
			foreach ($args as $i) {
				$item = Item::fromString($i);
				if ($item->getId() == Item::AIR) continue;
				$this->wcfg[$level]["ubab"][$item->getId()] = $i."(".$item->getId().")";
			}
		}
		$this->saveWorldConfig($level);
		return true;
	}
	//
	// World protect
	//
	private function worldProtectMode(CommandSender $sender,$mode,$args) {
		if (count($args) == 0) {
			if (!$this->inGame($sender)) return true;
			$level = $sender->getLevel()->getName();
		} elseif (count($args) == 1) {
			$level = $args[0];
		} else {
			return $this->helpCmd($sender,[$mode]);
		}
		if (!$this->doLoadWorldConfig($sender,$level)) return true;
		if (!$this->isAuth($sender,$level)) return true;
		if (isset($this->wcfg[$level]["protect"])) {
			if ($mode == "unlock") {
				unset($this->wcfg[$level]["protect"]);
			} elseif ($mode == $this->wcfg[$level]["protect"]) {
				$sender->sendMessage("[WP] $level was not changed!");
				return true;
			} else {
				$this->wcfg[$level]["protect"] = $mode;
			}
		} else {
			if ($mode == "unlock") {
				$sender->sendMessage("[WP] $level was not changed!");
				return true;
			}
			$this->wcfg[$level]["protect"] = $mode;
		}
		$this->saveWorldConfig($level);
		$this->getServer()->broadcastMessage("[WP] World $level protection mode changed to $mode");
		return true;
	}
	// No Explode controls
	private function worldNoExplodeMode(CommandSender $sender,$args) {
		if (count($args) == 0) {
			if (!$this->inGame($sender)) return true;
			$level = $sender->getLevel()->getName();
			$mode = "show";
		} elseif (count($args) == 1) {
			if (in_array(strtolower($args[0]),["off","world","spawn"])) {
				if (!$this->inGame($sender)) return true;
				$mode = strtolower($args[0]);
				$level = $sender->getLevel()->getName();
			} else {
				$mode = "show";
				$level = $args[0];
			}
		} elseif (count($args) == 2) {
			list($level,$mode) = $args;
			$mode = strtolower($mode);
		} else {
			return $this->helpCmd($sender,["noexplode"]);
		}
		if (!$this->doLoadWorldConfig($sender,$level)) return true;
		if ($mode == "show") {
			$m = "NoExplode status for $level is ";
			if (!isset($this->wcfg[$level]["no-explode"])) {
				$m .= TextFormat::RED."OFF".TextFormat::RESET." (explosions allowed)";
			} elseif ($this->wcfg[$level]["no-explode"] == "world") {
				$m .= TextFormat::GREEN."world";
			} elseif ($this->wcfg[$level]["no-explode"] == "spawn") {
				$m .= TextFormat::YELLOW."spawn";
			} else {
				$m .= TextFormat::RED."Unknown".TextFormat::RESET.
					" (explosions allowed)";
			}
			$sender->sendMessage($m);
			return true;
		}
		if (!$this->isAuth($sender,$level)) return true;
		if ($mode == "off") {
			if (!isset($this->wcfg[$level]["no-explode"])) {
				$sender->sendMessage("no-explode status unchanged");
				return true;
			}
			unset($this->wcfg[$level]["no-explode"]);
		} elseif ($mode == "world" || $mode == "spawn") {
			$this->wcfg[$level]["no-explode"] = $mode;
		} else {
			$sender->sendMessage("Invalid no-explode mode $mode");
			return false;
		}
		$this->saveWorldConfig($level);
		$this->getServer()->broadcastMessage("[WP] World $level no-explode changed to $mode");
		return true;
	}
	// pvp mode
	private function worldPvpMode(CommandSender $sender,$args) {
		if (count($args) == 0) {
			if (!$this->inGame($sender)) return true;
			$level = $sender->getLevel()->getName();
			$mode = "show";
		} elseif (count($args) == 1) {
			if (strtolower($args[0]) == "on" ||
				 strtolower($args[0]) == "off" ||
				 strtolower($args[0]) == "spawn") {
				if (!$this->inGame($sender)) return true;
				$mode = strtolower($args[0]);
				$level = $sender->getLevel()->getName();
			} else {
				$mode = "show";
				$level = $args[0];
			}
		} elseif (count($args) == 2) {
			list($level,$mode) = $args;
			$mode = strtolower($mode);
		} else {
			return $this->helpCmd($sender,["pvp"]);
		}
		if (!$this->doLoadWorldConfig($sender,$level)) return true;
		if ($mode == "show") {
			$sender->sendMessage("PvP status for $level is ".
										$this->wpPvpMode($level));
			return true;
		}
		if (!$this->isAuth($sender,$level)) return true;
		if ($mode == "on") {
			if (!isset($this->wcfg[$level]["pvp"]) ||
				 $this->wcfg[$level]["pvp"] === true) {
				$sender->sendMessage("PvP status unchanged");
				return true;
			}
			$this->wcfg[$level]["pvp"] = true;
		} elseif ($mode == "spawn") {
			if (isset($this->wcfg[$level]["pvp"]) &&
				 $this->wcfg[$level]["pvp"] === "spawn") {
				$sender->sendMessage("PvP status unchanged");
				return true;
			}
			$this->wcfg[$level]["pvp"] = "spawn";
		} else {
			if (isset($this->wcfg[$level]["pvp"]) &&
				 $this->wcfg[$level]["pvp"] == false) {
				$sender->sendMessage("PvP status unchanged");
				return true;
			}
			$this->wcfg[$level]["pvp"] = false;
		}
		$this->saveWorldConfig($level);
		$this->getServer()->broadcastMessage("[WP] World $level PvP changed to $mode");
		return true;
	}

	// World borders
	private function worldBorderCmd(CommandSender $sender,$args) {
		if (count($args) == 4) {
			if (!$this->inGame($sender)) return true;
			$level = $sender->getLevel()->getName();
		} elseif (count($args) == 5) {
			$level = array_shift($args);
		} elseif (count($args) == 2) {
			$level = array_shift($args);
		} elseif (count($args) == 1) {
			if (strtolower($args[0]) == "off") {
				if (!$this->inGame($sender)) return true;
				$level = $sender->getLevel()->getName();
			} else {
				$level = array_shift($args);
			}
		} else {
			return $this->helpCmd($sender,["border"]);
		}
		if (!$this->doLoadWorldConfig($sender,$level)) return true;
		if (count($args) == 0) {
			list($x1,$z1,$x2,$z2) = $this->wcfg[$level]["border"];
			$sender->sendMessage("[WP] Border for $level is ($x1,$z1)-($x2,$z2)");
			return true;
		}
		if (!$this->isAuth($sender,$level)) return true;
		if (count($args) == 4) {
			list($x1,$z1,$x2,$z2) = $args;
			if ($x1 > $x2) list($x1,$x2) = [$x2,$x1];
			if ($z1 > $z2) list($z1,$z2) = [$z2,$z1];
			if ($x2 - $x1 < self::MIN_BORDER || $z2 - $z1 < self::MIN_BORDER) {
				$sender->sendMessage("[WP] Invalid border region");
				return true;
			}
			$this->wcfg[$level]["border"] = [$x1,$z1,$x2,$z2];
		} else {
			if (!isset($this->wcfg[$level]["border"])) {
				$sender->sendMessage("[WP] No border for $level currently");
				return true;
			}
			unset($this->wcfg[$level]["border"]);
		}
		$this->saveWorldConfig($level);
		if (isset($this->wcfg[$level]["border"])) {
			$this->getServer()->broadcastMessage("[WP] $level border: $x1,$z1,$x2,$z2");
		} else {
			$this->getServer()->broadcastMessage("[WP] Borders for $level removed");
		}
		return true;
	}

	// World limits
	private function worldLimitCmd(CommandSender $sender,$args) {
		if (count($args) == 0) {
			if (!$this->inGame($sender)) return true;
			$level = $sender->getLevel()->getName();
			$count = "show";
		} elseif (count($args) == 1) {
			if (is_numeric($args[0])) {
				if (!$this->inGame($sender)) return true;
				$level = $sender->getLevel()->getName();
				$count = (int)$args[0];
			} else{
				$level = $args[0];
				$count = "show";
			}
		} elseif (count($args) == 2) {
			$level = $args[0];
			$count = (int)$args[1];
		} else {
			return $this->helpCmd($sender,["max"]);
		}
		if (!$this->doLoadWorldConfig($sender,$level)) return true;
		if ($count == "show") {
			if (isset($this->wcfg[$level]["max-players"])) {
				$sender->sendMessage("[WP] ".
											$this->wcfg[$level]["max-players"].
											" allowed in world $level");
			} else {
				$sender->sendMessage("[WP] Max players in $level is un-limited");
			}
			return true;
		}
		if (!$this->isAuth($sender,$level)) return true;
		$count = (int)$count;
		if ($count <= 0) $count = 0;
		if (isset($this->wcfg[$level]["max-players"])) {
			if ($count == $this->wcfg[$level]["max-players"]) {
				$sender->sendMessage("[WP] Max players in $level is unchanged");
				return true;
			}
		} else {
			if ($count <= 0) {
				$sender->sendMessage("[WP] Max players in $level is unchanged");
				return true;
			}
		}
		$this->wcfg[$level]["max-players"] = $count;
		$this->saveWorldConfig($level);
		if ($count) {
			$this->getServer()->broadcastMessage("[WP] Max-players limit for $level set to $count");
		} else {
			$this->getServer()->broadcastMessage("[WP] Max-players limits for $level removed");
		}
		return true;
	}
	//
	// MOTD stuff...
	//
	private function worldMotdCmd(CommandSender $sender,$args) {
		if (count($args)==0) {
			if (!$this->inGame($sender)) return true;
			$level = $sender->getLevel()->getName();
		} else {
			if ($this->getServer()->isLevelGenerated($args[0])) {
				$level = array_shift($args);
			} else {
				if (!$this->inGame($sender)) return true;
				$level = $sender->getLevel()->getName();
			}
		}
		if (count($args) == 0) return $this->motdCmd($sender,[$level]);
		if (!$this->doLoadWorldConfig($sender,$level)) return true;
		if (!$this->isAuth($sender,$level)) return true;
		$ln = implode(" ",$args);
		if (isset($this->wcfg[$level]["motd"])) {
			if (!is_array($this->wcfg[$level]["motd"]) &&
				 $this->wcfg[$level]["motd"] == $ln) {
				$sender->sendMessage("[WP] $level's motd unchanged");
				return true;
			}
		}
		$this->wcfg[$level]["motd"] = $ln;
		$this->saveWorldConfig($level);
		$sender->sendMessage("[WP] $level's motd updated");
		return true;
	}
	//////////////////////////////////////////////////////////////////////
	//
	// ManyWorlds entry points
	//
	//////////////////////////////////////////////////////////////////////
	public function getPlayerLimit($level) {
		if (!isset($this->wcfg[$level])) return 0;
		if (!isset($this->wcfg[$level]["max-players"])) return 0;
		return $this->wcfg[$level]["max-players"];
	}
	public function getWorldInfo($level) {
		//echo "WORLDINFO: $level\n";//##DEBUG
		$txt = [];
		if (!isset($this->wcfg[$level])) return $txt;
		if (isset($this->wcfg[$level]["motd"])) {
			$txt[] = "MOTD:";
			if (is_array($this->wcfg[$level]["motd"])) {
				foreach ($this->wcfg[$level]["motd"] as $ln) {
					$txt[] = TextFormat::BLUE."  ".$ln.TextFormat::RESET;
				}
			} else {
				$txt[] = TextFormat::BLUE."  ".$this->wcfg[$level]["motd"];
			}
		}
		if (isset($this->wcfg[$level]["protect"]))
			$txt[] = TextFormat::AQUA."Protect Status:  ".
					 TextFormat::WHITE.$this->wcfg[$level]["protect"];
		$txt[] = TextFormat::AQUA."PvP: ".$this->wpPvpMode($level);
		if (isset($this->wcfg[$level]["no-explode"])) {
			if ($this->wcfg[$level]["no-explode"] == "world") {
				$txt[] = TextFormat::AQUA."NoExplode: ".TextFormat::GREEN."world";
			} elseif ($this->wcfg[$level]["no-explode"] == "spawn") {
				$txt[] = TextFormat::AQUA."NoExplode: ".TextFormat::YELLOW."spawn";
			} else {
				$txt[]= TextFormat::AQUA."NoExplode: ".TextFormat::RED.$this->wcfg[$level]["no-explode"];
			}
		}
		if (isset($this->wcfg[$level]["border"])) {
			$txt[] = TextFormat::AQUA."World Borders:".
					 TextFormat::WHITE.implode(",",$this->wcfg[$level]["border"]);
		}
		if (isset($this->wcfg[$level]["auth"]) && count($this->wcfg[$level]["auth"])) {
			$txt[] = TextFormat::AQUA.
					 "Auth-List(".count($this->wcfg[$level]["auth"])."):".
					 TextFormat::WHITE.implode(", ",$this->wcfg[$level]["auth"]);
		}
		return $txt;
	}
}
