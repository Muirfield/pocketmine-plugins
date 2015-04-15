<?php
namespace grabbag;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandExecutor;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerChatEvent;

use pocketmine\utils\Config;
use pocketmine\command\PluginCommand;
use pocketmine\entity\Living;
use pocketmine\nbt\tag\Compound;
use pocketmine\scheduler\CallbackTask;
use pocketmine\tile\Sign;
use pocketmine\level\Position;

//use pocketmine\entity\Entity;
//use pocketmine\nbt\tag\Byte;
//use pocketmine\nbt\tag\Double;
//use pocketmine\nbt\tag\Enum;
//use pocketmine\nbt\tag\Float;
//use pocketmine\utils\Random;
use pocketmine\item\Item;
use pocketmine\math\Vector3;


class Main extends PluginBase implements CommandExecutor {
	protected $listeners = [];
	protected $config;
	protected $modules;
	protected $slain = [];
	protected $shield = [];
	protected $state = [];
	protected $rpt;

	static $items = [];
	// Override the MaxStacks counter...
	static $stacks = [ Item::MINECART => 1, Item::BOOK => 1, Item::COMPASS => 1,
							 Item::CLOCK => 1 ];

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
	public function checkModule($name) {
		return array_key_exists($name,$this->modules["listener"]);
	}
	public function checkCommand($name) {
		return array_key_exists($name,$this->modules["commands"]);
	}
	public function itemName(Item $item) {
		if (count(self::$items) == 0) {
			$constants = array_keys((new \ReflectionClass("pocketmine\\item\\Item"))->getConstants());
			foreach ($constants as $constant) {
				$id = constant("pocketmine\\item\\Item::$constant");
				$constant = str_replace("_", " ", $constant);
				self::$items[$id] = $constant;
			}
		}
		$n = $item->getName();
		if ($n != "Unknown") return $n;
		if (isset(self::$items[$item->getId()]))
			return self::$items[$item->getId()];
		return $n;
	}
	public function cleanupPlayer($pl) {
		if (isset($this->listeners["cmd.mute"])) {
			$this->listeners["cmd.mute"]->unmute($pl);
		}
		if (isset($this->listeners["cmd.freeze"])) {
			$this->listeners["cmd.freeze"]->thaw($pl);
		}
	}
	public function runCommand($cmd) {
		$this->getServer()->dispatchCommand(new ConsoleCommandSender(),$cmd);
	}
	private function dumpNbtIndent($spc,&$off,&$last) {
		if (isset($off[$spc])) return $off[$spc];
		$last += 2;
		$off[$spc] = str_repeat(' ',$last);
		return $off[$spc];
	}
	public function dumpNbt($nbt) {
		$txt = [];
		$name = '';
		$off=[];
		$last = 0;

		foreach (explode("\n",print_r($nbt,true)) as $ln) {
			if (trim($ln) == "(" || trim($ln) == ")" || trim($ln) == "") continue;
			if (preg_match('/^(\s*)(\[[^\]]+\])\s*=>\s*pocketmine\\\\nbt\\\\tag\\\\(Enum|Compound)/',$ln,$m)) {
				$txt[] = ".".$this->dumpNbtIndent($m[1],$off,$last).$m[2];
				continue;
			}
			if (preg_match('/^\s*\[name:protected\]\s*=>\s*(.*)$/',$ln,$m)) {
				$name = $m[1];
			}
			if (preg_match('/^(\s*)\[value:protected\]\s*=>\s*(.*)$/',$ln,$m)) {
				if ($m[2] == "Array") continue;
				$txt[] = ".".$this->dumpNbtIndent($m[1],$off,$last).$name.": ".
						 $m[2];
				$name = "";
			}
		}
		return $txt;
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
		$fmt = "";
		foreach ($cols as $c) {
			if (strlen($fmt) > 0) $fmt .= " ";
			$fmt .= "%-".$c."s";
		}
		foreach ($tab as $row) {
			$txt[] = sprintf($fmt,...$row);
		}
		return $this->paginateText($sender,$pageNumber,$txt);
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Standard call-backs
	//
	//////////////////////////////////////////////////////////////////////
	public function onLoad() {
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());

		$v = $this->getDescription()->getVersion();
		$modules = $this->getDataFolder()."modules-dist.yml";
		$modcfg = $this->getDataFolder()."modules.yml";
		$current = is_file($modules) ? file_get_contents($modules) : "";
		$active = is_file($modcfg) ? file_get_contents($modcfg) : "";
		$fp = $this->getResource(basename($modcfg));
		$next = "version: $v\n".stream_get_contents($fp);
		fclose($fp);
		if ($next != $current) {
			// We need to upgrade...
			file_put_contents($modules,$next);
			if ($current == $active) {
				// It is not a custom yml, so we just upgrade...
				file_put_contents($modcfg,$next);
				$this->getLogger()->info(TextFormat::RED."module configuration has been updated".TextFormat::RESET);
			} else {
				$this->getLogger()->info(TextFormat::RED."modules-dist.yml has been updated".TextFormat::RESET);
				$this->getLogger()->info(TextFormat::GREEN."Review your modules.yml to activate new features".TextFormat::RESET);
			}
		} else {
			if ($active == "") {
				// Special case... user deleted modules.yml...
				file_put_contents($modcfg,$next);
				$this->getLogger()->info(TextFormat::GREEN."modules.yml initialized with defaults".TextFormat::RESET);
			}
		}
		$this->modules =(new Config($this->getDataFolder()."modules.yml",
											 Config::YAML,[]))->getAll();
		foreach (["listener","commands"] as $i) {
			if (!isset($this->modules[$i])) $this->modules[$i] = [];
		}

		$pluginCmds = [];
		foreach ($this->modules["commands"] as $cmd => $dat) {
			if(strpos($cmd, ":") !== false){
				$this->getLogger()->info("Unable to load command $cmd");
				continue;
			}
			if (!is_array($dat)) continue;
			$newCmd = new PluginCommand($cmd,$this);
			if(isset($dat["description"])){
				$newCmd->setDescription($dat["description"]);
			}
			if(isset($dat["usage"])){
				$newCmd->setUsage($dat["usage"]);
			}
			if(isset($dat["aliases"]) and is_array($dat["aliases"])){
				$aliasList = [];
				foreach($dat["aliases"] as $alias){
					if(strpos($alias, ":") !== false){
						$this->getLogger()->info("Unable to load alias $alias");
						continue;
					}
					$aliasList[] = $alias;
				}
				$newCmd->setAliases($aliasList);
			}
			if(isset($dat["permission"])){
				$newCmd->setPermission($dat["permission"]);
			}
			if(isset($dat["permission-message"])){
				$newCmd->setPermissionMessage($dat["permission-message"]);
			}
			$pluginCmds[] = $newCmd;
		}
		if (count($pluginCmds) > 0) {
			$cmdMap = $this->getServer()->getCommandMap();
			$cmdMap->registerAll($this->getDescription()->getName(),$pluginCmds);
			$this->getLogger()->info("Loaded ".count($pluginCmds)." command(s)");
		}
	}
	public function onEnable(){
		$defaults =
					 [
						 "settings" =>[
							 "hard-freeze"=>false,
						 ],
						 "unbreakable"=>[
							 7,
							 95,
						 ],
						 "spawn"=>[
							 "armor"=>[
								 "head"=>"-",
								 "body"=>"chainmail",
								 "legs"=>"leather",
								 "boots"=>"leather",
							 ],
							 "items"=>[
								 "272:0:1",
								 "17:0:16",
								 "364:0:5",
							 ],
						 ],
					 ];
		if (file_exists($this->getDataFolder()."config.yml")) {
			unset($defaults["spawn"]["items"]);
			unset($defaults["unbreakable"]);
		}
		$this->config=(new Config($this->getDataFolder()."config.yml",
										  Config::YAML,$defaults))->getAll();

		$hardfreeze = isset($this->config["settings"]["hard-freeze"]) ?
						$this->config["settings"]["hard-freeze"] : false;

		if (array_key_exists("adminjoin",$this->modules["listener"])
			 || array_key_exists("rpt",$this->modules["commands"])
			 || array_key_exists("servermotd",$this->modules["listener"]))
			$this->listeners["adminjoin"] = new AdminJoinMgr($this);
		if (array_key_exists("spawnitems",$this->modules["listener"])
			 || array_key_exists("spawnarmor",$this->modules["listener"]))
			$this->listeners["spawnmgr"] = new SpawnMgr($this);
		if (array_key_exists("unbreakable",$this->modules["listener"]) &&
			 isset($this->config["unbreakable"]) &&
			 is_array($this->config["unbreakable"]))
			$this->listeners["unbreakable"] = new UnbreakMgr($this,
																			 $this->config["unbreakable"]);
		if (array_key_exists("compasstp",$this->modules["listener"]))
			$this->listeners["compasstp"] = new CompassTpMgr($this);
		if (array_key_exists("repeater",$this->modules["listener"]))
			$this->listeners["repeater"] = new RepeatMgr($this);
		if (array_key_exists("slay",$this->modules["commands"]))
			$this->listeners["cmd.slay"] = new ReaperMgr($this);
		if (array_key_exists("shield",$this->modules["commands"]))
			$this->listeners["cmd.shield"] = new ShieldMgr($this);
		if (array_key_exists("servicemode",$this->modules["commands"]))
			$this->listeners["cmd.servicemode"] = new SrvModeMgr($this);
		if (array_key_exists("mute",$this->modules["commands"]) &&
			 array_key_exists("unmute",$this->modules["commands"]))
			$this->listeners["cmd.mute"] = new MuteMgr($this);
		if (array_key_exists("freeze",$this->modules["commands"]) &&
			 array_key_exists("thaw",$this->modules["commands"]))
			$this->listeners["cmd.freeze"] = new FreezeMgr($this,$hardfreeze);
		if (array_key_exists("shield",$this->modules["commands"]) ||
			 array_key_exists("summon",$this->modules["commands"])) {
			$this->listeners["cleanup"] = new Cleaner($this);
		}
		if (array_key_exists("rpt",$this->modules["commands"])) {
			$this->rpt = new Config($this->getDataFolder()."reports.yml",
											Config::YAML,[0,[]]);
		} else {
			$this->rpt = null;
		}

		$this->getLogger()->info("Installed ".count($this->listeners)." managers");
	}
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		// Make sure the command is active
		if (!isset($this->modules["commands"][$cmd->getName()])) return false;
		switch($cmd->getName()) {
			case "ops":
				return $this->cmdOps($sender,$args);
			case "players":
				return $this->cmdPlayers($sender,$args);
			case "as":
				return $this->cmdSudo($sender,$args);
			case "gms":
				return $this->cmdGmX($sender,0);
			case "gmc":
				return $this->cmdGmX($sender,1);
			case "gma":
				return $this->cmdGmX($sender,2);
			case "slay":
				return $this->cmdSlay($sender,$args);
			case "heal":
				return $this->cmdHeal($sender,$args);
			case "whois":
				return $this->cmdWhois($sender,$args);
			case "showtimings":
				return $this->cmdTimings($sender,$args);
			case "get":
				return $this->cmdGet($sender,$args);
			case "seeinv":
				return $this->cmdSeeInv($sender,$args);
			case "clearinv":
				return $this->cmdClearInv($sender,$args);
			case "seearmor":
				return $this->cmdSeeArmor($sender,$args);
			case "shield":
				return $this->cmdShield($sender,$args);
			case "mute":
			case "unmute":
				return $this->cmdMute($sender,$cmd->getName(),$args);
			case "freeze":
			case "thaw":
				return $this->cmdFreeze($sender,$cmd->getName(),$args);
			case "servicemode":
				return $this->cmdSrvMode($sender,$args);
			case "opms":
				return $this->cmdOpMsg($sender,$args);
			case "rpt":
				return $this->cmdRpt($sender,$args);
			case "after":
				return $this->cmdAfter($sender,$args);
			case "at":
				return $this->cmdAt($sender,$args);
			case "entities":
				return $this->cmdEntities($sender,$args);
			case "summon":
				return $this->cmdSummon($sender,$args);
			case "dismiss":
				return $this->cmdDismiss($sender,$args);
			case "pushtp":
				return $this->cmdPushTp($sender,$args);
			case "poptp":
				return $this->cmdPopTp($sender,$args);
		}
		return false;
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Command implementations
	//
	//////////////////////////////////////////////////////////////////////
	private function mwteleport($pl,$pos) {
		if (($pos instanceof Position) &&
			 ($mw = $this->getServer()->getPluginManager()->getPlugin("ManyWorlds")) != null) {
			// Using ManyWorlds for teleporting...
			$mw->teleport($pl,$pos->getLevel()->getName(),
							  new Vector3($pos->getX(),
											  $pos->getY(),
											  $pos->getZ()));
		} else {
			$pl->teleport($pos);
		}
	}

	private function cmdPushTp(CommandSender $c,$args) {
		if (!$this->inGame($c)) return true;

		// Determine target...
		if (count($args) == 3 && is_numeric($args[0]) && is_numeric($args[1]) && is_numeric($args[2])) {
			$target = new Vector3($args[0],$args[1],$args[2]);
		} elseif (count($args) == 1 || count($args) == 4) {
			// is it a person or a world?...
			if (count($args) == 1
				 && ($pl = $this->getServer()->getPlayer($args[0]))) {
				$target = $pl;
			} else {
				// Assume it is a level...
				$level = array_shift($args);
				if (count($args) == 3) {
					if (!(is_numeric($args[0]) && is_numeric($args[1]) && is_numeric($args[2]))) {
						$c->sendMessage("Invalid coordinate set");
						return true;
					}
					$cc = new Vector3($args[0],$args[1],$args[2]);
				} else {
					$cc = null;
				}
				if (!$this->getServer()->isLevelLoaded($level)) {
					if (!$this->getServer()->loadLevel($level)) {
						$c->sendMessage("Level not found $level");
						return true;
					}
				}
				$level = $this->getServer()->getLevelByName($level);
				if (!$level) {
					$c->sendMesage("$level not found");
					return treu;
				}
				$target = $level->getSafeSpawn($cc);
			}
		} elseif (count($args) == 0) {
			$target = null;
		} else {
			return false;
		}

		// save location...
		$cn = $c->getName();
		if (!isset($this->state[$cn])) $this->state[$cn] = [];
		if (!isset($this->state[$cn]["tpstack"]))
			$this->state[$cn]["tpstack"] = [];
		array_push($this->state[$cn]["tpstack"],new Position($c->getX(),
																			  $c->getY(),
																			  $c->getZ(),
																			  $c->getLevel()));
		$c->sendMessage("Position saved!");


		if ($target) {
			$c->sendMessage("Teleporting...");
			$this->mwteleport($c,$target);
		}
		return true;
	}
	private function cmdPopTp(CommandSender $c,$args) {
		if (!$this->inGame($c)) return true;
		$cn = $c->getName();
		if (!isset($this->state[$cn]) ||
			 !isset($this->state[$cn]["tpstack"]) ||
			 count($this->state[$cn]["tpstack"]) == 0) {
			$c->sendMessage("Your tpstack is empty");
			return true;
		}
		$pos = array_pop($this->state[$cn]["tpstack"]);
		$c->sendMessage("Teleporting...");
		$this->mwteleport($c,$pos);
	}
	private function cmdSummon(CommandSender $c,$args) {
		if (count($args) == 0) return false;
		if (!$this->inGame($c)) return true;
		$pl = $this->getServer()->getPlayer($args[0]);
		if (!$pl) {
			$c->sendMessage("$args[0] can not be found");
			return true;
		}
		array_shift($args);
		if (count($args)) {
			$pl->sendMessage(implode(" ",$args));
		} else {
			$pl->sendMessage("You have been summoned by ".$c->getName());
		}
		// Do we need to save current location?
		$cn = $c->getName();
		$pn = $pl->getName();
		if (!isset($this->state[$cn])) $this->state[$cn] = [];
		if (!isset($this->state[$cn]["summons"]))
			$this->state[$cn]["summons"] = [];
		if (!isset($this->state[$cn]["summons"][$pn])) {
			$this->state[$cn]["summons"][$pn] = new Position($pl->getX(),
																			 $pl->getY(),
																			 $pl->getZ(),
																			 $pl->getLevel());
		}
		$mv = new Vector3($c->getX()+mt_rand(-3,3),$c->getY(),
								$c->getZ()+mt_rand(-3,3));
		$c->sendMessage("Summoning $pn....");
		$this->mwteleport($pl,$c->getLevel()->getSafeSpawn($mv));
		return true;
	}
	private function cmdDismiss(CommandSender $c,$args) {
		if (count($args) == 0) return false;
		if (!$this->inGame($c)) return true;
		$cn = $c->getName();
		if (!isset($this->state[$cn]) ||
			 !isset($this->state[$cn]["summons"]) ||
			 count($this->state[$cn]["summons"]) == 0) {
			$c->sendMessage("There is nobody to dismiss");
			$c->sendMessage("You need to summon people first");
			return true;
		}
		$plist = [ array_shift($args) ];
		if ($plist[0] == "--all") {
			$plist = array_keys($this->state[$cn]["summons"]);
		}
		if (count($args)) {
			$msg = implode(" ",$args);
		} else {
			$msg = "You have been dismissed by ".$cn;
		}
		foreach ($plist as $i) {
			$pl = $this->getServer()->getPlayer($i);
			if (!$pl) {
				$c->sendMessage("$i can not be found");
				continue;
			}
			$pn = $pl->getName();
			if (!isset($this->state[$cn]["summons"][$pn])) {
				$c->sendMessage("$pn was never summoned");
				continue;
			}
			$pl->sendMessage($msg);
			$c->sendMessage("Dismissing $pn");
			$this->mwteleport($pl,$this->state[$cn]["summons"][$pn]);
			unset($this->state[$cn]["summons"][$pn]);
		}
		return true;
	}

	private function cmdAfter(CommandSender $c,$args) {
		if (count($args) < 2) return false;
		if (!is_numeric($args[0])) {
			$c->sendMessage("Unable to specify delay $args[0]");
			return false;
		}
		$secs = intval(array_shift($args));
		$c->sendMessage("Scheduled for ".date(DATE_RFC2822,time()+$secs));
		$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"runCommand"],[implode(" ",$args)]),$secs * 20);
		return true;
	}
	private function cmdAt(CommandSender $c,$args) {
		if (count($args) < 2) {
			$c->sendMessage("Time now is: ".date(DATE_RFC2822));
			return false;
		}
		if (($pos = array_search(":",$args)) != false) {
			if ($pos == 0) return false;
			$ts = [];
			while ($pos--) {
				$ts[] = array_shift($args);
			}
			array_shift($args);
			if (count($args) == 0) return false;
			$ts = implode(" ",$ts);
			$when = strtotime($ts);
		} else {
			for ($ts = array_shift($args);
				  ($when = strtotime($ts)) == false && count($args) > 1;
				  $ts .= ' '.array_shift($args)) ;
		}
		if ($when == false) {
			$c->sendMessage("Unable to parse time specification $ts");
			return false;
		}
		while ($when < time()) {
			$when += 86400; // We can not travel back in time...
		}
		$c->sendMessage("Scheduled for ".date(DATE_RFC2822,$when));
		$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"runCommand"],[implode(" ",$args)]),($when - time())*20);
		return true;
	}
	private function cmdWhois(CommandSender $c,$args) {
		$pageNumber = $this->getPageNumber($args);
		if (count($args) != 1) {
			$c->sendMessage("You must specify a player's name");
			return true;
		}
		$target = $this->getServer()->getPlayer($args[0]);
		if($target == null) {
			$c->sendMessage($args[0]." can not be found.");
			return true;
		}
		$txt = [];
		$txt[] = TextFormat::AQUA."About $args[0]".TextFormat::RESET;
		$txt[] = TextFormat::GREEN."Health: ".TextFormat::WHITE
				 ."[".$target->getHealth()."/".$target->getMaxHealth()."]"
				 .TextFormat::RESET;
		$txt[] = TextFormat::GREEN."World: ".TextFormat::WHITE
				 .$target->getLevel()->getName().TextFormat::RESET;

		$txt[] = TextFormat::GREEN."Location: ".TextFormat::WHITE."X:".floor($target->getPosition()->x)." Y:".floor($target->getPosition()->y)." Z:".floor($target->getPosition()->z)."".TextFormat::RESET;
		if ($c->hasPermission("gb.cmd.whois.showip"))
			$txt[] = TextFormat::GREEN."IP Address: ".TextFormat::WHITE.$target->getAddress().TextFormat::RESET;
		$txt[] = TextFormat::GREEN."Gamemode: ".TextFormat::WHITE
				 .ucfirst(strtolower(Server::getGamemodeString($target->getGamemode())))
				 .TextFormat::RESET;
		$txt[] = TextFormat::GREEN."Whitelisted: ".TextFormat::WHITE
				 . ($target->isWhitelisted() ? "YES" : "NO").TextFormat::RESET;
		$txt[] = TextFormat::GREEN."Opped: ".TextFormat::WHITE
				 . ($target->isOp() ? "YES" : "NO").TextFormat::RESET;
		$txt[] = TextFormat::GREEN."Dislay Name: ".TextFormat::WHITE
				 . $target->getDisplayName().TextFormat::RESET;
		$txt[] = TextFormat::GREEN."Flying: ".TextFormat::WHITE
				 . ($target->isOnGround() ? "NO" : "YES").TextFormat::RESET;

		if (isset($this->modules["commands"]["shield"])) {
			$txt[] = TextFormat::GREEN."Shield: ".TextFormat::WHITE
					 . (isset($this->shield[$target->getName()]) ? "UP" : "DOWN").TextFormat::RESET;
		}
		return $this->paginateText($c,$pageNumber,$txt);
	}

	private function cmdHeal(CommandSender $c,$args) {
		if (count($args) == 0) {
			if (!$this->inGame($c)) return true;
			$c->setHealth($c->getMaxHealth());
			$c->sendMessage("You have been healed");
			return true;
		}
		$patient = $this->getServer()->getPlayer($args[0]);
		if ($patient == null) {
			$c->sendMessage("$args[0] was not found");
			return true;
		}
		if (isset($args[1]) && is_numeric($args[1])) {
			$health = $patient->getHealth() + intval($args[1]);
			if ($health > $patient->getMaxHealth()) $health = $patient->getMaxHealth();
		} else {
			$health = $patient->getMaxHealth();
		}
		$patient->setHealth($health);
		$c->sendMessage("$args[0] was healed.");
		return true;
	}
	private function cmdMute(CommandSender $c,$cmd,$args) {
		if (count($args)) {
			$cnt = 0;
			foreach ($args as $i) {
				$pl = $this->getServer()->getPlayer($i);
				if ($pl) {
					if ($cmd == "mute") {
						$msg = $this->listeners["cmd.mute"]->mute($pl->getName());
					} else {
						$msg = $this->listeners["cmd.mute"]->unmute($pl->getName());
					}
					if ($msg)
						$c->sendMessage($msg);
					else {
						++$cnt;
						$pl->sendMessage("You have been muted by ".$c->getName());
					}
				} else {
					$c->sendMessage("Player $i not found");
				}
			}
			if (!$cnt) return false;
			$c->sendMessage("Affected Players: $cnt");
			return true;
		}
		$lst = $this->listeners["cmd.mute"]->getMutes();
		$c->sendMessage("Mutes: ".count($lst));
		if (count($lst)) $c->sendMessage(implode(", ",$lst));
		return true;
	}
	private function cmdFreeze(CommandSender $c,$cmd,$args) {
		if (count($args)) {
			$cnt = 0;
			foreach ($args as $i) {
				$pl = $this->getServer()->getPlayer($i);
				if ($pl) {
					if ($cmd == "freeze") {
						$msg = $this->listeners["cmd.freeze"]->freeze($pl->getName());
					} else {
						$msg = $this->listeners["cmd.freeze"]->thaw($pl->getName());
					}
					if ($msg)
						$c->sendMessage($msg);
					else {
						++$cnt;
						$pl->sendMessage("You have been frozen by ".$c->getName());
					}
				} else {
					$c->sendMessage("Player $i not found");
				}
			}
			if (!$cnt) return false;
			$c->sendMessage("Affected Players: $cnt");
			return true;
		}
		$lst = $this->listeners["cmd.freeze"]->getFrosties();
		$c->sendMessage("Frozen: ".count($lst));
		if (count($lst)) $c->sendMessage(implode(", ",$lst));
		return true;
	}

	private function cmdShield(CommandSender $c,$args) {
		if (!$this->inGame($c)) return true;
		if (count($args) > 1) return false;
		$name = $c->getName();
		if (count($args) == 0) {
			if (isset($this->shield[$name])) {
				$c->sendMessage("Shields UP!");
			} else {
				$c->sendMessage("Shields DOWN!");
			}
			return true;
		}
		$status = strtolower($args[0]);
		if ($status =="on" || $status=="up" || $status=="true" || $status==1) {
			if (isset($this->shield[$name])) {
				$c->sendMessage("Shields are already up");
			} else {
				$c->sendMessage("Raising shields!");
				$this->shield[$name] = $name;
			}
			return true;
		}
		if (!isset($this->shield[$name])) {
			$c->sendMessage("Shields are already down");
			return true;
		}
		$c->sendMessage("Lowering shields!");
		unset($this->shield[$name]);
		return true;
	}
	public function checkShield($name) {
		if (isset($this->shield[$name])) return false;
		return true;
	}
	private function cmdSrvMode(CommandSender $c,$args) {
		if (count($args) == 0) {
			$mode = $this->listeners["cmd.servicemode"]->getMode();
			if ($mode) {
				$c->sendMessage(TextFormat::RED."In Service Mode: $mode");
			} else {
				$c->sendMessage(TextFormat::GREEN."In Normal operating mode");
			}
			return true;
		}
		$status = strtolower(array_shift($args));
		if ($status =="on" || $status=="up" || $status=="true" || $status==1) {
			$msg = implode(" ",$args);
			if (!$msg) $msg = "Scheduled maintenance";
		} else {
			$msg = false;
		}
		$this->listeners["cmd.servicemode"]->setMode($msg);
		return true;
	}

	private function cmdSlay(CommandSender $c,$args) {
		if (!isset($args[0])) {
			$c->sendMessage("Must specify a player to slay");
			return true;
		}
		$victim = $this->getServer()->getPlayer($args[0]);
		if ($victim == null) {
			$c->sendMessage("Player $args[0] was not found!");
			return true;
		}
		array_shift($args);
		$this->slainGc();
		if (count($args)) {
			$this->slain[$victim->getName()] = [ time(), implode(" ",$args) ];
		}
		$victim->setHealth(0);
		$c->sendMessage(TextFormat::RED.$victim->getName()." has been slain.".TextFormat::RESET);
		return true;
	}
	private function slainGc() {
		$lst = [];
		$now = time();
		foreach ($this->slain as $p=>$dat) {
			list($time,$msg) = $dat;
			if ($now - $time > 3) $lst[] = $p;
		}
		foreach ($lst as $p) {
			unset($this->slain[$p]);
		}
	}
	public function onPlayerDeath($name) {
		if (isset($this->slain[$name])) {
			list($time,$msg) = $this->slain[$name];
			unset($this->slain[$name]);
			return $msg;
		}
		return "";
	}

	private function cmdGmX(CommandSender $c,$mode) {
		if (!$this->inGame($c)) return true;
		if ($mode !== $c->getGamemode()) {
			$c->setGamemode($mode);
			if ($mode !== $c->getGamemode()) {
				$c->sendMessage("Unable to change gamemode");
			} else {
				$this->getServer()->broadcastMessage($c->getName()." changed gamemode to ". strtolower(Server::getGamemodeString($mode))." mode");
			}
		} else {
			$c->sendMessage("You are alredy in ".strtolower(Server::getGamemodeString($mode))." mode");
		}
		return true;
	}
	public function checkRpt($name) {
		$pl = $this->getServer()->getPlayer($name);
		if ($pl == null) return;
		if(!$pl->hasPermission("gb.cmd.rpt.read")) return;
		list($id,$rpt) = $this->rpt->getAll();
		if (count($rpt)) $pl->sendMessage(count($rpt)." reports on file");
	}
	private function cmdRpt(CommandSender $c,$args) {
		if (count($args) == 0) return false;
		list($id,$rpt) = $this->rpt->getAll();
		if ($args[0] == "read" && (count($args) == 1 ||
											(count($args) == 2 && is_numeric($args[1])))) {
			if (!$this->access($c,"gb.cmd.rpt.read")) return false;
			if (count($rpt) == 0) {
				$c->sendMessage(TextFormat::RED."No reports on file!");
				return true;
			}
			$pageNumber = $this->getPageNumber($args);
			$tab = [[ "ID","Date","Name","Reports: ".count($rpt) ]];
			foreach ($rpt as $i=>$ln) {
				list($tm,$name,$ms) = $ln;
				$tm = date("d-M H:i",$tm);
				$tab[] = [ $i,$tm,$name,$ms ];
			}
			$this->paginateTable($c,$pageNumber,$tab);
			return true;
		}
		if ($args[0] == "clear" && count($args) == 2) {
			if (!$this->access($c,"gb.cmd.rpt.read")) return false;
			if ($args[1] == "all") {
				$rpt = [];
			} else {
				$i = intval($args[1]);
				if (!isset($rpt[$i])) {
					$c->sendMessage("Unknown report #$i");
					return true;
				}
				unset($rpt[$i]);
				$c->sendMessage("Deleting report #$i");
			}
		} else {
			$rpt[++$id] = [time(),$c->getName(),implode(" ",$args)];

			$c->sendMessage("Report filed as #".$id);
			$ms = TextFormat::BLUE.
				 "Rpt[#$id from ".$c->getName()."] ".TextFormat::YELLOW.implode(" ",$args);
			$this->getLogger()->info($ms);
			foreach ($this->getServer()->getOnlinePlayers() as $pl) {
				if (!$pl->isOp()) continue;
				$pl->sendMessage($ms);
			}
		}
		$this->rpt->setAll([$id,$rpt]);
		$this->rpt->save();
		return true;
	}
	private function cmdOpMsg(CommandSender $c,$args) {
		if (count($args) == 0) return false;
		$ms = TextFormat::BLUE.
			 "OpMsg [".$c->getName()."] ".TextFormat::YELLOW.implode(" ",$args);
		$this->getLogger()->info($ms);
		$count = 0;
		foreach ($this->getServer()->getOnlinePlayers() as $pl) {
			if (!$pl->isOp()) continue;
			$pl->sendMessage($ms);
			++$count;
		}
		if (($c instanceof Player) && !$c->isOp()) {
			$pl->sendMessage("(ops:$count) ".implode(" ",$args));
		}
		return true;
	}
	private function cmdOps(CommandSender $c,$args) {
		$txt = [ "" ];
		$pageNumber = $this->getPageNumber($args);
		$cnt=0;
		foreach (array_keys($this->getServer()->getOps()->getAll()) as $opname) {
			$p = $this->getServer()->getPlayer($opname);
			if($p && ($p->isOnline() && (!($c instanceof Player) || $c->canSee($p)))){
				++$cnt;
				$txt[] = TextFormat::BLUE."$opname (online)".TextFormat::RESET;
			}else{
				$txt[] = TextFormat::RED."$opname".TextFormat::RESET;
			}
		}
		$txt[0] = "Server Ops (Online:$cnt)";
		return $this->paginateText($c,$pageNumber,$txt);
	}
	private function cmdSudo(CommandSender $c,$args) {
		if (count($args) < 2) {
			$c->sendMessage("Must specified a player and a command");
			return true;
		}
		$player = $this->getServer()->getPlayer($name = array_shift($args));
		if (!$player) {
			$c->sendMessage("Player $name not found");
			return true;
		}
		if ($args[0] == 'chat' || $args[0] == 'say') {
			array_shift($args);
			$chat = implode(" ",$args);
			$c->sendMessage("Sending message as $name");
			$this->getServer()->getPluginManager()->callEvent($ev = new PlayerChatEvent($player,$chat));
			if (!$ev->isCancelled()) {
				$this->getServer()->broadcastMessage(sprintf($ev->getFormat(),$ev->getPlayer()->getDisplayName(),$ev->getMessage()),$ev->getRecipients());
			}
		} else {
			$cmdline = implode(' ',$args);
			$c->sendMessage("Running command as $name");
			$this->getServer()->dispatchCommand($player,$cmdline);
		}
		return true;
	}
	private function cmdPlayers(CommandSender $c,$args) {
		$tab = [[ "Player","World","Pos","Health","Mode" ]];
		$cnt = 0;
		foreach ($this->getServer()->getOnlinePlayers() as $player) {
			if(!$player->isOnline() || (($c instanceof Player) && !$c->canSee($player))) continue;
			$pos = $player->getPosition();
			$j = count($tab);
			$mode = substr(ucfirst(strtolower(Server::getGamemodeString($player->getGamemode()))),0,4);
			$tab[]=[$player->getDisplayName(),$player->getLevel()->getName(),
					  $pos->getFloorX().",".$pos->getFloorY().",".$pos->getFloorZ(),
					  intval($player->getHealth()).'/'.intval($player->getMaxHealth()),
					  $mode];
			++$cnt;
		}
		if (!$cnt) {
			$c->sendMessage(TextFormat::RED."Nobody is on-line at the moment".TextFormat::RESET);
			return true;
		}
		$tab[0][0] = "Players:$cnt";
		$pageNumber = $this->getPageNumber($args);
		return $this->paginateTable($c,$pageNumber,$tab);
	}
	private function cmdTimings(CommandSender $c,$args) {
		$pageNumber = $this->getPageNumber($args);
		if (count($args)) {
			// Show the specified report
			$rpt = array_shift($args);
			if ($rpt == "clear") {
				$count = 0;
				foreach (glob($this->getServer()->getDataPath(). "timings/timings*.txt") as $f) {
					unlink($f); $count++;
				}
				$c->sendMessage("Deleted reports: $count");
				return true;
			}
			$rpt = preg_replace('/[^0-9]+/i','',$rpt);
			$f = $this->getServer()->getDataPath()."timings/timings$rpt.txt";
			if (!file_exists($f)) {
				$c->sendMessage("Report $rpt can not be found");
				return true;
			}
			$txt = file($f);
			array_unshift($txt,"Report: timings$rpt");
			return $this->paginateText($c,$pageNumber,$txt);
		}
		$txt = ["HDR"];
		// Inventorise the reports
		$count = 0;
		foreach (glob($this->getServer()->getDataPath(). "timings/timings*.txt") as $f) {
			++$count;
			$txt[] = "- ".basename($f);
		}
		if ($count == 0) {
			$sender->sendMessage(TextFormat::RED."No timmings report found");
			$sender->sendMessage("Enable timings by typing /timings on");
			$sender->sendMessage("Generate timings report by typing /timings report");
			return true;
		}
		$txt[0] = "Reports: $count";
		return $this->paginateText($c,$pageNumber,$txt);
	}
	private function cmdGet(CommandSender $c,$args) {
		if (!isset($args[0])) return false;
		if (!$this->inGame($c)) return true;
		if ($c->isCreative()) {
			$c->sendMessage("You are in creative mode");
			return true;
		}
		$item = Item::fromString($args[0]);
		if ($item->getId() == 0) {
			$c->sendMessage(TextFormat::RED."There is no item called ".$args[0]);
			return true;
		}
		if (isset($args[1])) {
			$item->setCount((int)$args[1]);
		} else {
			if (isset(self::$stacks[$item->getId()])) {
				$item->setCount(self::$stacks[$item->getId()]);
			} else {
				$item->setCount($item->getMaxStackSize());
			}
		}
		$c->getInventory()->addItem(clone $item);
		$this->getServer()->broadcastMessage($c->getName()." got ".
														 $item->getCount()." of ".
														 $this->itemName($item).
														 " (" . $item->getId() . ":" .
														 $item->getDamage() . ")");
		return true;
	}
	private function cmdSeeArmor(CommandSender $c,$args) {
		$pageNumber = $this->getPageNumber($args);
		if (count($args) != 1) {
			$c->sendMessage("You must specify a player's name");
			return true;
		}
		$target = $this->getServer()->getPlayer($args[0]);
		if($target == null) {
			$c->sendMessage($args[0]." can not be found.");
			return true;
		}
		$tab= [["Armor for",TextFormat::RED.$args[0]]];
		foreach ([0=>"head",1=>"body",2=>"legs",3=>"boots"] as $slot=>$attr) {
			$item = $target->getInventory()->getArmorItem($slot);
			if ($item->getID() == 0) continue;
			$tab[]=[$attr.TextFormat::BLUE,
					  $this->itemName($item)." (" .$item->getId().":".$item->getDamage().")"];
		}
		return $this->paginateTable($c,$pageNumber,$tab);
	}
	private function cmdClearInv(CommandSender $c,$args) {
		if (count($args) != 1) {
			$c->sendMessage("You must specify a player's name");
			return true;
		}
		$target = $this->getServer()->getPlayer($args[0]);
		if($target == null) {
			$c->sendMessage($args[0]." can not be found.");
			return true;
		}
		$target->getInventory()->clearAll();
		$target->sendMessage("Your inventory has been cleared by ".$c->getName());
		$c->sendMessage($target->getName()."'s inventory cleared");
		return true;
	}
	private function cmdSeeInv(CommandSender $c,$args) {
		$pageNumber = $this->getPageNumber($args);
		if (count($args) != 1) {
			$c->sendMessage("You must specify a player's name");
			return true;
		}
		$target = $this->getServer()->getPlayer($args[0]);
		if($target == null) {
			$c->sendMessage($args[0]." can not be found.");
			return true;
		}
		$tab= [[$args[0],"Count","Damage"]];
		$max = $target->getInventory()->getSize();
		foreach ($target->getInventory()->getContents() as $slot => &$item) {
			if ($slot >= $max) continue;
			$tab[] = [$this->itemName($item)." (".$item->getId().")",
						 $item->getCount(),$item->getDamage() ];
		}
		if (count($tab) == 1) {
			$c->sendMessage("The inventory for $args[0] is EMPTY");
			return true;
		}
		return $this->paginateTable($c,$pageNumber,$tab);
	}
	private function cmdTileList(CommandSender $c,$level,$pageNumber) {
		$tab = [];
		$tab[] = [$level->getName(),"Name","Position"];
		foreach ($level->getTiles() as $t) {
			$id = $t->getId();
			$pos = implode(",",[floor($t->getX()),floor($t->getY()),floor($t->getZ())]);
			$name = basename(strtr(get_class($t),"\\","/"));
			$tab[] = [ $id,$name,$pos ];
		}
		return $this->paginateTable($c,$pageNumber,$tab);
	}
	private function cmdEtList(CommandSender $c,$level,$pageNumber) {
		$tab = [];
		$tab[] = [$level->getName(),"Name","Position","Health"];
		foreach ($level->getEntities() as $e) {
			if ($e instanceof Player) continue;
			$id = $e->getId();
			$pos = implode(",",[floor($e->getX()),floor($e->getY()),floor($e->getZ())]);
			if ($e instanceof Living) {
				$name = $e->getName();
			} elseif ($e instanceof \pocketmine\entity\Item) {
				$name = "Item:".$this->itemName($e->getItem());
			} else {
				$name = basename(strtr(get_class($e),"\\","/"));
			}
			$tab[] = [ $id,$name,$pos,$e->getHealth() ];
		}
		return $this->paginateTable($c,$pageNumber,$tab);
	}
	private function cmdEtInfo(CommandSender $c,$level,$args,$pageNumber) {
		$cnt = 0;
		if (count($args) == 0) return false;
		$txt = [];
		if (count($args) > 1) {
			$txt[] = "";
		}
		foreach ($args as $i) {
			if (strtolower(substr($i,0,1)) == "t") {
				$i = substr($i,1);
				if (!is_numeric($i)) {
					$c->sendMessage("Invalid Tile id $i");
					continue;
				}
				$tile = $level->getTileById(intval($i));
				if ($tile == null) {
					$c->sendMessage("Tile $i not found");
					continue;
				}
				++$cnt;
				$txt[] = "Tile: $i";
				foreach ($this->dumpNbt($tile->namedtag) as $ln) {
					$txt[] = $ln;
				}
			} else {
				if (strtolower(substr($i,0,1)) == "e") {
					$i = substr($i,1);
				}
				if (!is_numeric($i)) {
					$c->sendMessage("Invalid Entity id $i");
					continue;
				}
				$et = $level->getEntity(intval($i));
				if ($et == null) {
					$c->sendMessage("Entity $i not found");
					continue;
				}
				++$cnt;
				$txt[] = "Entity: $i";
				foreach ($this->dumpNbt($et->namedtag) as $ln) {
					$txt[] = $ln;
				}
			}
		}
		if (count($args) > 1) {
			$txt[0] = "$cnt Entities";
		}
		return $this->paginateText($c,$pageNumber,$txt);
	}
	private function cmdEtRm(CommandSender $c,$level,$args) {
		$cnt = 0;
		if (count($args) == 0) return false;
		foreach ($args as $i) {
			if (strtolower(substr($i,0,1)) == "e") {
				$i = substr($i,1);
			}
			if (!is_numeric($i)) {
				$c->sendMessage("Invalid Entity id $i");
				continue;
			}
			$et = $level->getEntity(intval($i));
			if ($et == null) {
				$c->sendMessage("Entity $i not found");
				continue;
			}
			++$cnt;
			$level->removeEntity($et);
		}
		if ($cnt) {
			$c->sendMessage("Removed entities: ".$cnt);
		}
		return true;
	}
	private function cmdEtSign(CommandSender $c,$level,$sub,$args) {
		if (count($args) < 1) return false;
		$i = array_shift($args);
		if (strtolower(substr($i,0,1)) != "t") {
			$c->sendMessage("Only applies to tile ids");
			return false;
		}
		$i = substr($i,1);
		if (!is_numeric($i)) {
			$c->sendMessage("Invalid Tile id $i");
			return false;
		}
		$tile = $level->getTileById(intval($i));
		if ($tile == null) {
			$c->sendMessage("Tile $i not found");
			return false;
		}
		if (!($tile instanceof Sign)) {
			$c->sendMessage("Tile $i is not a sign");
			return false;
		}
		$sign = $tile->getText();
		$txt = implode(" ",$args);
		$sub = intval(substr($sub,-1)) - 1;
		if ($sign[$sub] == $txt) {
			$c->sendMessage("Text unchanged");
			return true;
		}
		$sign[$sub] = $txt;
		$tile->setText($sign[0],$sign[1],$sign[2],$sign[3]);
		$c->sendMessage("Changed to \"$txt\"");
		return true;
	}
	private function cmdEntities(CommandSender $c,$args) {
		$pageNumber = $this->getPageNumber($args);
		$level = null;
		if (isset($args[0])) {
			$level = $this->getServer()->getLevelByName($args[0]);
			if ($level) array_shift($args);
		}
		if (!$level) {
			if (!$this->inGame($c)) return false;
			$level = $c->getLevel();
		}
		// list entities
		// remove entity
		// remove *ALL* entities
		if (count($args)) {
			$sub = strtolower(array_shift($args));
			switch ($sub) {
				case "tiles":
				case "tile":
					return $this->cmdTileList($c,$level,$pageNumber);
				case "info":
				case "nbt":
					return $this->cmdEtInfo($c,$level,$args,$pageNumber);
				case "rm":
					return $this->cmdEtRm($c,$level,$args);
				case "sign1":
				case "sign2":
				case "sign3":
				case "sign4":
					return $this->cmdEtSign($c,$level,$sub,$args);
			}
			return false;
		}
		return $this->cmdEtList($c,$level,$pageNumber);
	}
	//////////////////////////////////////////////////////////////////////
	// Event based stuff...
	//////////////////////////////////////////////////////////////////////
	public function cleanup($player) {
		// Called when a player disconnects, to release state related resources...
		if (isset($this->shield[$player])) unset($this->shield[$player]);
		if (isset($this->state[$player])) unset($this->state[$player]);
	}

	public function canCompassTp($player) {
		if (!array_key_exists("compasstp",$this->modules["listener"])) return false;
		$pl = $this->getServer()->getPlayer($player);
		if ($pl == null) return false;
		return $pl->hasPermission("gb.compasstp.allow");
	}

	private function spawnArmor($pl) {
		if ($pl->isCreative()) return;

		foreach ([0=>"head",1=>"body",2=>"legs",3=>"boots"] as $slot=>$attr) {
			if ($pl->getInventory()->getArmorItem($slot)->getID() != 0) continue;
			if (!isset($this->config["spawn"]["armor"][$attr])) continue;
			$type = strtolower($this->config["spawn"]["armor"][$attr]);
			if ($type == "leather") {
				$type = 298;
			} elseif ($type == "chainmail") {
				$type = 302;
			} elseif ($type == "iron") {
				$type = 306;
			} elseif ($type == "gold") {
				$type = 314;
			} elseif ($type == "diamond") {
				$type = 310;
			} else {
				continue;
			}
			//echo "slot=$slot($attr) type=$type ".($type+$slot)."\n";
			$pl->getInventory()->setArmorItem($slot,new Item($type+$slot,0,1));
		}
	}
	private function spawnItems($pl) {
		if ($pl->isCreative()) return;

		// Figure out if the inventory is empty...
		$cnt = 0;
		$max = $pl->getInventory()->getSize();
		foreach ($pl->getInventory()->getContents() as $slot => &$item) {
			if ($slot < $max) ++$cnt;
		}
		if ($cnt) return;

		// This player has nothing... let's give them some to get started...
		foreach ($this->config["spawn"]["items"] as $i) {
			$r = explode(":",$i);
			if (count($r) != 3) continue;
			$item = new Item($r[0],$r[1],$r[2]);
			$pl->getInventory()->addItem($item);
		}
	}

	public function respawnPlayer($player) {
		$pl = $this->getServer()->getPlayer($player);
		if ($pl == null) return;
		if (!isset($this->config["spawn"])) return;
		if (isset($this->config["spawn"]["items"])
			 && array_key_exists("spawnitems",$this->modules["listener"])
			 && $pl->hasPermission("gb.spawnitems.receive")) {
			$this->spawnItems($pl);
		}
		if (isset($this->config["spawn"]["armor"])
			 && array_key_exists("spawnarmor",$this->modules["listener"])
			 && $pl->hasPermission("gb.spawnarmor.receive")) {
			$this->spawnArmor($pl);
		}
	}
}
