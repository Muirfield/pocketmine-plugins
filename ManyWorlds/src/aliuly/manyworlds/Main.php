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

use pocketmine\utils\Config;


class Main extends PluginBase implements CommandExecutor {
  const MIN_BORDER = 128;

  protected $canUnload = false;
  private $listener;
  private $tpManager;
  private $cfg;
  private $noborders;
  static private $aliases = [
			     "list" => "ls",
			     "load" => "ld",
			     "no-border" => "noborder",
			     "limit" => "limits",
			     "max" => "limits"
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
  public function onDisable() {
    $this->getLogger()->info(TextFormat::RED."* Disabled!".TextFormat::RESET);
  }
  public function onEnable(){
    $this->getLogger()->info(TextFormat::GREEN."* Enabled!".TextFormat::RESET);
    $this->listener = new MwListener($this);
    $this->tpManager = new TeleportManager($this);
    @mkdir($this->getDataFolder());
    $defaults = [
		 "settings" => [
				"broadcast-tp" => true,
				],
		 ];


    $this->cfg = (new Config($this->getDataFolder()."config.yml",
		       Config::YAML,$defaults))->getAll();
    $this->borders = [];
    if (!isset($this->cfg["protect"])) $this->cfg["protect"] = [];
    if (!isset($this->cfg["border"])) $this->cfg["border"] = [];
  }
  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    switch($cmd->getName()) {
    case "motd":
      if (!$this->access($sender,"mw.motd")) return false;
      if (!$this->inGame($sender)) return true;
      $level = $sender->getLevel()->getName();
      return $this->motdCmd($sender,$level);
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
	case "add":
	  if (!$this->access($sender,"mw.cmd.world.protect")) return false;
	  return $this->mwWorldProtectAdd($sender,$args);
	case "rm":
	  if (!$this->access($sender,"mw.cmd.world.protect")) return false;
	  return $this->mwWorldProtectRm($sender,$args);
	case "unprotect":
	case "open":
	case "lock":
	case "protect":
	case "pvp":
	case "peace":
	  if (!$this->access($sender,"mw.cmd.world.protect")) return false;
	  return $this->mwWorldProtectMode($sender,$scmd,$args);
	case "ld":
	  if (!$this->access($sender,"mw.cmd.world.load")) return false;
	  return $this->mwWorldLoadCmd($sender,$args);
	case "unload":
	  if (!$this->access($sender,"mw.cmd.world.load")) return false;
	  return $this->mwWorldUnloadCmd($sender,$args);
	case "motd":
	  if (!$this->access($sender,"mw.cmd.world.motd")) return false;
	  return $this->mwWorldMotdCmd($sender,$args);
	case "border":
	  if (!$this->access($sender,"mw.cmd.world.border")) return false;
	  return $this->mwWorldBorderCmd($sender,$args);
	case "noborder":
	  if (!$this->access($sender,"mw.cmd.world.border")) return false;
	  return $this->mwWorldNoBorderCmd($sender,$args);
	case "border-off":
	  if (!$this->access($sender,"mw.cmd.world.border")) return false;
	  return $this->mwWorldToggleBorderCmd($sender,false);
	case "border-on":
	  if (!$this->access($sender,"mw.cmd.world.border")) return false;
	  return $this->mwWorldToggleBorderCmd($sender,true);
	case "limits":
	  if (!$this->access($sender,"mw.cmd.world.limit")) return false;
	  return $this->mwWorldLimitCmd($sender,$args);
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
  private function motdCmd(CommandSender $sender,$level) {
    $f = $this->getServer()->getDataPath(). "worlds/".$level."/motd.txt";
    if (file_exists($f)) {
      $sender->sendMessage(file_get_contents($f));
    } else {
      $sender->sendMessage(TextFormat::RED."Sorry, no \"motd.txt\"".TextFormat::RESET);
    }
    return true;
  }
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
	  $this->getServer()->broadcastMessage("[MW] ".$sender->getName()." was teleported to $level");
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
  private function mwWorldMotdCmd(CommandSender $sender,$args) {
    if (!isset($args[0]))
      return $this->mwHelpCmd($sender,["motd"]);

    $level = array_shift($args);
    if (!$this->getServer()->isLevelGenerated($level)) {
      $sender->sendMessage("[MW] $level does not exist");
      return true;
    }
    if (!count($args)) {
      // Just show it...
      return $this->motdCmd($sender,$level);
    }
    // Edit the MOTD text
    $lnum = array_shift($args);
    if (!is_numeric($lnum)) {
      $sender->sendMessage("[MW] please provide a line number");
      return true;
    }
    $ntxt = implode(" ",$args);
    return $this->mwWorldEditMotd($sender,$level,$lnum,$ntxt);
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
	     "ld" => ["<level>|--all","Load a world"],
	     "unload" => ["<level>","Attempt to unload a world"],
	     "motd" => ["<level> [line [text]]",
			"View/Edit the world's motd file"],
	     "add" => ["[level] <user>","Add <user> to authorized list"],
	     "rm" => ["[level] <user>","Remove <user> from authorized list"],
	     "open" => ["[level]",
			"Open [level].  Default unprotected world"],
	     "lock"=>["[level]",
			"Locked [level].  Nobody (including op) can do anything"],
	     "protect"=>["[level]",
			   "Only people on authorized list can build, no pvp"],
	     "pvp"=>["[level]",
		     "pvp is allowed.  Placing, destroying blocks is NOT allowed"],
	     "peace"=>["[level]",
		       "pvp is NOT allowed.  Placing, destroying blocks is allowed"],
	     "unprotect"=>["[level]",
			   "Remove all world protection (including auth list)"],
	     "border" => ["[level] <x1 z1 x2 z2>",
			  "Creates a border in [level] defined by x1,z1 to x2,z2"],
	     "noborder" => ["[level]","Removes a border from level"],
	     "border-off" => ["","temporarily disable borders for you only"],
	     "border-on" => ["", "Reverses the effects of border-off"],
	     "limits" => ["[level] [value]","Limits the number of players in a world to [value] use 0 or -1 to remove limits"],
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
      $ln = "- ".TextFormat::RED."/mw ".$a;
      foreach (self::$aliases as $i => $j) {
	if ($j == $a) $ln .= "|$i";
      }
      $ln .= " ".$b[0];
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
    $default = $this->getServer()->getDefaultLevel()->getName();

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
      if (count($attrs)) $ln .= " (".implode(",",$attrs).")";
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
    $txt[] = "Provider: ". $provider::getProviderName();
    $txt[] = "Path: ".$provider->getPath();
    $txt[] = "Name: ".$provider->getName();
    $txt[] = "Seed: ".$provider->getSeed();
    $txt[] = "Generator: ".$provider->getGenerator();
    $txt[] = "Generator Options: ".print_r($provider->getGeneratorOptions(),true);
    $spawn = $provider->getSpawn();
    $txt[] = "Spawn: ".$spawn->getX().",".$spawn->getY().",".$spawn->getZ();
    $f = $this->getServer()->getDataPath(). "worlds/".$level."/motd.txt";
    if (file_exists($f)) {
      $txt[] = "MOTD:";
      foreach (file($f) as $ln) {
	$ln = preg_replace('/\s+$/','',$ln);
	$txt[] = "  ".TextFormat::BLUE.$ln.TextFormat::RESET;
      }
    }
    if (isset($this->cfg["protect"][$level])) {
      $txt[] = "World-Protect: ".
	TextFormat::GREEN.$this->cfg["protect"][$level]["mode"].
	TextFormat::RESET;
      $txt[] = "- Auth: ".
	TextFormat::GREEN.implode(",",$this->cfg["protect"][$level]["auth"]).
	TextFormat::RESET;
    }
    if (isset($this->cfg["border"][$level])) {
      $txt[] = "World-Border: ".
	TextFormat::GREEN.implode(",",$this->cfg["border"][$level]).
	TextFormat::RESET;
    }
    if (isset($this->cfg["limits"][$level])) {
      $txt[] = "Max Players: ".
	TextFormat::GREEN.$this->cfg["limits"][$level].TextFormat::RESET;
    }
    return $txt;
  }
  private function mwWorldEditMotd(CommandSender $sender,$level,$line,$lntxt) {
    $f = $this->getServer()->getDataPath(). "worlds/".$level."/motd.txt";
    if ($line < 1 || $line > 5) {
      $sender->sendMessage("[MW] Line $line must be between 1 and 5");
      return true;
    }
    --$line;
    if (file_exists($f)) {
      $txt = file($f);
    } else {
      $txt = [ "\n","\n","\n","\n","\n" ];
    }
    $txt[$line] = $lntxt;
    file_put_contents($f,preg_replace('/\s+$/','',implode("",$txt))."\n");
    return true;
  }
  // World-Protect functionality
  private function mwWorldProtectDefaults(CommandSender $sender,$cmd,$args,
					  &$user,&$level) {
    if (count($args) == 1) {
      if (!$this->inGame($sender)) return false;
      $level = $sender->getLevel()->getName();
      $user = $args[0];
    } elseif (count($args) == 2) {
      $level = $args[0];
      $user = $args[1];
      if(!$this->getServer()->isLevelGenerated($level)) {
	$sender->sendMessage("[MW] $level does not exist");
	return false;
      }
    } else {
      $this->mwHelpCmd($sender,[$cmd]);
      return false;
    }
    return true;
  }
  private function mwWorldProtectAdmin(CommandSender $sender,$level,$new=false) {
    /* First check access ... */
    if ($new && !$this->inGame($sender,false)) return true;

    if (!isset($this->cfg["protect"][$level])) {
      $sender->sendMessage("[MW] $level not using World Protect");
      if ($this->inGame($sender,false))
	$sender->sendMessage("[MW] Must be configured from the console");
      return false;
    }
    if (!$this->inGame($sender,false)) return true;
    if (!isset($this->cfg["protect"][$level]["auth"][$sender->getName()])) {
      $sender->sendMessage("[MW] You are not allowed to do this");
      return false;
    }
    return true;
  }
  private function saveCfg() {
    $yaml = new Config($this->getDataFolder()."config.yml",Config::YAML,[]);
    $yaml->setAll($this->cfg);
    $yaml->save();
  }

  private function mwWorldProtectAdd(CommandSender $sender,$args){
    if (!$this->mwWorldProtectDefaults($sender,"add",$args,$user,$level))
      return true;
    if (!$this->mwWorldProtectAdmin($sender,$level)) return true;
    if (isset($this->cfg["protect"][$level]["auth"][$user])) {
      $sender->sendMessage("[MW] $user is already in $level authorized list");
      return true;
    }
    $player = $this->getServer()->getPlayer($user);
    if (!$player) {
      $sender->sendMessage("[MW] $user can not be found.  Are they off-line?");
      return true;
    }
    if (!$player->isOnline()) {
	$sender->sendMessage("[MW] $user is offline!");
	return true;
    }
    $this->cfg["protect"][$level]["auth"][$user] = $user;
    $this->saveCfg();
    $sender->sendMessage("[MW] $user added to $level's authorized list");
    $player->sendMessage("[MW] You have been added $level's authorized list");
    return true;
  }
  private function mwWorldProtectRm(CommandSender $sender,$args) {
    if (!$this->mwWorldProtectDefaults($sender,"add",$args,$user,$level))
      return true;
    if (!$this->mwWorldProtectAdmin($sender,$level)) return true;
    if (!isset($this->cfg["protect"][$level]["auth"][$user])) {
      $sender->sendMessage("[MW] $user is not in $level authorized list");
      return true;
    }
    $player = $this->getServer()->getPlayer($user);
    if ($player) {
      if (!$player->isOnline()) $player = null;
    }
    unset($this->cfg["protect"][$level]["auth"][$user]);
    $this->saveCfg();
    $sender->sendMessage("[MW] $user removed from $level's authorized list");
    $player->sendMessage("[MW] You have been removed from $level's authorized list");
    return true;
  }
  private function mwWorldProtectMode(CommandSender $sender,$mode,$args) {
    if (count($args) == 0) {
      if (!$this->inGame($sender)) return true;
      $level = $sender->getLevel()->getName();
    } else {
      $level = $args[0];
      if(!$this->getServer()->isLevelGenerated($level)) {
	$sender->sendMessage("[MW] $level does not exist");
	return true;
      }
    }
    if (!$this->mwWorldProtectAdmin($sender,$level,true)) return true;
    if ($mode == "unprotect") {
      // This is a special case...
      if (!isset($this->cfg["protect"][$level])) {
	$sender->sendMessage("[MW] $level is not protected");
	return true;
      }
      unset($this->cfg["protect"][$level]);
      $this->saveCfg();
      $sender->sendMessage("[MW] $level is unprotected");
      return true;
    }
    if (!isset($this->cfg["protect"][$level])) {
      $this->cfg["protect"][$level] = [ "mode"=>"N/A","auth"=>[] ];
    }
    if ($this->cfg["protect"][$level]["mode"] == $mode) {
      $sender->sendMessage("[MW] $level is already in $mode status");
      return true;
    }
    $this->cfg["protect"][$level]["mode"] = $mode;
    $this->saveCfg();
    $sender->sendMessage("[MW] $level changed to $mode");
    return true;
  }
  // World limits
  private function mwWorldLimitCmd(CommandSender $sender,$args) {
    if (count($args) == 1) {
      if (!$this->inGame($sender)) return true;
      $level = $sender->getLevel()->getName();
    } elseif (count($args) == 2) {
      $level = array_shift($args);
      if(!$this->getServer()->isLevelGenerated($level)) {
	$sender->sendMessage("[MW] $level does not exist");
	return true;
      }
    } else {
      $this->mwHelpCmd($sender,["limits"]);
      return false;
    }
    $max = intval(array_shift($args));
    if ($max <= 0) {
      // Remove limits
      if (!isset($this->cfg["limits"][$level])) {
	$sender->sendMessage("[MW] $level is already unlimited");
	return true;
      }
      unset($this->cfg["limits"][$level]);
      $this->saveCfg();
      $sender->sendMessage("[MW] Removed player limits on $level");
      return true;
    }
    if (isset($this->cfg["limits"][$level])) {
      if ($this->cfg["limits"][$level] = $max) {
	$sender->sendMessage("[MW] Player limits for $level already at $max");
	return true;
      }
    }
    $this->cfg["limits"][$level] = $max;
    $this->saveCfg();
    $sender->sendMessage("[MW] Set limits for $level to $max");
    return true;
  }

  // World borders
  private function mwWorldBorderCmd(CommandSender $sender,$args) {
    if (count($args) == 4) {
      if (!$this->inGame($sender)) return true;
      $level = $sender->getLevel()->getName();
    } elseif (count($args) == 5) {
      $level = array_shift($args);
      if(!$this->getServer()->isLevelGenerated($level)) {
	$sender->sendMessage("[MW] $level does not exist");
	return true;
      }
    } else {
      $this->mwHelpCmd($sender,["border"]);
      return false;
    }
    list($x1,$z1,$x2,$z2) = $args;
    if ($x1 > $x2) list($x1,$x2) = [$x2,$x1];
    if ($z1 > $z2) list($z1,$z2) = [$z2,$z1];
    if ($x2 - $x1 < self::MIN_BORDER || $z2 - $z1 < self::MIN_BORDER) {
      $sender->sendMessage("[MW] Invalid border region");
      return true;
    }
    $this->cfg["border"][$level] = [$x1,$z1,$x2,$z2];
    $this->saveCfg();
    $sender->sendMessage("[MW] $level border region: $x1,$z1,$x2,$z2");
    return true;
  }
  private function mwWorldNoBorderCmd(CommandSender $sender,$args) {
    if (count($args) == 0) {
      if (!$this->inGame($sender)) return true;
      $level = $sender->getLevel()->getName();
    } elseif (count($args) == 1) {
      $level = array_shift($args);
      if(!$this->getServer()->isLevelGenerated($level)) {
	$sender->sendMessage("[MW] $level does not exist");
	return true;
      }
    } else {
      $this->mwHelpCmd($sender,["noborder"]);
      return false;
    }
    if (isset($this->cfg["border"][$level])) {
      unset($this->cfg["border"][$level]);
      $this->saveCfg();
      $sender->sendMessage("[MW] Removed border controls for $level");
      return true;
    }
    $sender->sendMessage("[MW] $level has no borders");
    return true;
  }
  private function mwWorldToggleBorderCmd(CommandSender $sender,$mode) {
    if (!$this->inGame($sender)) return true;
    $name = $sender->getName();
    if ($mode) {
      // Border ON
      if (isset($this->noborders[$name])) {
	unset($this->noborders[$name]);
	$sender->sendMessage("[MW] borders are enforced");
      } else {
	$sender->sendMessage("[MW] borders are already being enforced for you");
      }
      return true;
    }
    // Border OFF
    if (!isset($this->noborders[$name])) {
      $this->noborders[$name] = $name;
      $sender->sendMessage("[MW] borders enforcing is being turned off for you");
    } else {
      $sender->sendMessage("[MW] borders are not being enforced for you");
    }
    return true;
  }
  //
  // Public API
  //
  public function teleport($player,$level,$spawn=null) {
    /*
     * Enforce world limits
     */
    if (isset($this->cfg["limits"][$level])) {
      if (!$this->getServer()->isLevelLoaded($level)) return false;
      $np = count($this->getServer()->getLevelByName($level)->getPlayers());
      if ($np >= $this->cfg["limits"][$level]) {
	$player->sendMessage("Can not teleport to $level, its FULL\n");
	return false;
      }
    }
    return $this->tpManager->teleport($player,$level,$spawn);
  }
  //
  // Basic call backs
  //
  public function showMotd($pname,$level) {
    $player = $this->getServer()->getPlayer($pname);
    if (!$player) return;
    $f = $this->getServer()->getDataPath(). "worlds/".$level."/motd.txt";
    if (!file_exists($f)) return;
    $player->sendMessage(file_get_contents($f));
  }
  // Callbacks
  public function checkPvP($level) {
    if (!isset($this->cfg["protect"][$level])) return true;
    switch ($this->cfg["protect"][$level]["mode"]) {
    case "protect": return false;
    case "lock": return false;
    case "peace": return false;
    }
    return true;
  }
  public function checkBlockPlaceBreak($pname,$level) {
    if (!isset($this->cfg["protect"][$level])) return true;
    switch ($this->cfg["protect"][$level]["mode"]) {
    case "lock": return false;
    case "protect":
      if (!count($this->cfg["protect"][$level]["auth"])) {
	$sender = $this->getServer()->getPlayer();
	if (!$sender) return false;
	if ($sender->hasPermission("mw.world.protect.basic")) return true;
	return false;
      }
      if (!isset($this->cfg["protect"][$level]["auth"][$pname])) return false;
      break;
    case "pvp": return false;
    }
    return true;

  }
  public function checkMove($name,$level,$x,$z) {
    if (isset($this->noborders[$name])) return true;
    if (!isset($this->cfg["border"][$level])) return true;
    list($x1,$z1,$x2,$z2) = $this->cfg["border"][$level];
    if ($x1 < $x && $x < $x2 && $z1 < $z && $z < $z2) return true;
    return false;
  }
}
