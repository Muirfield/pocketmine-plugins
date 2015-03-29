<?php
namespace grabbag;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandExecutor;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

use pocketmine\utils\Config;
use pocketmine\command\PluginCommand;

//use pocketmine\entity\Entity;
//use pocketmine\nbt\tag\Byte;
//use pocketmine\nbt\tag\Compound;
//use pocketmine\nbt\tag\Double;
//use pocketmine\nbt\tag\Enum;
//use pocketmine\nbt\tag\Float;
//use pocketmine\utils\Random;
//use pocketmine\level\Position;
use pocketmine\item\Item;


class Main extends PluginBase implements CommandExecutor {
  protected $listener;
  protected $config;
  protected $modules;
  protected $slain = [];

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
    $fmt = "";
    foreach ($cols as $c) {
      if (strlen($fmt) > 0) $fmt .= " ";
      $fmt .= "%-".$c."s";
    }
    foreach ($tab as $row) {
      $txt[] = sprintf($fmt,...$row);
    }
    return $this->paginateText($sender,$pageNumber,$txt);
  }
  // Standard call-backs
  public function onDisable() {
    //$this->getLogger()->info("GrabBag Unloaded!");
  }
  public function onLoad() {
    @mkdir($this->getDataFolder());

    $v = $this->getDescription()->getVersion();
    $modules = $this->getDataFolder()."modules-dist.yml";
    $modcfg = $this->getDataFolder()."modules.yml";
    $current = is_file($modules) ? file_get_contents($modules) : "";
    $active = is_file($modcfg) ? file_get_contents($modcfg) : "";
    $fp = $this->getResource(basename($modcfg));
    $next = "version: $v\n".stream_get_contents($fp);
    fclose($fp);
    if ($next != $current) {
      // We need to upgrade...
      file_put_contents($modules,$next);
      if ($current == $active) {
	// It is not a custom yml, so we just upgrade...
	file_put_contents($modcfg,$next);
	$this->getLogger()->info(TextFormat::RED."module configuration has been updated".TextFormat::RESET);
      } else {
	$this->getLogger()->info(TextFormat::RED."modules-dist.yml has been updated".TextFormat::RESET);
	$this->getLogger()->info(TextFormat::GREEN."Review your modules.yml to activate new features".TextFormat::RESET);
      }
    }
    $this->modules =(new Config($this->getDataFolder()."modules.yml",
				Config::YAML,[]))->getAll();
    foreach (["listener","commands"] as $i) {
      if (!isset($this->modules[$i])) $this->modules[$i] = [];
    }

    $pluginCmds = [];
    foreach ($this->modules["commands"] as $cmd => $dat) {
      if(strpos($cmd, ":") !== false){
	$this->getLogger()->info("Unable to load command $cmd");
	continue;
      }
      if (!is_array($dat)) continue;
      $newCmd = new PluginCommand($cmd,$this);
      if(isset($dat["description"])){
	$newCmd->setDescription($dat["description"]);
      }
      if(isset($dat["usage"])){
	$newCmd->setUsage($dat["usage"]);
      }
      if(isset($dat["aliases"]) and is_array($dat["aliases"])){
	$aliasList = [];
	foreach($dat["aliases"] as $alias){
	  if(strpos($alias, ":") !== false){
	    $this->getLogger()->info("Unable to load alias $alias");
	    continue;
	  }
	  $aliasList[] = $alias;
	}
	$newCmd->setAliases($aliasList);
      }
      if(isset($dat["permission"])){
	$newCmd->setPermission($dat["permission"]);
      }
      if(isset($dat["permission-message"])){
	$newCmd->setPermissionMessage($dat["permission-message"]);
      }
      $pluginCmds[] = $newCmd;
    }
    if (count($pluginCmds) > 0) {
      $cmdMap = $this->getServer()->getCommandMap();
      $cmdMap->registerAll($this->getDescription()->getName(),$pluginCmds);
    }

    $this->getLogger()->info("GrabBag Loaded!");
  }
  public function onEnable(){
    $this->listener = new GrabBagListener($this);
    $this->slain = [];
    $defaults =
      [
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
       ];
    if (file_exists($this->getDataFolder()."config.yml")) {
      unset($defaults["spawn"]["items"]);
    }
    $this->config=(new Config($this->getDataFolder()."config.yml",
			      Config::YAML,$defaults))->getAll();
    // $this->getLogger()->info("* GrabBag Enabled!");
  }
  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    // Make sure the command is active
    if (!isset($this->modules["commands"][$cmd->getName()])) return false;
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
    case "showtimings":
      if (!$this->access($sender,"gb.cmd.timings")) return true;
      return $this->cmdTimings($sender,$args);
    case "get":
      if (!$this->access($sender,"gb.cmd.get")) return true;
      return $this->cmdGet($sender,$args);
    case "seeinv":
      if (!$this->access($sender,"gb.cmd.seeinv")) return true;
      return $this->cmdSeeInv($sender,$args);
    case "seearmor":
      if (!$this->access($sender,"gb.cmd.seearmor")) return true;
      return $this->cmdSeeArmor($sender,$args);
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
    array_shift($args);
    $this->slainGc();
    if (count($args)) {
      $this->slain[$victim->getName()] = [ time(), implode(" ",$args) ];
    }
    $victim->setHealth(0);
    $c->sendMessage(TextFormat::RED.$victim->getName()." has been slain.".TextFormat::RESET);
    return true;
  }
  private function slainGc() {
    $lst = [];
    $now = time();
    foreach ($this->slain as $p=>$dat) {
      list($time,$msg) = $dat;
      if ($now - $time > 3) $lst[] = $p;
    }
    foreach ($lst as $p) {
      unset($this->slain[$p]);
    }
  }
  public function onPlayerDeath($name) {
    if (isset($this->slain[$name])) {
      list($time,$msg) = $this->slain[$name];
      unset($this->slain[$name]);
      return $msg;
    }
    return "";
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
  private function cmdTimings(CommandSender $c,$args) {
    $pageNumber = $this->getPageNumber($args);
    if (count($args)) {
      // Show the specified report
      $rpt = array_shift($args);
      $rpt = preg_replace('/[^0-9]+/i','',$rpt);
      $f = $this->getServer()->getDataPath()."timings/timings$rpt.txt";
      if (!file_exists($f)) {
	$c->sendMessage("Report $rpt can not be found");
	return true;
      }
      $txt = file($f);
      array_unshift($txt,"Report: timings$rpt");
      return $this->paginateText($c,$pageNumber,$txt);
    }
    $txt = ["HDR"];
    // Inventorise the reports
    $count = 0;
    foreach (glob($this->getServer()->getDataPath(). "timings/timings*.txt") as $f) {
      ++$count;
      $txt[] = "- ".basename($f);
    }
    if ($count == 0) {
      $sender->sendMessage(TextFormat::RED."No timmings report found");
      $sender->sendMessage("Enable timings by typing /timings on");
      $sender->sendMessage("Generate timings report by typing /timings report");
      return true;
    }
    $txt[0] = "Reports: $count";
    return $this->paginateText($c,$pageNumber,$txt);
  }
  private function cmdGet(CommandSender $c,$args) {
    if (!isset($args[0])) return false;
    if (!$this->inGame($c)) return true;
    if ($c->isCreative()) {
      $c->sendMessage("You are in creative mode");
      return true;
    }
    $item = Item::fromString($args[0]);
    if ($item->getId() == 0) {
      $c->sendMessage(TextFormat::RED."There is no item called ".$args[0]);
      return true;
    }
    if (isset($args[1])) {
      $item->setCount((int)$args[1]);
    } else {
      $item->setCount($item->getMaxStackSize());
    }
    $c->getInventory()->addItem(clone $item);
    $this->getServer()->broadcastMessage($c->getName()." got ".
					 $item->getCount()." of ".
					 $item->getName().
					 " (" . $item->getId() . ":" .
					 $item->getDamage() . ")");
    return true;
  }
  private function cmdSeeArmor(CommandSender $c,$args) {
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
    $tab= [["Armor for",TextFormat::RED.$args[0]]];
    foreach ([0=>"head",1=>"body",2=>"legs",3=>"boots"] as $slot=>$attr) {
      $item = $target->getInventory()->getArmorItem($slot);
      if ($item->getID() == 0) continue;
      $tab[]=[$attr.TextFormat::BLUE,
	      $item->getName()." (" .$item->getId().":".$item->getDamage().")"];
    }
    return $this->paginateTable($c,$pageNumber,$tab);
  }
  private function cmdSeeInv(CommandSender $c,$args) {
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
    $tab= [[$args[0],"Count","Damage"]];
    $max = $target->getInventory()->getSize();
    foreach ($target->getInventory()->getContents() as $slot => &$item) {
      if ($slot >= $max) continue;
      $tab[] = [$item->getName()." (".$item->getId().")",
		$item->getCount(),$item->getDamage() ];
    }
    if (count($tab) == 1) {
      $c->sendMessage("The inventory for $args[0] is EMPTY");
      return true;
    }
    return $this->paginateTable($c,$pageNumber,$tab);
  }
  //////////////////////////////////////////////////////////////////////
  // Event based stuff...
  //////////////////////////////////////////////////////////////////////
  public function onPlayerJoin($player) {
    if (!array_key_exists("adminjoin",$this->modules["listener"])) return;
    $pl = $this->getServer()->getPlayer($player);
    if ($pl == null) return;
    if ($pl->isOp()) {
      $this->getServer()->broadcastMessage("Server Op ".$pl->getName()." has joined.");
    }
  }
  private function spawnArmor($pl) {
    if ($pl->isCreative()) return;
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
  private function spawnItems($pl) {
    if ($pl->isCreative()) return;

    // Figure out if the inventory is empty...
    $cnt = 0;
    $max = $pl->getInventory()->getSize();
    foreach ($pl->getInventory()->getContents() as $slot => &$item) {
      if ($slot < $max) ++$cnt;
    }
    if ($cnt) return;

    // This player has nothing... let's give them some to get started...
    foreach ($this->config["spawn"]["items"] as $i) {
      $r = explode(":",$i);
      if (count($r) != 3) continue;
      $item = new Item($r[0],$r[1],$r[2]);
      $pl->getInventory()->addItem($item);
    }
  }

  public function respawnPlayer($player) {
    $pl = $this->getServer()->getPlayer($player);
    if ($pl == null) return;
    if (!isset($this->config["spawn"])) return;
    if (isset($this->config["spawn"]["armor"])
	&& array_key_exists("spawnarmor",$this->modules["listener"])
	&& $pl->hasPermission("gb.spawnarmor.receive")) {
      $this->spawnArmor($pl);
    }
    if (isset($this->config["spawn"]["items"])
	&& array_key_exists("spawnitems",$this->modules["listener"])
	&& $pl->hasPermission("gb.spawnitems.receive")) {
      $this->spawnItems($pl);
    }
  }

  public function canCompassTp($player) {
    if (!array_key_exists("compasstp",$this->modules["listener"])) return false;
    $pl = $this->getServer()->getPlayer($player);
    if ($pl == null) return false;
    return $pl->hasPermission("gb.compasstp.allow");
  }
}
