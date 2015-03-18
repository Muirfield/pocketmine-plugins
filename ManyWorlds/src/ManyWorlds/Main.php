<?php
namespace ManyWorlds;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\permission\Permission;
use pocketmine\level\Level;
use pocketmine\level\generator\Generator;
//use pocketmine\command\CommandExecutor;
use pocketmine\event\Listener;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\Command;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\player\PlayerJoinEvent;

class Main extends Plugin implements Listener {
  protected $teleporters = [];
  protected $canUnload = false;

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
  private function after($task,$ticks) {
    $this->getServer()->getScheduler()->scheduleDelayedTask($task,$ticks);
  }

  public function onLoad() {
    $this->getLogger()->info("ManyWorlds Loaded!");
  }
  public function onEnable(){
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }

  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    switch($cmd->getName()) {
    case "motd":
      return $this->motdCommand($sender);
      break;
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
	case "motd":
	  return $this->mwWorldMotdCommand($sender,$args);
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
    if (isset($args[1]) && $args[1] == '--enable') {
      $this->canUnload = true;
      $sender->sendMessage("[MW] Unload sub-command enabled");
      return true;
    } elseif (isset($args[1]) && $args[1] == '--disable') {
      $this->canUnload = true;
      $sender->sendMessage("[MW] Unload sub-command disabled");
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
    if ($args[1] == "--all") {
      $sender->sendMessage("[MW] ".TextFormat::RED."Loading ALL levels".TextFormat::RESET);
      foreach (glob($this->getServer()->getDataPath(). "worlds/*") as $f) {
	$level = basename($f);
	if ($this->getServer()->isLevelLoaded($level)) continue;
	if (!$this->getServer()->isLevelGenerated($level)) continue;
	if (!$this->mwAutoLoad($sender,$level)) {
	  $sender->sendMessage("[MW] Unable to load $level");
	}
      }
      return true;
    }
    $level = $args[1];
    if (!$this->mwAutoLoad($sender,$level)) {
      $sender->sendMessage("[MW] Unable to load $level");
    }
    return true;
  }

  private function mwWorldDetails(CommandSender $sender,$level) {
    $txt = [];

    if (!$this->mwAutoLoad($sender,$level)) {
      $sender->sendMessage("[MW] Unable to load $level");
      return null;
    }
    $world = $this->getServer()->getLevelByName($level);
    if (!$world) {
      $sender->sendMessage("[MW] $level not loaded");
      return null;
    }
    //==== provider
    $provider = $world->getProvider();
    $txt[] = "Info for $level";
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
	$ln = preg_replace('/\s+$/','',$ln);
	$txt[] = "  ".TextFormat::BLUE.$ln.TextFormat::RESET;
      }
    }
    return $txt;
  }

  private function mwWorldListing(CommandSender $sender) {
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
	$count = count($this->getServer()->getLevelByName($file)->getPlayers());
	if ($count) $attrs[] = "players:$count";
      }
      $ln = "- $file";
      if (count($attrs)) $ln .= " (".implode(",",$attrs).")";
      $txt[] = $ln;
    }
    closedir($dh);
    $txt[0] = "Worlds: ".$count;
    return $txt;
  }

  private function mwWorldListCommand(CommandSender $sender, array $args) {
    if(!$this->checkPermission($sender, "mw.cmd.world.ls")) {
      return $this->permissionFail($sender);
    }
    $pageNumber = $this->getPageNumber($args);

    if (isset($args[1])) {
      $txt = $this->mwWorldDetails($sender,$args[1]);
      if ($txt == null) return true;
    } else {
      $txt = $this->mwWorldListing($sender);
      if ($txt == null) return true;
    }
    return $this->paginateText($sender,$pageNumber,$txt);
  }
  private function getPageNumber(array &$args) {
    $pageNumber = 1;
    if (is_numeric($args[count($args)-1])) {
      $pageNumber = (int)array_pop($args);
      if($pageNumber <= 0) $pageNumber = 1;
    }
    return $pageNumber;
  }
  private function paginateText(CommandSender $sender,$pageNumber,array $txt) {
    $hdr = array_shift($txt);
    //if($sender instanceof ConsoleCommandSender){
    if(0) {
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

  private function motdCommand(CommandSender $sender) {
    if (!($sender instanceof Player)) {
      $sender->sendMessage("[MW] You must use this command in-game");
      return true;
    }
    $level = $sender->getLevel()->getName();
    $f = $this->getServer()->getDataPath(). "worlds/".$level."/motd.txt";
    if (file_exists($f)) {
      $sender->sendMessage(file_get_contents($f));
    } else {
      $sender->sendMessage("Sorry, no \"motd.txt\"");
    }
    return true;
  }
  private function mwWorldShowMotd(CommandSender $sender,$level) {
    // Show a MOTD for a world
    $f = $this->getServer()->getDataPath(). "worlds/".$level."/motd.txt";
    if (file_exists($f)) {
      $l = 1;
      foreach (file($f) as $ln) {
	$ln = preg_replace('/\s+$/','',$ln);
	$sender->sendMessage($l.' '.TextFormat::BLUE.$ln.TextFormat::RESET);
	++$l;
      }
    }
    return true;
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

  private function mwWorldMotdCommand(CommandSender $sender, array $args) {
    if (count($args) < 2) {
      $sender->sendMessage("[MW] Must specify level name");
      return true;
    }
    $level = $args[1];
    if (!$this->getServer()->isLevelGenerated($level)) {
      $sender->sendMessage("[MW] $level does not exist");
      return true;
    }

    if (count($args) == 2) {
      // Just show it...
      if(!$this->checkPermission($sender, "mw.cmd.world.ls")) {
	return $this->permissionFail($sender);
      }
      return $this->mwWorldShowMotd($sender,$level);
    }
    // Edit the MOTD text
    if(!$this->checkPermission($sender, "mw.cmd.world.motd")) {
      return $this->permissionFail($sender);
    }
    array_shift($args);array_shift($args);
    $lnum = array_shift($args);
    if (!is_numeric($lnum)) {
      $sender->sendMessage("[MW] please provide a line number");
      return true;
    }
    return $this->mwWorldEditMotd($sender,$level,$lnum,implode(" ",$args));
  }

  public function showMotd($m) {
    list($name,$level) = $m;
    $player = $this->getServer()->getPlayer($name);
    if (!$player) return;
    $f = $this->getServer()->getDataPath(). "worlds/".$level."/motd.txt";
    $txt[] = "MOTD: $f";
    if (file_exists($f)) {
      $player->sendMessage(file_get_contents($f));
    }
  }

  public function onJoin(PlayerJoinEvent $ev) {
    $p = $ev->getPlayer();
    $level = $p->getLevel()->getName();
    $this->after(new MwTask($this,"showMotd",[$p->getName(),$level]),10);
  }
  public function onLevelChange(EntityLevelChangeEvent $ev) {
    $level = $ev->getTarget()->getName();
    $traveller = $ev->getEntity();
    if ($traveller instanceof Player) {
      $traveller = $traveller->getName();
    } else {
      return;
    }
    $this->after(new MwTask($this,"showMotd",[$traveller,$level]),21);
  }

  public function teleport($player,$level,$spawn=null) {
    $world = $this->getServer()->getLevelByName($level);
    // Try to find a reasonable spawn location
    $location = $world->getSafeSpawn($spawn);
    $this->teleporters[$player->getName()] = time();
    foreach ([5,10,20] as $ticks) {
      // Try to keep the player in place until the chunk finish loading
      $this->after(new MwTask($this,"delayedTP",
			      [$player->getName(),
			       $location->getX(),$location->getY(),
			       $location->getZ()]),$ticks);
    }
    // Make sure that any damage he may have taken is restored
    $this->after(new MwTask($this,"restoreHealth",[$player->getName(),$player->getHealth()]),20);
    // Make sure the player survives the transfer...
    $player->setHealth($player->getMaxHealth());
    $player->teleport($location); // Start the teleport
  }


  public function restoreHealth($m) {
    list($name,$health) = $m;
    $player = $this->getServer()->getPlayer($name);
    if (!$player) return;
    $player->setHealth($health);
  }

  public function delayedTP($m) {
    list($name,$x,$y,$z) = $m;
    $player = $this->getServer()->getPlayer($name);
    if (!$player) return;
    $player->teleport(new Vector3($x,$y,$z));
  }

  public function onDamage(EntityDamageEvent $event) {
    // Try keep the player alive while on transit...
    $victim= $event->getEntity();
    if (!($victim instanceof Player)) return;
    if (!isset($this->teleporters[$victim->getName()])) return;
    if (time() - $this->teleporters[$victim->getName()] > 2) {
      unset($this->teleporters[$victim->getName()]);
      return;
    }
    $victim->heal($event->getDamage());
    $event->setCancelled(true);
    $event->setDamage(0);
  }
}
