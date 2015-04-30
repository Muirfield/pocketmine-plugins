<?php
namespace aliuly\manyworlds;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandExecutor;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\level\generator\Generator;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\nbt\tag\Long;
use pocketmine\nbt\tag\Compound;

use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\level\Position;


class Main extends PluginBase implements CommandExecutor {
	const MIN_BORDER = 128;

	protected $canUnload = false;
	private $tpManager;
	private $cfg;
	protected $maxplayers;
	public $is15;

	static private $aliases = [
		"list" => "ls",
		"load" => "ld",
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

	// Paginate output
	private function getPageNumber(array &$args) {
		$pageNumber = 1;
		if (count($args) && is_numeric($args[count($args)-1])) {
			$pageNumber = (int)array_pop($args);
			if($pageNumber <= 0) $pageNumber = 1;
		}
		return $pageNumber;
	}
	private function paginateText(CommandSender $sender,$pageNumber,array $txt) {
		$hdr = array_shift($txt);
		if($sender instanceof ConsoleCommandSender){
			$sender->sendMessage( TextFormat::GREEN.$hdr.TextFormat::RESET);
			foreach ($txt as $ln) $sender->sendMessage($ln);
			return true;
		}
		$pageHeight = 5;
		$hdr = TextFormat::GREEN.$hdr. TextFormat::RESET;
		if (($pageNumber-1) * $pageHeight >= count($txt)) {
			$sender->sendMessage($hdr);
			$sender->sendMessage("Only ".intval(count($txt)/$pageHeight+1)." pages available");
			return true;
		}
		$hdr .= TextFormat::RED." ($pageNumber of ".intval(count($txt)/$pageHeight+1).")".TextFormat::RESET;
		$sender->sendMessage($hdr);
		for ($ln = ($pageNumber-1)*$pageHeight;$ln < count($txt) && $pageHeight--;++$ln) {
			$sender->sendMessage($txt[$ln]);
		}
		return true;
	}
	private function paginateTable(CommandSender $sender,$pageNumber,array $tab) {
		$cols = [];
		for($i=0;$i < count($tab[0]);$i++) $cols[$i] = strlen($tab[0][$i]);
		foreach ($tab as $row) {
			for($i=0;$i < count($row);$i++) {
				if (($l=strlen($row[$i])) > $cols[$i]) $cols[$i] = $l;
			}
		}
		$txt = [];
		foreach ($tab as $row) {
			$txt[] = sprintf("%-$cols[0]s %-$cols[1]s %-$cols[2]s %-$cols[3]s",
								  $row[0],$row[1],$row[2],$row[3]);
		}
		return $this->paginateText($sender,$pageNumber,$txt);
	}
	// Standard call-backs
	public function onEnable(){
		// Depending on the API, we allow unload by default...
		$api = explode(".",$this->getServer()->getApiVersion());
		if (intval($api[1]) < 12) {
			$this->canUnload = false;
			$this->is15 = false;
		} else {
			$this->getLogger()->info("Runniong on PocketMine-MP v1.5 or better");
			$this->getLogger()->info(TextFormat::RED.
											 "This version is still under development");
			$this->getLogger()->info(TextFormat::RED.
											 "and it may not be fully stable");
			$this->canUnload = true;
			$this->is15 = true;
		}

		$this->tpManager = new TeleportManager($this);
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		$defaults = [
			"settings" => [
				"broadcast-tp" => true,
			],
		];
		$this->cfg = (new Config($this->getDataFolder()."config.yml",
										 Config::YAML,$defaults))->getAll();
		$this->maxplayers = [$this,"maxPlayers1st"];
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		switch($cmd->getName()) {
			case "worldprotect": // Allow WP to call us...
			case "mw":
				if(isset($args[0])) {
					$scmd = strtolower(array_shift($args));
					if (isset(self::$aliases[$scmd])) $scmd = self::$aliases[$scmd];
					switch ($scmd) {
						case "tp":
							if (!$this->access($sender,"mw.cmd.tp")) return false;
							return $this->mwTpCmd($sender,$args);
							break;
						case "ls":
							if (!$this->access($sender,"mw.cmd.ls")) return false;
							return $this->mwLsCmd($sender,$args);
						case "create":
							if (!$this->access($sender,"mw.cmd.world.create")) return false;
							return $this->mwWorldCreateCmd($sender,$args);
							break;
						case "fixname":
							if (!$this->access($sender,"mw.cmd.lvdat")) return false;
							if (count($args) != 1) return $this->mwHelpCmd($sender,["fixname"]);
							$sender->sendMessage("Running /mw lvdat $args[0] name=$args[0]");
							return $this->mwLevelDatCmd($sender,[$args[0], "name=".$args[0]]);
							break;
						case "lvdat":
							if (!$this->access($sender,"mw.cmd.lvdat")) return false;
							return $this->mwLevelDatCmd($sender,$args);
							break;
						case "ld":
							if (!$this->access($sender,"mw.cmd.world.load")) return false;
							return $this->mwWorldLoadCmd($sender,$args);
						case "unload":
							if (!$this->access($sender,"mw.cmd.world.load")) return false;
							return $this->mwWorldUnloadCmd($sender,$args);
						case "help":
							return $this->mwHelpCmd($sender,$args);
						default:
							$sender->sendMessage(TextFormat::RED."Unknown sub command: ".
														TextFormat::RESET.$scmd);
							$sender->sendMessage("Use: ".TextFormat::GREEN." /mw help".
														TextFormat::RESET);
					}
					return true;
				} else {
					$sender->sendMessage("Must specify sub command");
					$sender->sendMessage("Try: ".TextFormat::GREEN." /mw help".
												TextFormat::RESET);
					return false;
				}
		}
		return false;
	}
	// Command entry points
	private function mwTpCmd(CommandSender $sender,$args) {
		if (!isset($args[0]))
			return $this->mwHelpCmd($sender,["tp"]);
		$level = array_shift($args);
		if (isset($args[0])) {
			// Teleport others...
			if (!$this->access($sender,"mw.cmd.tp.others")) return false;
			$player = $this->getServer()->getPlayer($args[0]);
			if (!$player) {
				$sender->sendMessage("[MW] Player ".$args[0]." can not be found");
				return true;
			}
			if (!$player->isOnLine()) {
				$sender->sendMessage("[MW] ".$args[0]." is offline!");
				return true;
			}
			if($player->getLevel() == $this->getServer()->getLevelByName($level)) {
				$sender->sendMessage("[MW] " . $player->getName() . " is already in " . $level . "!");
				return true;
			}
			if (!$this->mwAutoLoad($sender,$level)) {
				$sender->sendMessage("[MW] Unable to teleport " . $player->getName() . " as\nlevel " . $level . " is not loaded!");
				return true;
			}
			$player->sendMessage("[MW] Teleporting you to " . $level . " at\n" . $sender->getName() . "'s request...");
			if ($this->teleport($player,$level)) {
				if ($this->cfg["settings"]["broadcast-tp"]) {
					$this->getServer()->broadcastMessage("[MW] ".$player->getName()." was teleported to $level");
				} else {
					$sender->sendMessage("[MW] " . $player->getName() . " has been teleported to " . $level . "!");
				}
			} else {
				$sender->sendMessage("[MW] unable to teleport ".$player->getName()." to ".$level);
			}
			return true;
		}
		// Teleport self...
		if (!$this->inGame($sender)) return true;
		if ($sender->getLevel() == $this->getServer()->getLevelByName($level)) {
			$sender->sendMessage("[MW] You are already in " . $level . "!");
			return true;
		}
		if(!$this->mwAutoLoad($sender,$level)) {
			$sender->sendMessage("[MW] Unable to teleport");
			$sender->sendMessage("[MW] " . $level . " is not loaded!");
			return true;
		}
		$sender->sendMessage("[MW] Teleporting you to level " . $level . "...");
		if ($this->teleport($sender,$level)) {
			if ($this->cfg["settings"]["broadcast-tp"]) {
				$this->getServer()->broadcastMessage("[MW] ".$sender->getName()." teleported to $level");
			} else {
				$sender->sendMessage("[MW] you were teleported to $level");
			}
		} else {
			$sender->sendMessage("[MW] Unable to teleport ".$sender->getName()." to $level");
		}
		return true;
	}
	private function mwLsCmd(CommandSender $sender,$args) {
		$pageNumber = $this->getPageNumber($args);
		if (isset($args[0])) {
			if(!$this->mwAutoLoad($sender,$args[0])) {
				$sender->sendMessage("[MW] " . $args[0] . " is not loaded!");
				return true;
			}
			$txt = $this->mwWorldDetails($sender,$args[0]);
		} else {
			$txt = $this->mwWorldList($sender);
		}
		if ($txt == null) return true;
		return $this->paginateText($sender,$pageNumber,$txt);
	}
	private function mwLevelDatCmd(CommandSender $sender,$args) {
		if (!count($args)) return $this->mwHelpCmd($sender,["lvdat"]);
		$level = array_shift($args);
		if(!$this->mwAutoLoad($sender,$level)) {
			$sender->sendMessage("[MW] $level is not loaded!");
			return true;
		}
		$world = $this->getServer()->getLevelByName($level);
		if (!$world) {
			$sender->sendMessage("[MW] $level not loaded");
			return null;
		}
		//==== provider
		$provider = $world->getProvider();
		$changed = false; $unload = false;
		foreach ($args as $kv) {
			$kv = explode("=",$kv,2);
			if (count($kv) != 2) {
				$sender->sendMessage("Invalid element: $kv[0], ignored");
				continue;
			}
			list($k,$v) = $kv;
			switch ($k) {
				case "spawn":
					$pos = explode(",",$v);
					if (count($pos)!=3) {
						$sender->sendMessage("Invalid spawn location: ".implode(",",$pos));
						continue;
					}
					list($x,$y,$z) = $pos;
					$cpos = $provider->getSpawn();
					if (($x=intval($x)) == $cpos->getX() &&
						 ($y=intval($y)) == $cpos->getY() &&
						 ($z=intval($z)) == $cpos->getZ()) {
						$sender->sendMessage("Spawn location is unchanged");
						continue;
					}
					$changed = true;
					$provider->setSpawn(new Vector3($x,$y,$z));
					break;
				case "seed":
					if ($provider->getSeed() != intval($v)) {
						$sender->sendMessage("Seed unchanged");
						continue;
					}
					$changed = true; $unload = true;
					$provider->setSeed($v);
					break;
				case "name": // LevelName String
					if ($provider->getName() == $v) {
						$sender->sendMessage("Name unchanged");
						continue;
					}
					$changed = true;
					$provider->getLevelData()->LevelName = new String("LevelName",$v);
					break;
				case "generator":	// generatorName(String)
					if ($provider->getLevelData()->generatorName == $v) {
						$sender->sendMessage("Generator unchanged");
						continue;
					}
					$changed=true; $unload=true;
					$provider->getLevelData()->generatorName=new String("generatorName",$v);
					break;
				case "preset":	// String("generatorOptions");
					if ($provider->getLevelData()->generatorOptions == $v) {
						$sender->sendMessage("Preset unchanged");
						continue;
					}
					$changed=true; $unload=true;
					$provider->getLevelData()->generatorOptions =
																			  new String("generatorOptions",$v);
					break;
				default:
					$sender->sendMessage("Unknown key $k, ignored");
					continue;
			}
		}
		if ($changed) {
			$sender->sendMessage("Updating level.dat for $level");
			$provider->saveLevelData();
			if ($unload) {
				$sender->sendMessage(TextFormat::RED.
											"CHANGES WILL NOT TAKE EFFECT UNTIL UNLOAD");
			}
		} else {
			$sender->sendMessage("Nothing happens");
		}
		return true;
	}

	private function mwWorldCreateCmd(CommandSender $sender,$args) {
		if (!isset($args[0]))
			return $this->mwHelpCmd($sender,["create"]);
		$level = array_shift($args);
		if($this->getServer()->isLevelGenerated($level)) {
			$sender->sendMessage("[MW] A world with the name " . $level . " already exists!");
			return true;
		}
		$seed = null;
		$generator = null;
		$options = [];
		if(isset($args[0])) $seed = intval($args[0]);
		if(isset($args[1])) {
			$generator = Generator::getGenerator($args[1]);
			$sender->sendMessage("Using ".Generator::getGeneratorName($generator));
		}
		if(isset($args[2])) $options = ["preset" => $args[2] ];
		$this->getServer()->broadcastMessage("[MW] Creating level " . $level . "... (Expect Lag)");
		$this->getServer()->generateLevel($level, $seed, $generator, $options);
		$this->getServer()->loadLevel($level);
		return true;
	}
	private function mwWorldLoadCmd(CommandSender $sender,$args) {
		if (!isset($args[0]))
			return $this->mwHelpCmd($sender,["ld"]);
		if ($args[0] == "--all") {
			$sender->sendMessage("[MW] ".TextFormat::RED."Loading ALL levels".TextFormat::RESET);
			$args = [];
			foreach (glob($this->getServer()->getDataPath(). "worlds/*") as $f) {
				$level = basename($f);
				if ($this->getServer()->isLevelLoaded($level)) continue;
				if (!$this->getServer()->isLevelGenerated($level)) continue;
				$args[] = $level;
			}
		}
		foreach ($args as $level) {
			if (!$this->mwAutoLoad($sender,$level))
				$sender->sendMessage("[MW] Unable to load $level");
		}
		return true;
	}
	private function mwWorldUnloadCmd(CommandSender $sender,$args) {
		if (!isset($args[0]))
			return $this->mwHelpCmd($sender,["unload"]);

		// Activate|Deactive unload command
		if (isset($args[0]) && $args[0] == '--enable') {
			$this->canUnload = true;
			$sender->sendMessage("[MW] Unload sub-command enabled");
			$sender->sendMessage("[MW] To disable use: /mw unload --disable");
			return true;
		} elseif (isset($args[0]) && $args[0] == '--disable') {
			$this->canUnload = true;
			$sender->sendMessage("[MW] Unload sub-command disabled");
			$sender->sendMessage("[MW] To enable use: /mw unload --enable");
			return true;
		}
		if (!$this->canUnload) {
			$sender->sendMessage("[MW] Unload sub-command is disabled by default");
			$sender->sendMessage("[MW] this is because that it usually causes the");
			$sender->sendMessage("[MW] server to ".TextFormat::RED."crash.".TextFormat::RESET);
			$sender->sendMessage("[MW] Use: ".TextFormat::BLUE."/mw unload --enable".TextFormat::RESET);
			$sender->sendMessage("[MW] To activate");
			return true;
		}

		// Actual implementation
		$force = false;
		if (isset($args[0]) && $args[0] == '-f') {
			$force = true;
			array_shift($args);
		}
		if (!isset($args[0]))
			return $this->mwHelpCmd($sender,["unload"]);

		foreach ($args as $level) {
			$level = $args[0];
			if (!$this->getServer()->isLevelLoaded($level)) {
				$sender->sendMessage("[MW] Level $level is not loaded.");
				continue;
			}
			$world = $this->getServer()->getLevelByName($level);
			if ($world === null) {
				$sender->sendMessage("[MW] Unable to get $level");
				continue;
			}
			if (!$this->getServer()->unloadLevel($world,$force)) {
				$sender->sendMessage("[MW] Unable to unload $level.  Try -f");
				continue;
			}
			$sender->sendMessage("[MW] $level unloaded.");
		}
		return true;
	}
	private function mwHelpCmd(CommandSender $sender,$args) {
		$pageNumber = $this->getPageNumber($args);
		$cmds = [
			"tp" => ["<level> [player]",
						"Teleport across worlds"],
			"ls" => ["[level]",
						"List world information"],
			"create" => ["<level> [seed [flat|normal [preset]]]",
							 "Create a new world"],
			"lvdat" => ["<level> [attr=value]","Manipulate level.dat"],
			"fixname" => ["<level>","Fix level.dat world names"],
			"ld" => ["<level>|--all","Load a world"],
			"unload" => ["<level>","Attempt to unload a world"],
		];
		if (count($args)) {
			foreach ($args as $c) {
				if (isset(self::$aliases[$c])) $c = self::$aliases[$c];
				if (isset($cmds[$c])) {
					list($a,$b) = $cmds[$c];
					$sender->sendMessage(TextFormat::RED."Usage: /mw $c $a"
												.TextFormat::RESET);
					$sender->sendMessage($b);
				}
			}
			return true;
		}
		$txt = ["ManyWorlds sub-commands"];
		foreach ($cmds as $a => $b) {
			$ln = "- ".TextFormat::GREEN."/mw ".$a;
			foreach (self::$aliases as $i => $j) {
				if ($j == $a) $ln .= "|$i";
			}
			$ln .= TextFormat::RESET." ".$b[0];
			$txt[] = $ln;
		}
		return $this->paginateText($sender,$pageNumber,$txt);
	}

	//
	// Helper functions
	//
	private function mwAutoLoad(CommandSender $c,$level) {
		if ($this->getServer()->isLevelLoaded($level)) return true;
		if(!$this->access($c, "mw.cmd.world.load")) return false;
		if(!$this->getServer()->isLevelGenerated($level)) {
			$c->sendMessage("[MW] No level with the name $level exists!");
			return false;
		}
		$this->getServer()->loadLevel($level);
		return true;
	}
	private function mwWorldList(CommandSender $sender) {
		$dir = $this->getServer()->getDataPath(). "worlds";
		if (!is_dir($dir)) {
			$sender->sendMessage("[MW] Missing path $dir");
			return null;
		}
		$txt = ["HDR"];

		$auto = $this->getServer()->getProperty("worlds",[]);
		$default = $this->getServer()->getDefaultLevel();
		if ($default) $default = $default->getName();

		$count = 0;
		$dh = opendir($dir);
		if (!$dh) return null;
		while (($file = readdir($dh)) !== false) {
			if ($file == '.' || $file == '..') continue;
			if (!$this->getServer()->isLevelGenerated($file)) continue;
			$attrs = [];
			++$count;
			if (isset($auto[$file])) $attrs[] = "auto";
			if ($default == $file) $attrs[]="default";
			if ($this->getServer()->isLevelLoaded($file)) {
				$attrs[] = "loaded";
				$np = count($this->getServer()->getLevelByName($file)->getPlayers());
				if ($np) $attrs[] = "players:$np";
			}
			$ln = "- $file";
			if (count($attrs)) $ln .= TextFormat::AQUA." (".implode(",",$attrs).")";
			$txt[] = $ln;
		}
		closedir($dh);
		$txt[0] = "Worlds: ".$count;
		return $txt;
	}
	private function mwWorldDetails(CommandSender $sender,$level) {
		$txt = [];
		$world = $this->getServer()->getLevelByName($level);
		if (!$world) {
			$sender->sendMessage("[MW] $level not loaded");
			return null;
		}
		//==== provider
		$provider = $world->getProvider();
		$txt[] = "Info for $level";
		$txt[] = TextFormat::AQUA."Provider: ".TextFormat::WHITE. $provider::getProviderName();
		$txt[] = TextFormat::AQUA."Path: ".TextFormat::WHITE.$provider->getPath();
		$txt[] = TextFormat::AQUA."Name: ".TextFormat::WHITE.$provider->getName();
		$txt[] = TextFormat::AQUA."Seed: ".TextFormat::WHITE.$provider->getSeed();
		$txt[] = TextFormat::AQUA."Generator: ".TextFormat::WHITE.$provider->getGenerator();
		$gopts = $provider->getGeneratorOptions();
		if ($gopts["preset"] != "")
			$txt[] = TextFormat::AQUA."Generator Presets: ".TextFormat::WHITE.
					 $gopts["preset"];
		$spawn = $provider->getSpawn();
		$txt[] = TextFormat::AQUA."Spawn: ".TextFormat::WHITE.$spawn->getX().",".$spawn->getY().",".$spawn->getZ();
		$plst = $this->getServer()->getLevelByName($level)->getPlayers();
		$lst = "";
		if (count($plst)) {
			foreach ($plst as $p) {
				$lst .= (strlen($lst) ? ", " : "").$p->getName();
			}
		}
		$total = count($plst);
		$max = call_user_func($this->maxplayers,$level);
		if ($max) $total .= "/$max";
		$txt[] = TextFormat::AQUA."Players(".TextFormat::WHITE.$total.
				 TextFormat::AQUA."): ".TextFormat::WHITE.$lst;

		$fn = "getWorldInfo";
		foreach ($this->getServer()->getPluginManager()->getPlugins() as $p) {
			if ($p->isDisabled()) continue;
			if (is_callable([$p,$fn])) {
				foreach (call_user_func([$p,$fn],$world->getName()) as $ln) {
					$txt[] = $ln;
				}
			}
		}
		//////////////////////////////////////////////////////////////////////
		// Checks
		//$txt[] = "levelName:    ".$world->getName()."\n";
		//$txt[] = "folderName:   ".$world->getFolderName()."\n";
		//$txt[] = "providerName: ".$provider->getName()."\n";
		//////////////////////////////////////////////////////////////////////


		// Check for warnings...
		if ($provider->getName() != $level) {
			$txt[] = TextFormat::RED."Folder Name and Level.Dat names do NOT match";
			$txt[] = TextFormat::RED."This can cause intermitent problems";
			if($sender->hasPermission("mw.cmd.lvdat")) {
				$txt[] = TextFormat::RED."Use: ";
				$txt[] = TextFormat::GREEN."> /mw fixname $level";
				$txt[] = TextFormat::RED."to fix this issue";
			}
		}
		return $txt;
	}
	public function _getPlayerLimit($level) { return 0; }
	public function maxPlayers1st($level) {
		$fn = "getPlayerLimit";
		foreach ($this->getServer()->getPluginManager()->getPlugins() as $p) {
			if ($p->isDisabled()) continue;
			if (is_callable([$p,$fn])) {
				$this->maxplayers = [$p,$fn];
				$this->getLogger()->info(TextFormat::YELLOW."Using plugin ".
												 $p->getName()." for Player Limits");
				return call_user_func($this->maxplayers,$level);
			}
		}
		$this->maxplayers = [$this,"_".$fn];
		return call_user_func($this->maxplayers,$level);
	}
	//
	// Public API
	//
	public function mwtp($pl,$pos) {
		if ($pos instanceof Position) {
			// Using ManyWorlds for teleporting...
			return $this->teleport($pl,$pos->getLevel()->getName(),
										  new Vector3($pos->getX(),
														  $pos->getY(),
														  $pos->getZ()));
		}
		$pl->teleport($pos);
		return true;
	}
	public function teleport($player,$level,$spawn=null) {
		if (!$this->getServer()->isLevelLoaded($level)) return false;
		/*
		 * Check if we can enforce player limits
		 */
		$max = call_user_func($this->maxplayers,$level);
		if ($max) {
			$np = count($this->getServer()->getLevelByName($level)->getPlayers());
			if ($np >= $max) {
				$player->sendMessage("Can not teleport to $level, its FULL\n");
				return false;
			}
		}
		return $this->tpManager->teleport($player,$level,$spawn);
	}
}
