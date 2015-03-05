<?php
namespace phpeval;
use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\command\CommandExecutor;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Main extends Plugin implements CommandExecutor {
  public function onLoad() {
    $this->getLogger()->info("PHPEval Loaded!");
  }
  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    switch($cmd->getName()) {
    case "php":
      if (!$sender->hasPermission("phpeval.cmd.php")) {
	$sender->sendMessage("You do not have permission to do that.");
	return true;
      }
      // $sender->sendMessage("eval(".implode(' ',$args).")");
      $line = implode(" ",$args).";";
      if (substr($line,0,1) == "=") $line = "return ".substr($line,1);
      $ret = eval($line);
      $sender->sendMessage($ret);
      break;
    default:
      return false;
    }
    return true;
  }
 public function onDisable() {
    $this->getLogger()->info("PHPEval Unloaded!");
  }
}
