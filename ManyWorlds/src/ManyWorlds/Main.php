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
use pocketmine\command\Command;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;

class Main extends Plugin implements CommandExecutor {
  public function onLoad() {
    $this->getLogger()->info("MultiWorld Loaded!");
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
  private function mwTeleportCommand(CommandSender $sender,array $args) {
    if (!isset($args[1])) {
      $sender->sendMessage("[MW] must specify level");
      return true;
    }
    $level = $args[1];
    $player = null;
    if (isset($args[2])) {
      // Teleport others
      $player = $this->getServer()->getPlayer($args[2]);
      if($this->checkPermission($sender, "universe.cmd.tp.others")) {
	if ($player->isOnline()) {
	  if($player->getLevel() == $this->getServer()->getLevelByName($level)) {
	    $sender->sendMessage("[MW] " . $player . " is already in " . $level . "!");
	  } else {
	    if($this->getServer()->isLevelLoaded($level)) {
	      $player->sendMessage("[MW] Teleporting you to " . $level . " at\n" . $sender->getName() . "'s request...");
	      $world = $this->getServer()->getLevelByName($level);
	      $player->teleport($world->getSpawnLocation());
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
	    if($this->getServer()->isLevelLoaded($level)) {
	      $sender->sendMessage("[MW] Teleporting you to level " . $level . "...");
	      $world = $this->getServer()->getLevelByName($level);
	      $sender->teleport($world->getSpawnLocation());
	    } else {
	      $sender->sendMessage("[MW] Unable to teleport you to " . $level . " as it\nis not loaded!");
	    }
	  } else {
	    $sender->sendMessage("[Universe] You are already in " . $level . "!");
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
  private function mwWorldLoadCommand(CommandSender $sender, array $args) {
    if(!isset($args[1])) {
      $sender->sendMessage("[MW] Must specify level name");
      return true;
    }
    $level = $args[1];
    if(!$this->checkPermission($sender, "mw.cmd.world.load")) {
      return $this->permissionFail($sender);
    }
    if(!$this->getServer()->isLevelGenerated($level)) {
      $sender->sendMessage("[MW] No level with the name " . $level . " exists!");
      return true;
    }
    if($this->getServer()->isLevelLoaded($level)) {
	$sender->sendMessage("[MW] Level " . $level . " is already loaded!");
	return true;
    }
    $this->getServer()->loadLevel($level);
    $sender->sendMessage("[MW] Level " . $level . " is being loaded\nin the background!");
    return true;
  }
  private function mwWorldListCommand(CommandSender $sender, array $args) {
    if(!$this->checkPermission($sender, "mw.cmd.world.ls")) {
      return $this->permissionFail($sender);
    }
    $dir = $this->getServer()->getDataPath(). "worlds";
    if (!is_dir($dir)) {
      $sender->sendMessage("[MW] Missing path $dir");
      return true;
    }
    $dh = opendir($dir);
    if (!$dh) return true;
    while (($file = readdir($dh)) !== false) {
      if ($file == '.' || $file == '..') continue;
      if ($this->getServer()->isLevelLoaded($file)) {
	$sender->sendMessage("- $file (loaded)");
	continue;
      }
      if (!is_file($dir.'/'.$file.'/level.dat')) continue;
      $sender->sendMessage("- $file");
    }
    closedir($dh);
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
    $this->getLogger()->info("MultiWorld Unloaded!");
  }
}