<?php
namespace ManyWorlds;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\permission\Permission;
use pocketmine\level\Level;
use pocketmine\level\generator\Generator;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\Command;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat;


class Main extends Plugin implements CommandExecutor {
  public function onLoad() {
    $this->getLogger()->info("ManyWorlds Loaded!");
  }
  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    switch($cmd->getName()) {
    case "mw":
      if(isset($args[0])) {
	$scmd = strtolower($args[0]);
	switch ($scmd) {
	case "tp":
	  return $this->mwTeleportCommand($sender,$args);
	case "create":
	  return $this->mwWorldCreateCommand($sender,$args);
	case "unload":
	  return $this->mwWorldUnloadCommand($sender,$args);
	case "load":
	case "ld":
	  return $this->mwWorldLoadCommand($sender,$args);
	case "ls":
	case "list":
	  return $this->mwWorldListCommand($sender,$args);
	default:
	  $sender->sendMessage("Unknown sub command: $args[0]");
	  return true;
	}
      } else {
	$sender->sendMessage("Must specify sub command");
	return true;
      }
      break;
    }
    return false;
  }
  private function mwAutoLoad(CommandSender $c,$level) {
    if (!$this->getServer()->isLevelLoaded($level)) {
      if(!$this->checkPermission($c, "mw.cmd.world.load")) {
	$c->sendMessage("[MW] $level is not loaded.");
	return false;
      }
      if(!$this->getServer()->isLevelGenerated($level)) {
	$c->sendMessage("[MW] No level with the name $level exists!");
	return false;
      }
      $c->sendMessage("[MW] Loading $level...");
      $this->getServer()->loadLevel($level);
    }
    return true;
  }

  private function mwTeleportCommand(CommandSender $sender,array $args) {
    if (!isset($args[1])) {
      $sender->sendMessage("[MW] must specify level");
      return true;
    }

    $level = $args[1];

    if (isset($args[2])) {
      // Teleport others
      $player = $this->getServer()->getPlayer($args[2]);
      if (!$player) {
	$sender->sendMessage("[MW] Player ".$args[2]." can not be found");
	return true;
      }
      if($this->checkPermission($sender, "universe.cmd.tp.others")) {
	if ($player->isOnline()) {
	  if($player->getLevel() == $this->getServer()->getLevelByName($level)) {
	    $sender->sendMessage("[MW] " . $player . " is already in " . $level . "!");
	  } else {
	    if ($this->mwAutoLoad($sender,$level)) {
	      $player->sendMessage("[MW] Teleporting you to " . $level . " at\n" . $sender->getName() . "'s request...");
	      $this->teleport($player,$level);
	      $sender->sendMessage("[MW] " . $player . " has been teleported to " . $level . "!");
	    } else {
	      $sender->sendMessage("[MW] Unable to teleport " . $player . " as\nlevel " . $level . " is not loaded!");
	    }
	  }
	} else {
	  $sender->sendMessage("[MW] ".$player." is offline!");
	}
      } else {
	return $this->permissionFail($sender);
      }
    } else {
      // Teleport self
      if ($sender instanceof Player) {
	if ($this->checkPermission($sender,"mw.cmd.tp")) {
	  if(!($sender->getLevel() == $this->getServer()->getLevelByName($level))) {
	    if($this->mwAutoLoad($sender,$level)) {
	      $sender->sendMessage("[MW] Teleporting you to level " . $level . "...");
	      $this->teleport($sender,$level);
	      $this->getServer()->broadcastMessage("[MW] ".$sender->getName()." teleported to $level");
	    } else {
	      $sender->sendMessage("[MW] Unable to teleport you to " . $level . " as it\nis not loaded!");
	    }
	  } else {
	    $sender->sendMessage("[MW] You are already in " . $level . "!");
	  }
	} else {
	  return $this->permissionFail($sender);
	}
      } else {
	$sender->sendMessage("[MW] This command may only be used in-game");
      }
    }
    return true;
  }
  private function mwWorldCreateCommand(CommandSender $sender, array $args) {
    if(!isset($args[1])) {
      $sender->sendMessage("[MW] Usage: create level seed generator options");
      return true;
    }
    if(!$this->checkPermission($sender, "mw.cmd.world.create")) {
      return $this->permissionFail($sender);
    }
    $level = $args[1];
    if($this->getServer()->isLevelGenerated($level)) {
      $sender->sendMessage("[MW] A world with the name " . $level . " already exists!");
      return true;
    }
    $seed = null;
    $generator = null;
    $options = [];
    if(isset($args[2])) $seed = intval($args[2]);
    if(isset($args[3])) {
      $generator = Generator::getGenerator($args[3]);
    }
    if(isset($args[4])) $options = ["preset" => $args[4] ];
    $this->getServer()->broadcastMessage("[MW] Creating level " . $level . "... (Expect Lag)");
    $this->getServer()->generateLevel($level, $seed, $generator, $options);
    $this->getServer()->loadLevel($level);
    return true;
  }
  private function mwWorldUnloadCommand(CommandSender $sender, array $args) {
    $force = false;
    if (isset($args[1]) && $args[1] == '-f') {
      $force = true;
      array_shift($args);
    }
    if(!isset($args[1])) {
      $sender->sendMessage("[MW] Must specify level name");
      return true;
    }
    $level = $args[1];
    if (!$this->getServer()->isLevelLoaded($level)) {
      $sender->sendMessage("[MW] Level $level is not loaded.");
      return true;
    }
    $world = $this->getServer()->getLevelByName($level);
    if ($world === null) {
      $sender->sendMessage("[MW] Unable to get $level");
      return true;
    }
    if ($this->getServer()->unloadLevel($world,$force)) {
      $sender->sendMessage("[MW] $level unloaded.");
    } else {
      $sender->sendMessage("[MW] Unable to unload $level.  Try -f");
    }
    return true;
  }
  private function mwWorldLoadCommand(CommandSender $sender, array $args) {
    if(!isset($args[1])) {
      $sender->sendMessage("[MW] Must specify level name");
      return true;
    }
    $level = $args[1];
    if (!$this->mwAutoLoad($sender,$level)) {
      $sender->sendMessage("[MW] Unable to load $level");
    }
    return true;
  }
  private function mwWorldListCommand(CommandSender $sender, array $args) {
    if(!$this->checkPermission($sender, "mw.cmd.world.ls")) {
      return $this->permissionFail($sender);
    }
    $txt  = [];
    $pageNumber = 1;
    if (is_numeric($args[count($args)-1])) {
      $pageNumber = (int)array_pop($args);
      if($pageNumber <= 0) $pageNumber = 1;
    }

    if (isset($args[1])) {
      $level = $args[1];
      if (!$this->mwAutoLoad($sender,$level)) {
	$sender->sendMessage("[MW] Unable to load $level");
	return true;
      }
      $world = $this->getServer()->getLevelByName($level);
      if (!$world) {
	$sender->sendMessage("[MW] $level not loaded");
	return true;
      }
      //==== provider
      $provider = $world->getProvider();
      $hdr = "Info for $level";
      $txt[] = "Provider: ". get_class($provider);
      $txt[] = "Path: ".$provider->getPath();
      $txt[] = "Name: ".$provider->getName();
      $txt[] = "Seed: ".$provider->getSeed();
      $txt[] = "Generator: ".$provider->getGenerator();
      $txt[] = "Generator Options: ".print_r($provider->getGeneratorOptions(),true);
      $spawn = $provider->getSpawn();
      $txt[] = "Spawn: ".$spawn->getX().",".$spawn->getY().",".$spawn->getZ();
      $f = $this->getServer()->getDataPath(). "worlds/".$level."/motd.txt";
      $txt[] = "MOTD: $f";
      if (file_exists($f)) {
	$txt[] = "MOTD:";
	foreach (file($f) as $ln) {
	  $txt[] = "  ".TextFormat::BLUE.$ln.TextFormat::RESET;
	}
      }

    } else {
      $dir = $this->getServer()->getDataPath(). "worlds";
      if (!is_dir($dir)) {
	$sender->sendMessage("[MW] Missing path $dir");
	return true;
      }
      $count = 0;
      $dh = opendir($dir);
      if (!$dh) return true;
      while (($file = readdir($dh)) !== false) {
	if ($file == '.' || $file == '..') continue;
	if ($this->getServer()->isLevelLoaded($file)) {
	  $txt[] = "- $file (loaded)";
	  ++$count;
	  continue;
	}
	if ($this->getServer()->isLevelGenerated($file)) {
	  $txt[] = "- $file";
	  ++$count;
	  continue;
	}
      }
      closedir($dh);
      $hdr = "Worlds: ".$count;
    }
    if($sender instanceof ConsoleCommandSender){
      $sender->sendMessage( TextFormat::GREEN.$hdr.TextFormat::RESET);
      foreach ($txt as $ln) $sender->sendMessage($ln);
      return true;
    }
    $pageHeight = 5;
    $hdr = TextFormat::GREEN.$hdr. TextFormat::RESET;
    if (($pageNumber-1) * $pageHeight >= count($txt)) {
      $sender->sendMessage($hdr);
      $sender->sendMessage("Only ".intval(count($txt)/$pageHeight)." pages available");
      return true;
    }
    $hdr .= TextFormat::RED." ($pageNumber of ".intval(count($txt)/$pageHeight).")".TextFormat::RESET;
    $sender->sendMessage($hdr);
    for ($ln = ($pageNumber-1)*$pageHeight;$ln < count($txt) && $pageHeight--;++$ln) {
      $sender->sendMessage($txt[$ln]);
    }
    return true;
  }
  private function checkPermission(CommandSender $sender, $permission) {
    if($sender->hasPermission($permission)) return true;
    return false;
  }
  private function permissionFail(CommandSender $sender) {
    $sender->sendMessage("You do not have permission to do that.");
    return true;
  }
  public function onDisable() {
    $this->getLogger()->info("ManyWorlds Unloaded!");
  }
  public function teleport($player,$level,$spawn=null) {
    $world = $this->getServer()->getLevelByName($level);
    $location = $world->getSafeSpawn($spawn);
    $player->teleport($location);
    $f = $this->getServer()->getDataPath(). "worlds/".$level."/motd.txt";
    if (file_exists($f)) $player->sendMessage(file_get_contents($f));
    $this->getServer()->getScheduler()->scheduleDelayedTask(new MwTask($this,$player,$location),5);
    $this->getServer()->getScheduler()->scheduleDelayedTask(new MwTask($this,$player,$location),10);
    $this->getServer()->getScheduler()->scheduleDelayedTask(new MwTask($this,$player,$location),20);
  }
  public function delayedTP($m) {
    list($name,$x,$y,$z) = explode("\0",$m);
    $player = $this->getServer()->getPlayer($name);
    if (!$player) return;
    $player->teleport(new Vector3($x,$y,$z));
    //$this->getServer()->broadCastMessage("Moving $name to $x,$y,$z");
  }
}
