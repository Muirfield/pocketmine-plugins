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
use pocketmine\utils\Config;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\tile\Sign;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\network\protocol\EntityDataPacket;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\String;
use pocketmine\scheduler\CallbackTask;


class Main extends PluginBase implements CommandExecutor,Listener {
  protected $dbm;
  protected $points;
  protected $money;
  protected $texts;

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
		 "settings" => [
				"dynamic-updates" => 1,
				],
		 "points" => [
			      "kills" => 100,
			      "level" => 1000,
			      ],
		 "signs" => [
			     "stats" => ["[STATS]"],
			     "rankings" => ["[RANKINGS]"],
			     "onlineranks" => ["[ONLINE RANKS]"],
			     "shop" => ["[SHOP]"],
			     "casino" => ["[CASINO]"],
			     ],
		 ];

    $cfg = (new Config($this->getDataFolder()."config.yml",
		       Config::YAML,$defaults))->getAll();
    $this->points = $cfg["points"];
    if (isset($cfg["settings"]["dynamic-updates"])) {
      $this->getLogger()->info("dynamic-updates: ON");
      $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this,"updateTimer"],[]),40);
    }

    // Configure texts
    foreach ($cfg["signs"] as $sn => $tab) {
      foreach ($tab as $z) {
	$this->texts[$z] = $sn;
      }
    }

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
    //print_r([$limit,$plist]);
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
  // Manage signs
  //////////////////////////////////////////////////////////////////////
  private function parseItemLine($txt) {
    $txt = preg_split('/\s+/',$txt);
    if (count($txt) == 0) return null;
    $cnt = $txt[count($txt)-1];
    if (preg_match('/^x(\d+)$/',$cnt,$mv)) {
      $cnt = $mv[1];
      array_pop($txt);
    } else {
      $cnt = 1;
    }
    $item = Item::fromString(implode("_",$txt));
    if ($item->getId() == 0) return null;
    $item->setCount($cnt);
    return $item;
  }
  private function parsePriceLine($txt) {
    $n = intval(preg_replace('/[^0-9]/', '', $txt));
    if ($n == 0) return null;
    return $n;
  }
  private function parseCasinoLine($txt) {
    $txt = preg_replace('/^\s*odds:\s*/i','',$txt);
    $txt = preg_split('/\s*:\s*/',$txt);
    if (count($txt) != 2) return [null,null];
    list($odds,$payout) = $txt;
    $payout = $this->parsePriceLine($payout);
    if ($payout === null) return [null,null];
    return [$this->parsePriceLine($odds),$payout];
  }
  private function updateTile($tile) {
    $sign = $tile->getText();
    $sn = $this->texts[$sign[0]];
    $upd = [ $sign[0], $sign[1], $sign[2], $sign[3] ];
    switch ($sn) {
    case "stats":
      $lv = $tile->getLevel();
      foreach ($lv->getPlayers() as $pl) {
	$score = $this->dbm->getScore($pl->getName());
	if ($score == null) continue;
	$money = $this->getMoney($pl->getName());
	$data = $tile->getSpawnCompound();
	$data->Text1 = new String("Text1",$sign[0]);
	$data->Text2 = new String("Text2","Level: ".$score["level"]);
	$data->Text3 = new String("Text3","Kills: ".$score["kills"]);
	$data->Text4 = new String("Text4","Points: ".$money);
	$nbt = new NBT(NBT::LITTLE_ENDIAN);
	$nbt->setData($data);
	$pk = new EntityDataPacket();
	$pk->x = $tile->getX();
	$pk->y = $tile->getY();
	$pk->z = $tile->getZ();
	$pk->namedtag = $nbt->write();
	$pl->dataPacket($pk);
      }
      break;
    case "rankings":
    case "onlineranks":
      $res = $this->getRankings(3, $sn == "onlineranks");
      if ($res == null) {
	$upd[1] = "Not Available";
	$upd[2] = "insufficient";
	$upd[3] = "players on-line";
	break;
      }
      $upd[1] = "";
      $upd[2] = "";
      $upd[3] = "";
      $i =1;
      foreach ($res as $r) {
	$upd[$i] = implode(" ",[$r["player"],$r["kills"]]);
	++$i;
      }
      break;
    default:
      return;
    }
    if ($upd[0] == $sign[0] && $upd[2] == $sign[2] &&
	$upd[1] == $sign[1] && $upd[3] == $sign[3]) return;
    $tile->setText($upd[0],$upd[1],$upd[2],$upd[3]);
  }

  private function activateSign($pl,$tile) {
    $sign = $tile->getText();
    if (!$this->access($pl,"runepvp.signs.use")) return;
    $sn = $this->texts[$sign[0]];
    if (!$this->access($pl,"runepvp.signs.use.".$sn)) return;
    switch ($sn) {
    case "stats":
      $this->updateTile($tile);
      break;
    case "rankings":
      $this->updateTile($tile);
      $this->cmdTops($pl,[]);
      break;
    case "onlineranks":
      $this->updateTile($tile);
      $this->cmdTops($pl,["online"]);
      break;
    case "shop":
      $item = $this->parseItemLine($sign[1]);
      if ($item === null) {
	$pl->sendMessage("Invalid item line");
	return false;
      }
      $price = $this->parsePriceLine($sign[2]);
      if ($price === null) {
	$pl->sendMessage("Invalid price line");
	return false;
      }
      $money = $this->getMoney($pl->getName());
      if ($money < $price) {
	$pl->sendMessage("[RunePvP] You do not have enough points");
      } else {
	$this->grantMoney($pl->getName(),-$price);
	$pl->getInventory()->addItem(clone $item);
	$pl->sendMessage("[RunePvP] Item purchased");
      }
      break;
    case "casino":
      list($odds,$payout) = $this->parseCasinoLine($sign[1]);
      if ($odds === null) {
	$pl->sendMessage("Invalid odds line");
	return false;
      }
      $price = $this->parsePriceLine($sign[2]);
      if ($price === null) {
	$pl->sendMessage("Invalid price line");
	return false;
      }
      $money = $this->getMoney($pl->getName());
      if ($money < $price) {
	$pl->sendMessage("[RunePvP] You do not have enough points");
      } else {
	$pl->sendMessage("[RunePvP] Betting $price...");
	$this->grantMoney($pl->getName(),-$price);
	$rand = mt_rand(0,$odds);
	if ($rand == 1) {
	  $pl->sendMessage("[RunePvP] You WON!!! prize...".$payout);
	  $this->grantMoney($pl->getName(),$payout);
	} else {
	  $pl->sendMessage("[RunePvP] BooooM!!! You lost");
	}
      }
      break;
    }
    return true;
  }

  private function validateSign($pl,$sign) {
    if (!$this->access($pl,"runepvp.signs.place")) return false;
    $sn = $this->texts[$sign[0]];
    if (!$this->access($pl,"runepvp.signs.place.".$sn)) return false;
    switch ($sn) {
    case "shop":
      $item = $this->parseItemLine($sign[1]);
      if ($item === null) {
	$pl->sendMessage("Invalid item line");
	return false;
      }
      $price = $this->parsePriceLine($sign[2]);
      if ($price === null) {
	$pl->sendMessage("Invalid price line");
	return false;
      }
      break;
    case "casino":
      list($odds,$payout) = $this->parseCasinoLine($sign[1]);
      if ($odds === null) {
	$pl->sendMessage("Invalid odds line");
	return false;
      }
      $price = $this->parsePriceLine($sign[2]);
      if ($price === null) {
	$pl->sendMessage("Invalid price line");
	return false;
      }
      break;
    }
    return true;
  }


  //////////////////////////////////////////////////////////////////////
  //
  // Event handlers
  //
  //////////////////////////////////////////////////////////////////////
  public function onPlayerDeath(PlayerDeathEvent $e) {
    $pv = $e->getEntity();
	 $cause = $pv->getLastDamageCause();
	 // If we don't know the real cause, we can score it!
	 if (!($cause instanceof EntityDamageEvent)) return;

    if ($cause->getCause() != EntityDamageEvent::CAUSE_ENTITY_ATTACK) return;
    if (!($pv instanceof Player)) return; // We don't really need this check!
    $pp = $cause->getDamager();
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

  // Sign functionality
  public function placeSign(SignChangeEvent $ev){
    if($ev->getBlock()->getId() != Block::SIGN_POST &&
       $ev->getBlock()->getId() != Block::WALL_SIGN) return;
    $sign = $ev->getPlayer()->getLevel()->getTile($ev->getBlock());
    if(!($sign instanceof Sign)) return;

    $sign = $ev->getLines();
    if (!isset($this->texts[$sign[0]])) return;
    if (!$this->validateSign($ev->getPlayer(),$sign)) {
      $ev->setLine(0,"[BROKEN]");
      return;
    }
    $ev->getPlayer()->sendMessage("[RunePvP] placed sign");
  }

  public function playerTouchSign(PlayerInteractEvent $ev){
    if($ev->getBlock()->getId() != Block::SIGN_POST &&
       $ev->getBlock()->getId() != Block::WALL_SIGN) return;
    //echo "TOUCHED\n";
    $sign = $ev->getPlayer()->getLevel()->getTile($ev->getBlock());
    if(!($sign instanceof Sign)) return;
    //echo __METHOD__.",".__LINE__."\n";
    $lines = $sign->getText();
    //print_r($lines);
    //print_r($this->texts);
    if (!isset($this->texts[$lines[0]])) return;
    //echo __METHOD__.",".__LINE__."\n";
    $this->activateSign($ev->getPlayer(),$sign);
  }
  public function updateTimer() {
    foreach ($this->getServer()->getLevels() as $lv) {
      if (count($lv->getPlayers()) == 0) continue;
      foreach ($lv->getTiles() as $tile) {
	if (!($tile instanceof Sign)) continue;
	$sign = $tile->getText();
	if (!isset($this->texts[$sign[0]])) continue;
	$this->updateTile($tile);
      }
    }
  }
}
