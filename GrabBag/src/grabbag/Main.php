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

use pocketmine\utils\Config;

use pocketmine\entity\Entity;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\utils\Random;
use pocketmine\level\Position;
use pocketmine\item\Item;


class Main extends Plugin implements CommandExecutor {
  protected $listener;
  protected $config;

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

  public function after($ticks,$callback) {
    $task = new GrabBagTask($this,$callback);
    $this->getServer()->getScheduler()->scheduleDelayedTask($task,$ticks);
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
    $this->getLogger()->info("* GrabBag Enabled!");
    $this->listener = new GrabBagListener($this);
    @mkdir($this->getDataFolder());
    $defaults 
      = [
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
	 "world-protect"=>[
			   "world"=>[
				     "status"=>"locked",
				     "users"=>["a","b"],
				     ],

			   ],
	 ];
    if (file_exists($this->getDataFolder()."config.yml")) {
      unset($defaults["world-protect"]["world"]);
      unset($defaults["spawn"]["items"]);
    }
    $this->config=(new Config($this->getDataFolder()."config.yml",
			      Config::YAML,$defaults))->getAll();
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
    case "gma":
      if (!$this->access($sender,"gb.cmd.gma")) return true;
      return $this->cmdGmX($sender,2);
    case "slay":
      if (!$this->access($sender,"gb.cmd.slay")) return true;
      return $this->cmdSlay($sender,$args);
    case "heal":
      if (!$this->access($sender,"gb.cmd.heal")) return true;
      return $this->cmdHeal($sender,$args);
    case "whois":
      if (!$this->access($sender,"gb.cmd.whois")) return true;
      return $this->cmdWhois($sender,$args);
    case "wp":
      if (!$this->access($sender,"gb.cmd.wp")) return true;
      return $this->cmdWp($sender,$args);
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
  // Manage world protect
  private function cmdWp(CommandSender $c,$args) {
    if ($this->inGame($c,false)) {
      $world = $c->getLevel()->getName();
    } else {
      if (!isset($args[0])) {
	$c->sendMessage("Must specify a world name");
	return false;
      }
      $world = array_shift($args);
      if (!$this->getServer()->isLevelGenerated($world)) {
	$c->sendMessage("$world: does not exist");
	return true;
      }
    }
    if (!isset($this->config["world-protect"]))
      $this->config["world-protect"] = [];
    if (!isset($this->config["world-protect"][$world])) {
      $dat = [ "status"=>"open",
	      "users"=>[],
	      ];
    } else {
      $dat = $this->config["world-protect"][$world];
    }

    if (!isset($args[0])) {
      $c->sendMessage("$world: ".$dat["status"]);
      if (count($dat["users"])) {
	$c->sendMessage("- Authorized: ".implode(", ",$dat["users"]));
      }
      return true;
    }
    if ($this->inGame($c,false)) {
      if (!in_array($c->getName(),$dat["users"])) {
	$c->sendMessage("You are not in the authorized list");
	return true;
      }
    }

    switch (array_shift($args)) {
    case "add":
      if (!isset($args[0])) {
	$c->sendMessage("Must specify a player to add");
	return false;
      }
      if (in_array($args[0],$dat["users"])) {
	$c->sendMessage("$args[0] already in the authorized list");
	return true;
      }
      $p = $this->getServer()->getPlayer($args[0]);
      if (!$p) {
	$c->sendMessage("$args[0] can not be found.  Maybe they are offline");
	return true;
      }
      $p->sendMessage("You are now in the authorized list for $world");
      $dat["users"][] = $p->getName();

      $c->sendMessage("$args[0] added to the authorized list for $world");
      break;
    case "rm":
      if (!isset($args[0])) {
	$c->sendMessage("Must specify a player to add");
	return false;
      }
      if (!in_array($args[0],$dat["users"])) {
	$c->sendMessage("$args[0] is not in the authorized list");
	return true;
      }
      $lst = [];
      foreach ($dat["users"] as $i) {
	if ($i != $args[0]) $lst[] = $i;
      }
      $dat["users"] = $lst;
      $p = $this->getServer()->getPlayer($args[0]);
      if ($p) $p->sendMessage("You have been removed from the authorized list for $world");
      $c->sendMessage("$args[0] removed from authorized list for $world");
      break;
    case "close":
      $dat["status"] = "locked";
      break;
    case "lock":
      $dat["status"] = "locked";
      break;
    case "protect":
      $dat["status"] = "protected";
      break;
    case "open":
      $dat["status"] = "open";
      break;
    case "unprotect":
      $dat = null;
      break;
    default:
      $c->sendMessage("Invalid sub command");
      return false;
    }
    // Save configuration
    @mkdir($this->getDataFolder());
    if ($dat) {
      $this->config["world-protect"][$world]= $dat;
    } else {
      unset($this->config["world-protect"][$world]);
    }
    $yaml = new Config($this->getDataFolder()."config.yml",Config::YAML,
		       $this->config);
    $yaml->setAll($this->config);
    $yaml->save();

    return true;
  }

  //////////////////////////////////////////////////////////////////////
  // Event based stuff...
  //////////////////////////////////////////////////////////////////////
  public function worldProtect($player) {
    if (!isset($this->config["world-protect"])) return false;
    $world = $player->getLevel()->getName();
    if (!isset($this->config["world-protect"][$world])) return false;
    switch ($this->config["world-protect"][$world]["status"]) {
    case "locked":
      return true;
    case "protected":
      if (!isset($this->config["world-protect"][$world]["users"]))
	return false;
      if (in_array($player->getName(),
		   $this->config["world-protect"][$world]["users"]))
	return false;
      return true;
    case "open":
    default:
      return false;
    }
    return false;
  }
  public function onPlayerJoin($player) {
    $pl = $this->getServer()->getPlayer($player);
    if ($pl == null) return;
    if ($pl->isOp()) {
      $this->getServer()->broadcastMessage("Server Op ".$pl->getName()." has joined.");
    }
  }
  public function respawnPlayer($player) {
    $pl = $this->getServer()->getPlayer($player);
    if ($pl == null) return;

    if (isset($this->config["spawn"])) {
      if (isset($this->config["spawn"]["armor"])
	  && $pl->hasPermission("gb.spawnarmor.receive")) {
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
	  $pl->getInventory()->setArmorItem($slot,new Item($type+$slot,0,1));
	}
      }
      if (isset($this->config["spawn"]["items"])
	  && $pl->hasPermission("gb.spawitems.receive")) {
	// Figure out if the inventory is empty...
	$cnt = 0;
	$max = $pl->getInventory()->getSize();
	foreach ($pl->getInventory()->getContents() as $slot => &$item) {
	  if ($slot < $max) ++$cnt;
	}
	if (!$cnt) {
	  // This player has nothing... let's give them some to get started...
	  foreach ($this->config["spawn"]["items"] as $i) {
	    $r = explode(":",$i);
	    if (count($r) == 3) {
	      $item = new Item($r[0],$r[1],$r[2]);
	      $pl->getInventory()->addItem($item);
	    }
	  }
	}
      }
    }
  }
}
