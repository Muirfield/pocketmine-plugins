<?php
namespace aliuly\killrate;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandExecutor;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;

use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\utils\Config;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\player\PlayerDeathEvent;

class Main extends PluginBase implements CommandExecutor,Listener {
	protected $dbm;
	protected $cfg;
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
		@mkdir($this->getDataFolder());
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->dbm = new DatabaseManager($this->getDataFolder()."stats.sqlite3");
		$defaults = [
			"settings" => [
				"points" => true,
				"rewards" => true,
				"creative" => false,
			],
			"values" => [
				"*" => [ 1, 10 ],	// Default
				"Player" => [ 100, 100 ],
			],
		];
		$this->cfg = (new Config($this->getDataFolder()."config.yml",
										 Config::YAML,$defaults))->getAll();

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
			case "killrate":
				if (count($args) == 0) return $this->cmdStats($sender,[]);
				$scmd = strtolower(array_shift($args));
				switch ($scmd) {
					case "stats":
						if (!$this->access($sender,"killrate.cmd.stats")) return true;
						return $this->cmdStats($sender,$args);
					case "top":
					case "ranking":
						if (!$this->access($sender,"killrate.cmd.rank")) return true;
						return $this->cmdTops($sender,$args);
					case "help":
						return $this->cmdHelp($sender,$args);
					default:
						$sender->sendMessage("Unknown command.  Try /killrate help");
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
	private function cmdHelp(CommandSender $sender,$args) {
		$cmds = [
			"stats" => ["[player ...]","Show player scores"]
		];
		if (count($args)) {
			foreach ($args as $c) {
				if (isset($cmds[$c])) {
					list($a,$b) = $cmds[$c];
					$sender->sendMessage(TextFormat::RED."Usage: /killrate $c $a"
												.TextFormat::RESET);
					$sender->sendMessage($b);
				} else {
					$sender->sendMessage("Unknown command $c");
				}
			}
			return true;
		}
		$sender->sendMessage("KillRate sub-commands");
		foreach ($cmds as $a => $b) {
			$sender->sendMessage("- ".TextFormat::GREEN."/killrate ".$a.
										TextFormat::RESET." ".$b[0]);
		}
		return true;
	}
	public function getRankings($limit=10,$online=false,$col = "points") {
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
	  return $this->dbm->getTops($limit,$plist,$col);
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
		$c->sendMessage(". Player Points");
		$i = 1;
		foreach ($res as $r) {
			$c->sendMessage(implode(" ",[$i++,$r["player"],$r["count"]]));
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
				if (!$this->access($c,"killrate.cmd.stats.other")) return true;
			}
			$score = $this->dbm->getScores($pl);
			if ($score == null) {
				$c->sendMessage("No scores found for $pl");
				continue;
			} else {
				if (count($args) != 1) $c->sendMessage(TextFormat::BLUE.$pl);
				foreach ($score as $row) {
					$c->sendMessage(TextFormat::GREEN.$row['type'].": ".
										 TextFormat::WHITE.$row['count']);
				}
			}
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
	public function updateDb($perp,$vic,$incr = 1) {
		$score = $this->dbm->getScore($perp,$vic);
		if ($score) {
			$this->dbm->updateScore($perp,$vic,$score["count"]+$incr);
		} else {
			$this->dbm->insertScore($perp,$vic,$incr);
		}
	}
	public function getPrizes($vic) {
		if (isset($this->cfg["values"][$vic])) {
			return $this->cfg["values"][$vic];
		}
		if (isset($this->cfg["values"]["*"])) {
			return $this->cfg["values"]["*"];
		}
		return [0,0];
	}
	public function updateScores($perp,$vic) {
		//echo "VIC=$vic PERP=$perp\n";
		$this->updateDb($perp,$vic);
		if (isset($this->cfg["settings"]["points"])) {
			// Add points...
			list($points,$money) = $this->getPrizes($vic);
			$this->updateDb($perp,"points",$points);
		}
		if (isset($this->cfg["settings"]["rewards"])) {
			// Add money...
			list($points,$money) = $this->getPrizes($vic);
			$this->grantMoney($perp,$money);
		}
	}
	public function onPlayerDeath(PlayerDeathEvent $e) {
		$pv = $e->getEntity();
		$cause = $pv->getLastDamageCause();
		// If we don't know the real cause, we can score it!
		if (!($cause instanceof EntityDamageEvent)) return;

		if ($cause->getCause() != EntityDamageEvent::CAUSE_ENTITY_ATTACK) return;
		if (!($pv instanceof Player)) return; // We don't really need this check!
		$pp = $cause->getDamager();
		if (!($pp instanceof Player)) return; // Not killed by player...
		// No scoring for creative players...
		if ($pp->isCreative() && !isset($this->cfg["settings"]["creative"])) return;
		$perp = $pp->getName();
		$this->updateScores($perp,"Player");
	}
	public function onDeath(EntityDeathEvent $e) {
		$pv = $e->getEntity();
		$cause = $pv->getLastDamageCause();
		// If we don't know the real cause, we can't score it!
		if (!($cause instanceof EntityDamageEvent)) return;

		if ($cause->getCause() != EntityDamageEvent::CAUSE_ENTITY_ATTACK) return;

		$pp = $cause->getDamager();
		if (!($pp instanceof Player)) return; // Not killed by player...
		$vic = $pv->getName();
		if ($pv instanceof Player) {
			$vic = "Player";
		}
		$perp = $pp->getName();
		// No scoring for creative players...
		if ($pp->isCreative() && !isset($this->cfg["settings"]["creative"])) return;
		$pp->sendMessage("Killed $vic!");
		$this->updateScores($perp,$vic);
	}

}
