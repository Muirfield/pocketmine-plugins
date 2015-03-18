<?php
namespace grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\command\CommandExecutor;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

use pocketmine\entity\Entity;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\utils\Random;
use pocketmine\level\Position;


class Main extends Plugin implements CommandExecutor {
  // Access and other permission related checks
  private function access(CommandSender $sender, $permission) {
    if($sender->hasPermission($permission)) return true;
    $sender->sendMessage("You do not have permission to do that.");
    return false;
  }
  private function inGame(CommandSender $sender) {
    if ($sender instanceof Player) return true;
    $sender->sendMessage("You can only use this command in-game");
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
    $this->getLogger()->info("GrabBag Unloaded!");
  }
  public function onLoad() {
    $this->getLogger()->info("GrabBag Loaded!");
  }
  public function onEnable(){
    $this->getLogger()->info("GrabBag Enabled!");
  }

  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    switch($cmd->getName()) {
    case "ops":
      if (!$this->access($sender,"gb.cmd.ops")) return true;
      return $this->cmdOps($sender,$args);
    case "players":
      if (!$this->access($sender,"gb.cmd.players")) return true;
      return $this->cmdPlayers($sender,$args);
    case "as":
      if (!$this->access($sender,"gb.cmd.sudo")) return true;
      return $this->cmdSudo($sender,$args);
    case "gms":
      if (!$this->access($sender,"gb.cmd.gms")) return true;
      return $this->cmdGmX($sender,0);
    case "gmc":
      if (!$this->access($sender,"gb.cmd.gmc")) return true;
      return $this->cmdGmX($sender,1);
    case "slay":
      if (!$this->access($sender,"gb.cmd.slay")) return true;
      return $this->cmdSlay($sender,$args);
    case "heal":
      if (!$this->access($sender,"gb.cmd.heal")) return true;
      return $this->cmdHeal($sender,$args);
    case "whois":
      if (!$this->access($sender,"gb.cmd.whois")) return true;
      return $this->cmdWhois($sender,$args);
    }
    return false;
  }
  // Command implementations

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
    $victim->setHealth(0);
    $c->sendMessage(TextFormat::RED.$args[0]." has been slain.".TextFormat::RESET);
    return true;
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
    if ($args[0] == 'chat') {
      array_shift($args);
      $chat = implode(" ",$args);
      $c->sendMessage("Sending message as $name");
      $this->getServer()->getPlugingManager()->callEvent($ev = new PlayerChatEvent($player,$chat));
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
    $tab = [[ "Player","World","Pos","Health" ]];
    $cnt = 0;
    foreach ($this->getServer()->getOnlinePlayers() as $player) {
      if(!$player->isOnline() || (($c instanceof Player) && !$c->canSee($player))) continue;
      $pos = $player->getPosition();
      $j = count($tab);
      $tab[]=[$player->getDisplayName(),$player->getLevel()->getName(),
	      $pos->getFloorX().",".$pos->getFloorY().",".$pos->getFloorZ(),
	      intval($player->getHealth()).'/'.intval($player->getMaxHealth())];
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
}
