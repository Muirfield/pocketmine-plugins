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
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\utils\Config;

class Main extends PluginBase implements CommandExecutor,Listener {
  protected $dbm;
  protected $hits = [];
  protected $points;

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
			     "#Credit: Cha0sRuin(alchemistdy@naver.com) for");
    $this->getLogger()->info(TextFormat::YELLOW.
			     "#    the original RuinPvP that this plugin");
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
    print_r($this->points);
  }
  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    switch($cmd->getName()) {
    case "runepvp":
      if (count($args) == 0) return $this->cmdStats($sender,[]);
      $scmd = strtolower(array_shift($args));
      switch ($scmd) {
      case "stats":
	return $this->cmdStats($sender,$args);
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
  private function cmdStats(CommandSender $c,$args) {
    if (count($args) == 0) {
      if (!$this->inGame($c)) return true;
      $args = [ $c->getName() ];
    }
    foreach ($args as $pl) {
      $score = $this->dbm->getScore($pl);
      if ($score == null) {
	$c->sendMessage("No scores found for $pl");
	continue;
      } else {
	$money = $this->getMoney($pl);
	if (count($args) != 1) $c->sendMessage(TextFormat::BLUE.$pl);
	$c->sendMessage(TextFormat::GREEN."Level: ".
			TextFormat::WHITE.$score["level"]);
	$c->sendMessage(TextFormat::GREEN."Kills: ".
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
  // PocketMoney handlers
  //
  //////////////////////////////////////////////////////////////////////
  public function grantMoney($player,$count) {
    return $this->getServer()->getPluginManager()->getPlugin("PocketMoney")->grantMoney($player,$count);
  }
  public function getMoney($player) {
    echo "GetMoney: $player\n";
    return $this->getServer()->getPluginManager()->getPlugin("PocketMoney")->getMoney($player);
  }
  //////////////////////////////////////////////////////////////////////
  //
  // Event handlers
  //
  //////////////////////////////////////////////////////////////////////
  public function onPlayerDeath(PlayerDeathEvent $e) {
    $pv = $e->getEntity();
    $vic = $pv->getName();
    if (!isset($this->hits[$vic])) return; // No recorded hit
    $perp = $this->hits[$vic];
    $perp_score = $this->dbm->getScore($perp);
    $pp = $this->getServer()->getPlayer($perp);

    echo "VIC=$vic PERP=$perp\n";

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
  public function onDamage(EntityDamageEvent $ev) {
    if(!($ev instanceof EntityDamageByEntityEvent)) return;
    if (!($ev->getEntity() instanceof Player)) return;
    $vic = $ev->getEntity()->getName();
    if (isset($this->hits[$vic])) unset($this->hits[$vic]);
    if (!($ev->getDamager() instanceof Player)) return;
    if ($ev->isCancelled()) return;
    $this->hits[$vic] = $ev->getDamager()->getName();
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

