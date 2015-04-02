<?php
namespace aliuly\pmptemplate;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandExecutor;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

use pocketmine\utils\Config;


use pocketmine\item\Item;
use pocketmine\network\protocol\SetHealthPacket;

class Main extends PluginBase implements CommandExecutor {
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
    $this->getLogger()->info("Commander Unloaded!");
  }
  public function onEnable(){
    $this->getLogger()->info("* Commander Enabled!");
  }
  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    switch($cmd->getName()) {
    case "m":
      if (!$this->access($sender,"cmd.cmd.m")) return true;
      return $this->cmdMain($sender,$args);
    }
    return false;
  }
  // Command implementations

  private function cmdMain(CommandSender $c,$args) {
    //$c->sendMessage("ARGS: ".implode(',',$args));
    if (count($args) == 0) return false;
    $p = $this->getServer()->getPlayer($args[0]);
    if ($p == null) return false;
    $pk = new SetHealthPacket;
    $pk->health = $p->getHealth()-1;
    $p->dataPacket($pk);
    return true;
  }
}
