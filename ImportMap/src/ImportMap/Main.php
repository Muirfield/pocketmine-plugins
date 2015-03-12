<?php
namespace ImportMap;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\Config;
use pocketmine\level\format\LevelProviderManager;

class Main extends Plugin implements CommandExecutor {

  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    switch($cmd->getName()) {
    case "im":
      if (!$sender->hasPermission("im.cmd.im")) {
	$sender->sendMessage("You do not have permission to do that.");
	return true;
      }
      $async = false;
      if (isset($args[0])) {
	if ($args[0] == '-s') {
	  $async = false;
	  array_shift($args);
	} elseif ($args[0] == '-a') {
	  $async = true;
	  array_shift($args);
	}
      }
      if (isset($args[0]) && isset($args[1])) {
	$impath = $args[0];
	$world = $args[1];
	if ($this->getServer()->isLevelGenerated($world)) {
	  $sender->sendMessage("$world already exists");
	  return true;
	}
	$impath = preg_replace('/\/*$/',"",$impath).'/';
	if (!is_dir($impath)) {
	  $sender->sendMessage("$impath not found");
	  return true;
	}
	$srcfmt = LevelProviderManager::getProvider($impath);
	if (!$srcfmt) {
	  $sender->sendMessage("$impath: Format not recognized");
	  return true;
	}
	$dstfmt = $this->getServer()->getProperty("level-settings.default-format", "mcregion");
	$dstfmt = LevelProviderManager::getProviderByName($dstfmt);
	if ($dstfmt === null) {
	  $dstfmt = "mcregion";
	} else {
	  $dstfmt = $dstfmt::getProviderName();
	}
	$target = $this->getServer()->getDataPath()."worlds/".$world."/";

	if ($async) {
	  $sender->sendMessage("Importing $impath to $world in the background");
	  $this->getServer()->broadcastMessage("Importing world, expect LAG!");
	  $this->getServer()->getScheduler()->scheduleAsyncTask(new Importer($impath,$target,$dstfmt));
	} else {
	  $sender->sendMessage("Importing $impath to $world");
	  $this->getServer()->broadcastMessage("Importing world, expect DISCONNECTS!");
	  $w = new Importer($impath,$target,$dstfmt);
	  $w->onRun();
	  $w->onCompletion($this->getServer());
	}
	return true;
      } else {
	$sender->sendMessage("Usage: im <path> <world>");
      }
      break;
    default:
      return false;
    }
    return true;
  }

  public function onEnable() {
    @mkdir($this->getDataFolder());
    $cfg = (new Config($this->getDataFolder()."config.yml",
		       Config::YAML,["blocks" => [ 1 => 1 ]]))->getAll();
    if (isset($cfg["blocks"])) {
      $btab = $cfg["blocks"];
    } else {
      $btab = [];
    }
    Importer::init($btab);
    $this->getLogger()->info("ImportMap: ".count($btab)." rules applied");
  }
  public function onDisable() {
    $this->getLogger()->info("ImportMap Unloaded!");
  }
}
