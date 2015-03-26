<?php
namespace aliuly\manyworlds;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandExecutor;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

use pocketmine\utils\Config;

use pocketmine\item\Item;


class Main extends PluginBase implements CommandExecutor {
  protected $canUnload = false;
  private $listener;
  private $tpManager;

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
	switch ($scmd) {
	case "tp":
	  if (!$this->access($sender,"mw.cmd.tp")) return false;
	  return $this->mwTpCmd($sender,$args);
	  break;
	case "ls":
	case "list":
	  if (!$this->access($sender,"mw.cmd.ls")) return false;
	  return $this->mwLsCmd($sender,$args);
	case "create":
	  if (!$this->access($sender,"mw.cmd.world.create")) return false;
	  return $this->mwWorldCreateCmd($sender,$args);
	  break;
	case "ld":
	case "load":
	  if (!$this->access($sender,"mw.cmd.world.load")) return false;
	  return $this->mwWorldLoadCmd($sender,$args);
	case "unload":
	  if (!$this->access($sender,"mw.cmd.world.load")) return false;
	  return $this->mwWorldUnloadCmd($sender,$args);
	case "motd":
	  if (!$this->access($sender,"mw.cmd.world.motd")) return false;
	  return $this->mwWorldMotdCmd($sender,$args);
	case "help":
	  return $this->mwHelpCmd($sender,$args);
	default:
	  $sender->sendMessage(TextFormat::RED."Unknown sub command: ".
			       TextFormat::RESET.$args[0]);
	  $sender->sendMessage("Use: ".TextFormat::GREEN." /mw help".
			       TextFormat::RESET);
	}
	return true;
      } else {
	$sender->sendMessage("Must specify sub command");
	$sender->sendMessage("Use: ".TextFormat::GREEN." /mw help".
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
      $page = $this->getPageNumber($args);
      $lines = explode("\n",file_get_contents($f));
      $this->paginateText($sender,$page,$lines);
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
	$sender->sendMessage("[MW] ".$player." is offline!");
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
      $this->teleport($player,$level);
      $sender->sendMessage("[MW] " . $player . " has been teleported to " . $level . "!");
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
    $this->teleport($sender,$level);
    $this->getServer()->broadcastMessage("[MW] ".$sender->getName()." teleported to $level");
    return true;
  }
  private function mwLsCmd(CommandSender $sender,$args) {
    $pageNumber = $this->getPageNumber($args);
    if (isset($args[0])) {
      if(!$this->mwAutoLoad($sender,$args[0])) {
	$sender->sendMessage("[MW] " . $args[0] . " is not loaded!");
	return true;
      }
      $txt = $this->mwWordDetails($sender,$args[0]);
    } else {
      $txt = $this->mwWordList($sender);
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
    }
    if(isset($args[2])) $options = ["preset" => $args[2] ];
    $this->getServer()->broadcastMessage("[MW] Creating level " . $level . "... (Expect Lag)");
    $this->getServer()->generateLevel($level, $seed, $generator, $options);
    $this->getServer()->loadLevel($level);
    return true;
  }
  private function mwWorldLoadCmd(CommandSender $sender,$args) {
    if (!isset($args[0]))
      return $this->mwHelpCmd($sender,["load"]);
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
	     "tp" => "<level> [player]",
	     "ls" => "[level]",
	     "create" => "<level> [seed [flat|normal [preset]]]",
	     "ld" => "<level>|--all",
	     "unload" => "<level>",
	     "motd" => "<level> [line [text]]" ];
    $aliases = [
		"list" => "ls",
		"load" => "ld",
		]

    if (count($args)) {
      foreach ($args as $c) {
	if (isset($cmds[$c]))
	  $this->sendMessage(TextFormat::RED."Usage: /mw $c $cmds[$c]"
			     .TextFormat::RESET);
      }
      return true;
    }
    $txt = ["ManyWorlds sub-commands"];
    foreach ($cmds as $a => $b) {
      $ln = "- ".TextFormat::RED."/mw ".$a;
      foreach ($aliases as $i => $j) {
	if ($j == $a) $ln .= "|$i";
      }
      $ln .= " ".$b;
    }
    $txt[] = $ln;
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
  //
  // Public API
  //
  public function teleport($player,$level,$spawn=null) {
    $this->tpManager->teleport($player,$level,$spawn);
  }
  //
  // Basic call backs
  //
  public function showMotd($args) {
    list($pname,$level) = $args;
    $player = $this->getServer()->getPlayer($name);
    if (!$player) return;
    $f = $this->getServer()->getDataPath(). "worlds/".$level."/motd.txt";
    if (!file_exists($f)) return;
    $player->sendMessage(file_get_contents($f));
  }



}
