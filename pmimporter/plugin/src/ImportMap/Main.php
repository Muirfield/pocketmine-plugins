<?php
namespace ImportMap;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\Config;
use pocketmine\level\format\LevelProviderManager;

class Main extends Plugin implements CommandExecutor {
  private function importer() {
    if ($this->isPhar())
      return preg_replace('/\/+$/','',substr($this->getFile(),7));
    //echo "IMPORTER: ".substr($this->getFile(),7)."\n";
    echo dirname(realpath($this->getFile()))."/pmimporter/pmimporter.phar\n";
    return dirname(realpath($this->getFile()))."/pmimporter/pmimporter.phar";
  }
  private function schedule($args) {
    $this->getServer()->getScheduler()->scheduleAsyncTask(new Importer($args));
  }
  private function usage(CommandSender $c) {
    $c->sendMessage("Usage:");
    $c->sendMessage("-    im version : get the pmimporter version");
    $c->sendMessage("-    im path world : import [path] as [world]");
    return true;
  }
  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {

    switch($cmd->getName()) {
    case "im":
      if (!$sender->hasPermission("im.cmd.im")) {
	$sender->sendMessage("You do not have permission to do that.");
	return true;
      }
      if (!isset($args[0])) return $this->usage($sender);
      if ($args[0] == "version") {
	$sender->sendMessage(Importer::phpRun([$this->importer(),'version']));
	return true;
      }
      if (!isset($args[1])) return $this->usage($sender);

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

      $sender->sendMessage("Importing $impath to $world in the background");
      $this->getServer()->broadcastMessage("Importing world, expect LAG!");
      $dir =$this->getDataFolder();
      $this->schedule([$this->importer(),'pmconvert',
		       '-c',$dir.'rules.txt','-f',$dstfmt,
		       $impath,$target]);
      return true;
      break;
    default:
      return false;
    }
    return true;
  }

  public function onEnable() {
    @mkdir($this->getDataFolder());
    $this->saveResource('rules.txt',false);
  }
  public function onDisable() {
    $this->getLogger()->info("ImportMap Unloaded!");
  }
}
