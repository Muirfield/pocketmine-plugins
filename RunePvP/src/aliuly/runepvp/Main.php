<?php
namespace aliuly\runepvp;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandExecutor;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\utils\Config;

class Main extends PluginBase implements CommandExecutor,Listener {
  protected $dbm;
  protected $points;
  protected $money;

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
  //////////////////////////////////////////////////////////////////////
  //
  // Standard call-backs
  //
  //////////////////////////////////////////////////////////////////////
  public function onEnable(){
    $this->getLogger()->info(TextFormat::YELLOW.
			     "#Credit: Cha0sRuin(alchemistdy@naver.com)");
    $this->getLogger()->info(TextFormat::YELLOW.
			     "#    for the original RuinPvP that this plugin");
    $this->getLogger()->info(TextFormat::YELLOW.
			     "#    was inspired from");
    @mkdir($this->getDataFolder());
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    $this->dbm = new DatabaseManager($this->getDataFolder()."runepvp.sqlite3");
    $defaults = [
		 "points" => [
			      "kills" => 100,
			      "level" => 1000,
			      ],
		 ];

    $cfg = (new Config($this->getDataFolder()."config.yml",
		       Config::YAML,$defaults))->getAll();
    $this->points = $cfg["points"];
    $pm = $this->getServer()->getPluginManager();
    if(!($this->money = $pm->getPlugin("PocketMoney"))
       && !($this->money = $pm->getPlugin("EconomyAPI"))
       && !($this->money = $pm->getPlugin("MassiveEconomy"))){
      $this->getLogger()->info(TextFormat::RED.
			       "# MISSING MONEY API PLUGIN");
      $this->getLogger()->info(TextFormat::BLUE.
			       ". Please install one of the following:");
      $this->getLogger()->info(TextFormat::WHITE.
			       "* PocketMoney");
      $this->getLogger()->info(TextFormat::WHITE.
			       "* EconomyAPI or");
      $this->getLogger()->info(TextFormat::WHITE.
			       "* MassiveEconomy");
    } else {
      $this->getLogger()->info(TextFormat::BLUE."Using money API from ".
			       TextFormat::WHITE.$this->money->getName()." v".
			       $this->money->getDescription()->getVersion());
    }
  }
  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    switch($cmd->getName()) {
    case "runepvp":
      if (count($args) == 0) return $this->cmdStats($sender,[]);
      $scmd = strtolower(array_shift($args));
      switch ($scmd) {
      case "stats":
	if (!$this->access($sender,"runepvp.cmd.stats")) return true;
	return $this->cmdStats($sender,$args);
      case "top":
	if (!$this->access($sender,"runepvp.cmd.top")) return true;
	return $this->cmdTops($sender,$args);
      case "help":
	return $this->cmdHelp($sender,$args);
      default:
	$sender->sendMessage("Unknown command.  Try /runepvp help");
	return false;
      }
    }
    return false;
  }
  //////////////////////////////////////////////////////////////////////
  //
  // Command implementations
  //
  //////////////////////////////////////////////////////////////////////
  public function getRankings($limit=10,$online=false) {
    if ($online) {
      // Online players only...
      $plist = [];
      foreach ($this->getServer()->getOnlinePlayers() as $p) {
	$plist[] = $p->getName();
      }
      if (count($plist) < 2) return null;
    } else {
      $plist = null;
    }
    return $this->dbm->getTops($limit,$plist);
  }
  private function cmdTops(CommandSender $c,$args) {
    if (count($args) == 0) {
      $res = $this->getRankings(5);
    } else {
      $res = $this->getRankings(5,true);
      if ($res == null) {
	$c->sendMessage("Not enough on-line players");
	return true;
      }
    }
    $c->sendMessage(". Player Level Kills");
    $i = 1;
    foreach ($res as $r) {
      $c->sendMessage(implode(" ",[$i++,$r["player"],$r["level"],$r["kills"]]));
    }
    return true;
  }

  private function cmdStats(CommandSender $c,$args) {
    if (count($args) == 0) {
      if (!$this->inGame($c)) return true;
      $args = [ $c->getName() ];
    }
    foreach ($args as $pl) {
      if ($this->inGame($c,false) && $pl != $c->getName()) {
	if (!$this->access($c,"runepvp.cmd.stats.other")) return true;
      }
      $score = $this->dbm->getScore($pl);
      if ($score == null) {
	$c->sendMessage("No scores found for $pl");
	continue;
      } else {
	$money = $this->getMoney($pl);
	if (count($args) != 1) $c->sendMessage(TextFormat::BLUE.$pl);
	$c->sendMessage(TextFormat::GREEN."Level:  ".
			TextFormat::WHITE.$score["level"]);
	$c->sendMessage(TextFormat::GREEN."Kills:  ".
			TextFormat::WHITE.$score["kills"]);
	$c->sendMessage(TextFormat::GREEN."Points: ".
			TextFormat::WHITE.$money);
      }
    }
    return true;
  }
  private function cmdHelp(CommandSender $sender,$args) {
    $cmds = [ "stats" => ["[player ...]","Show player scores"] ];
    if (count($args)) {
      foreach ($args as $c) {
	if (isset($cmds[$c])) {
	  list($a,$b) = $cmds[$c];
	  $sender->sendMessage(TextFormat::RED."Usage: /runepvp $c $a"
			       .TextFormat::RESET);
	  $sender->sendMessage($b);
	} else {
	  $sender->sendMessage("Unknown command $c");
	}
      }
      return true;
    }
    $sender->sendMessage("RunePvP sub-commands");
    foreach ($cmds as $a => $b) {
      $sender->sendMessage("- ".TextFormat::GREEN."/runepvp ".$a.
			   TextFormat::RESET." ".$b[0]);
    }
    return true;
  }
  //////////////////////////////////////////////////////////////////////
  //
  // Economy/Money handlers
  //
  //////////////////////////////////////////////////////////////////////
  public function grantMoney($p,$money) {
    if(!$this->money) return false;
    switch($this->money->getName()){
    case "PocketMoney":
      $this->money->grantMoney($p, $money);
      break;
    case "EconomyAPI":
      $this->money->setMoney($p,$this->money->getMoney($p)+$money);
      break;
    case "MassiveEconomy":
      $this->money->payPlayer($p,$money);
      break;
    default:
      return false;
    }
    return true;
  }
  public function getMoney($player) {
    if(!$this->money) return false;
    switch($this->money->getName()){
    case "PocketMoney":
    case "MassiveEconomy":
      return $this->money->getMoney($player);
    case "EconomyAPI":
      return $this->money->mymoney($player);
    default:
      return false;
      break;
    }
  }
  //////////////////////////////////////////////////////////////////////
  //
  // Event handlers
  //
  //////////////////////////////////////////////////////////////////////
  public function onPlayerDeath(PlayerDeathEvent $e) {
    $pv = $e->getEntity();
    if ($pv->getLastDamageCause()->getCause() != EntityDamageEvent::CAUSE_ENTITY_ATTACK) return;
    if (!($pv instanceof Player)) return; // We don't really need this check!
    $pp = $pv->getLastDamageCause()->getDamager();
    if (!($pp instanceof Player)) return; // Not killed by player...

    $vic = $pv->getName();
    $perp = $pp->getName();

    $perp_score = $this->dbm->getScore($perp);

    //echo "VIC=$vic PERP=$perp\n";

    if ($perp_score == null) return; // Non players!
    if (++$perp_score["kills"] >= $perp_score["level"]*10) {
      $lv = ++$perp_score["level"];
      $this->grantMoney($perp,$this->points["level"]);
      $pp->sendMessage("[RunePvP] Congratulations! You are now level $lv!");
      $pp->sendMessage("You get ".$this->points["level"]." as a reward");
      $this->getServer()->broadcastMessage("[RunePvP] $perp is now level $lv");
    }
    if ($this->getMoney($vic) >= 100) {
      $this->grantMoney($vic,-$this->points["kills"]);
      $this->grantMoney($perp,$this->points["kills"]);
      $pv->sendMessage("[RunePvP] You lost ".$this->points["kills"]." points");
      $pv->sendMessage("[RunePvP] You now have ".$this->getMoney($vic).
		       " points");
      $pp->sendMessage("[RunePvP] You won ".$this->points["kills"]." points");
      $pp->sendMessage("[RunePvP] You now have ".$this->getMoney($perp).
		       " points");
    } else {
      $this->grantMoney($perp,intval($this->points["kills"]/2));
      $pv->sendMessage("[RunePvP] You did not loose any points!");
      $pp->sendMessage("[RunePvP] Opponent did not have enough points");
      $pp->sendMessage("[RunePvP] You now have ".$this->getMoney($perp).
		       " points");
    }
    $this->dbm->updateScore($perp,$perp_score["level"],$perp_score["kills"]);
  }

  public function onPlayerJoin(PlayerJoinEvent $e) {
    $pl = $e->getPlayer();
    if ($pl == null) return;
    $pn = $pl->getName();
    $scores = $this->dbm->getScore($pn);
    if ($scores == null) {
      // First time player
      $this->dbm->addScore($pn,1,0);
      $lv = 1;
    } else {
      $lv = $scores["level"];
    }
    $this->getServer()->broadcastMessage("[RunePvP] <Lv.$lv> $pn joined the game.");
  }
}

