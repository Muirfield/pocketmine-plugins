<?php
/**
 ** CONFIG:config.yml
 **/
namespace aliuly\killrate;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandExecutor;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\utils\Config;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\entity\Projectile;

use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\block\Block;
use pocketmine\tile\Sign;
use pocketmine\network\protocol\EntityDataPacket;
use pocketmine\network\protocol\TileEntityDataPacket;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\String;
use pocketmine\Achievement;

use aliuly\killrate\common\mc;
use aliuly\killrate\common\MPMU;
use aliuly\killrate\common\PluginCallbackTask;
use aliuly\killrate\common\MoneyAPI;

use aliuly\killrate\api\KillRate as KillRateAPI;
use aliuly\killrate\api\KillRateScoreEvent;
use aliuly\killrate\api\KillRateResetEvent;


class Main extends PluginBase implements CommandExecutor,Listener {
	protected $dbm;
	protected $cfg;
	protected $money;
	protected $stats;
	public $api;

	//////////////////////////////////////////////////////////////////////
	//
	// Standard call-backs
	//
	//////////////////////////////////////////////////////////////////////
	public function onEnable(){
		$this->dbm = null;
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		if (mc::plugin_init($this,$this->getFile()) === false) {
			file_put_contents($this->getDataFolder()."messages.ini",MPMU::getResourceContents($this,"messages/eng.ini")."\n\"<nagme>\"=\"yes\"\n");
			mc::plugin_init($this,$this->getFile());
			$this->getLogger()->error(TextFormat::RED."Your selected language \"".$this->getServer()->getProperty("settings.language")."\" is not supported");
			$this->getLogger()->error(TextFormat::YELLOW."Creating a custom \"messages.ini\" with empty strings");
			$this->getLogger()->error(TextFormat::AQUA."Please consider translating it and submitting a");
			$this->getLogger()->error(TextFormat::AQUA."translation to the developer");
		} else {
			if (mc::_("<nagme>") === "yes") {
				$this->getLogger()->error(TextFormat::RED."Your selected language \"".$this->getServer()->getProperty("settings.language")."\" is not supported");
				$this->getLogger()->error(TextFormat::AQUA."Please consider translating \"messages.ini\"");
				$this->getLogger()->error(TextFormat::AQUA."and submitting a translation to the  developer");
			}
		}
		$this->api = new KillRateAPI($this);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$defaults = [
			"version" => $this->getDescription()->getVersion(),
			"# settings" => "Configuration settings",
			"settings" => [
				"# points" => "award points.", // if true points are awarded and tracked.
				"points" => true,
				"# rewards" => "award money.", // if true, money is awarded.  Requires an economy plugin
				"rewards" => true,
				"# creative" => "track creative kills.", // if true, kills done by players in creative are scored
				"creative" => false,
				"# dynamic-updates" => "Update signs.", // Set to 0 or false to disable, otherwise sign update frequence in ticks
				"dynamic-updates" => 80,
				"# reset-on-death" => "Reset counters on death.", // set to **false** or to a number.  When the player dies that number of times, scores will reset.  (GAME OVER MAN!)
				"reset-on-death" => false,
				"# kill-streak" => "Enable kill-streak tracking.", // "set to **false** or to a number.  Will show the kill streak of a player once the number of kills before dying reaches number
				"kill-streak" => false,
				"# achievements" => "Enable PocketMine achievements",
				"achievements" => true,
			],
			"# values" => "configure awards. (1st.money, 2nd.points)", // Configures how many points or how much money is awarded per kill type.  The first number is points, the second is money.  You can use negative values.
			"values" => [
				"*" => [ 1, 10 ],	// Default
				"Player" => [ 100, 100 ],
			],
			"# formats" => "Sign formats.", // Used to show sign data
			"formats" => [
				"default" => "{sname} {count}",
				"names" => "{n}.{player}",
				"scores" => "{count}",
			],
			"# backend" => "Use SQLiteMgr or MySqlMgr",
			"backend" => "SQLiteMgr",
			"# MySql" => "MySQL settings.", // Only used if backend is MySqlMgr to configure MySql settings
			"MySql" => [
				"host" => "localhost",
				"user" => "nobody",
				"password" => "secret",
				"database" => "KillRateDb",
				"port" => 3306,
			],
			"# signs" => "placed signs text.", // These are used to configure sign texts.  Place signs with the words on the left, and the sign type (on the right) will be created
			"signs" => [
				"[STATS]" => "stats",
				"[ONLINE TOPS]" => "online-tops",
				"[RANKINGS]" => "rankings",
				"[RANKNAMES]" => "rankings-names",
				"[RANKPOINTS]" => "rankings-points",
				"[TOPNAMES]" => "online-top-names",
				"[TOPPOINTS]" => "online-top-points",
			],
		];
		$this->cfg = (new Config($this->getDataFolder()."config.yml",
										 Config::YAML,$defaults))->getAll();
		if (version_compare($this->cfg["version"],"1.2.0") < 0) {
			$this->getLogger()->warning(TextFormat::RED.mc::_("Configuration has been changed"));
			$this->getLogger()->warning(mc::_("It is recommended to delete old config.yml"));
		}

		$backend = __NAMESPACE__."\\".$this->cfg["backend"];
		$this->dbm = new $backend($this);
		if ($this->cfg["backend"] != "SQLiteMgr") {
			$this->getLogger()->warning(TextFormat::RED.mc::_("Using %1% backend is untested",$this->cfg["backend"]));
			$this->getLogger()->warning(TextFormat::RED.mc::_("Please report bugs"));
		} else {
			$this->getLogger()->info(mc::_("Using %1% as backend",
													 $this->cfg["backend"]));
		}
		if (isset($this->cfg["settings"]["rewards"])) {
			$this->money = MoneyAPI::moneyPlugin($this);
			if ($this->money) {
				MoneyAPI::foundMoney($this,$this->money);
			} else {
				MoneyAPI::noMoney($this);
			}
		}
		if ($this->cfg["settings"]["dynamic-updates"]
			 && $this->cfg["settings"]["dynamic-updates"] > 0) {
			$this->getServer()->getScheduler()->scheduleRepeatingTask(new PluginCallbackTask($this,[$this,"updateTimer"],[]),$this->cfg["settings"]["dynamic-updates"]);
		}

		$this->stats = [];
		if ($this->cfg["settings"]["achievements"]) {
			Achievement::add("killer","First Blood!",[]);
			Achievement::add("serialKiller","Killer Streak!",["killer"]);
		}
	}
	public function onDisable() {
		if ($this->dbm !== null) $this->dbm->close();
		$this->dbm = null;
	}

	public function getCfg($key) {
		return $this->cfg[$key];
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		switch($cmd->getName()) {
			case "killrate":
				if (count($args) == 0) return $this->cmdStats($sender,[]);
				$scmd = strtolower(array_shift($args));
				switch ($scmd) {
					case "stats":
						if (!MPMU::access($sender,"killrate.cmd.stats")) return true;
						return $this->cmdStats($sender,$args);
					case "top":
					case "ranking":
						if (!MPMU::access($sender,"killrate.cmd.rank")) return true;
						return $this->cmdTops($sender,$args);
					case "help":
						return $this->cmdHelp($sender,$args);
					default:
						$sender->sendMessage(mc::_("Unknown command.  Try /killrate help"));
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
			"stats" => ["[player ...]",mc::_("Show player scores")],
			"top" => ["[online]",mc::_("Show top players")],
		];
		if (count($args)) {
			foreach ($args as $c) {
				if (isset($cmds[$c])) {
					list($a,$b) = $cmds[$c];
					$sender->sendMessage(TextFormat::RED.
												mc::_("Usage: /killrate %1% %2%",$c,$a).
												TextFormat::RESET);
					$sender->sendMessage($b);
				} else {
					$sender->sendMessage(mc::_("Unknown command %1%",$c));
				}
			}
			return true;
		}
		$sender->sendMessage(mc::_("KillRate sub-commands"));
		foreach ($cmds as $a => $b) {
			$sender->sendMessage("- ".TextFormat::GREEN."/killrate ".$a.
										TextFormat::RESET." ".$b[0]);
		}
		return true;
	}
	private function cmdTops(CommandSender $c,$args) {
		if (count($args) == 0) {
			$res = $this->getRankings(5);
		} else {
			$res = $this->getRankings(5,true);
			if ($res == null) {
				$c->sendMessage(mc::_("Not enough on-line players"));
				return true;
			}
		}
		$c->sendMessage(mc::_(". Player Points"));
		$i = 1;
		foreach ($res as $r) {
			$c->sendMessage(implode(" ",[$i++,$r["player"],$r["count"]]));
		}
		return true;
	}
	private function cmdStats(CommandSender $c,$args) {
		if (count($args) == 0) {
			if (!MPMU::inGame($c)) return true;
			$args = [ $c->getName() ];
		}
		foreach ($args as $pl) {
			if (MPMU::inGame($c,false) && $pl != $c->getName()) {
				if (!MPMU::access($c,"killrate.cmd.stats.other")) return true;
			}
			$score = $this->dbm->getScores($pl);
			if ($score == null) {
				$c->sendMessage(mc::_("No scores found for %1%",$pl));
				continue;
			} else {
				if (count($args) != 1) $c->sendMessage(TextFormat::BLUE.$pl);
				list($k,$d) = [null,null];
				foreach ($score as $row) {
					if ($row["type"] == "player") $k = (float)$row["count"];
					if ($row["type"] == "deaths") $d = $row["count"];
					$c->sendMessage(TextFormat::GREEN.$row['type'].": ".
										 TextFormat::WHITE.$row['count']);
				}
				if ($k !== null && $d !== null && $d > 0) {
					$c->sendMessage(TextFormat::GREEN.mc::_("kdratio: ").
										 TextFormat::WHITE.round($k/$d,2));
				}
			}
		}
		return true;
	}

	//////////////////////////////////////////////////////////////////////
	//
	// Event handlers
	//
	//////////////////////////////////////////////////////////////////////
	public function announce($pp,$points,$money) {
		if ($points) {
			if ($points > 0) {
				$pp->sendMessage(TextFormat::BLUE.mc::n(mc::_("one point awarded!"),
											  mc::_("%1% points awarded!",$points),
											  $points));
			} else {
				$pp->sendMessage(TextFormat::RED.mc::n(mc::_("one point deducted!"),
											  mc::_("%1% points deducted!",$points),
											  $points));
			}
		}
		if ($money) {
			if ($money > 0) {
				$pp->sendMessage(TextFormat::GREEN.mc::n(mc::_("You earn \$1"),
											  mc::_("You earn \$%1%", $money), $money));

			} else {
				$pp->sendMessage(TextFormat::YELLOW.mc::n(mc::_("You are fined \$1"),
											  mc::_("You are fined \$%1%",$money),
											  $money));
			}
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
	public function updateScores($player, $perp,$vic) {
		//echo "VIC=$vic PERP=$perp\n";//##DEBUG
		if ($this->cfg["settings"]["points"] || $this->cfg["settings"]["rewards"]){
			list($points,$money) = $this->getPrizes($vic);
			if (!$this->cfg["settings"]["points"]) $points = false;
			if (!$this->cfg["settings"]["rewards"]) $money = false;
		} else {
			list($points,$money) = [false,false];
		}
		$this->getServer()->getPluginManager()->callEvent(
				$ev = new KillRateScoreEvent($this,$player,$vic,$points,$money)
		);
		if ($ev->isCancelled()) return [false,false];
		if ($ev->getIncr()) $this->updateDb($perp,$vic,$ev->getIncr());
		$awards = [ false,false];
    $awards[0] = $points = $ev->getPoints();
		if ($points !== false && $points != 0) $this->updateDb($perp,"points", $points);
		$awards[1] = $money = $ev->getMoney();
		if ($money !== false) MoneyAPI::grantMoney($this->money,$perp,$money);
		return $awards;
	}
	/**
	 * @priority MONITOR
	 */
	public function onPlayerDeath(PlayerDeathEvent $e) {
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$this->deadDealer($e->getEntity());
	}
	/**
	 * @priority MONITOR
	 */
	public function onDeath(EntityDeathEvent $e) {
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$this->deadDealer($e->getEntity());
	}
	public function deadDealer($pv) {
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if ($pv instanceof Player) {
			// Score that this player died!
			//echo __METHOD__.",".__LINE__."\n";//##DEBUG
			$deaths = $this->updateDb($pv->getName(),"deaths");
			if ($this->cfg["settings"]["reset-on-death"]
				 && $this->cfg["settings"]["reset-on-death"] > 0) {
				if ($deaths >= $this->cfg["settings"]["reset-on-death"]) {
					// We died too many times... reset scores...
					$this->getServer()->getPluginManager()->callEvent(
						$ev = new KillRateResetEvent($this,$pv)
					);
					if (!$ev->isCancelled()) $this->delScore($pv);
				}
			}
			if ($this->cfg["settings"]["kill-streak"]) {
				$n = $pv->getName();
				$newstreak = $this->dbm->getScore($n,"streak");
				// Keep track of the best streak ever...
				if ($newstreak) {
					$newstreak = $newstreak["count"];
					$oldstreak = $this->dbm->getScore($n,"best-streak");
					if ($oldstreak) {
						$oldstreak = $oldstreak["count"];
						if ($newstreak > $oldstreak) {
							$this->dbm->updateScore($n,"best-streak",$newstreak);
							$this->getServer()->broadcastMessage(mc::_("%1% beat previous streak record of %2% at %3% kills", $n, $oldstreak, $newstreak));
						}
					} else {
						$this->dbm->insertScore($n,"best-streak",$newstreak);
						$this->getServer()->broadcastMessage(mc::_("%1% ended his kill-streak at %2% kills", $n, $newstreak));
					}
				}
				$this->dbm->delScore($n,"streak");
			}
		}
		$cause = $pv->getLastDamageCause();
		// If we don't know the real cause, we can score it!
		//echo __METHOD__.",".__LINE__."-".get_class($cause)."\n";//##DEBUG
		if (!($cause instanceof EntityDamageEvent)) return;
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG

		switch ($cause->getCause()) {
			case EntityDamageEvent::CAUSE_PROJECTILE:
				$pp = $cause->getDamager();
				//echo get_class($pp)." PROJECTILE\n";//##DEBUG
				break;
			case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
				$pp = $cause->getDamager();
				break;
			case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
				$pp = $cause->getDamager();
				if ($pp instanceof Projectile) {
					$pp = $pp->shootingEntity;
				}
				//echo get_class($pp)." EXPLOSION\n";//##DEBUG
				break;
			default:
				//echo "Cause: ".$cause->getCause()."\n";//##DEBUG
				return;
		}
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if (!($pp instanceof Player)) return; // Not killed by player...
		// No scoring for creative players...
		if ($pp->isCreative() && !$this->cfg["settings"]["creative"]) return;
		if ($this->cfg["settings"]["achievements"]) $pp->awardAchievement("killer");
		$perp = $pp->getName();
		$vic = $pv->getName();
		if ($pv instanceof Player) {
			$vic = "Player";
			// OK killed a player... check for a kill streak...
			$pv->sendMessage(TextFormat::RED.mc::_("You were killed by %1%!",
																$pp->getName()));
			if ($this->cfg["settings"]["kill-streak"]) {
				$streak = $this->updateDb($perp,"streak");
				if ($streak > $this->cfg["settings"]["kill-streak"]) {
					if ($this->cfg["settings"]["achievements"]) $pp->awardAchievement("serialKiller");
					$this->getServer()->broadcastMessage(TextFormat::YELLOW.mc::_("%1% has a %2% kill streak",$pp->getName(),$streak));
					if ($this->cfg["settings"]["rewards"]) {
						list($points,$money) = $this->getPrizes($vic);
						$pp->sendMessage(TextFormat::GREEN.
											  mc::_("You earn an additional $%1% for being in kill-streak!",$money));
						MoneyAPI::grantMoney($this->money,$perp,$money);
					}
				}
			}
		}
		$perp = $pp->getName();

		list ($points,$money) = $this->updateScores($pp,$perp,$vic);
		$this->announce($pp,$points,$money);
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Sign related functionality
	//
	//////////////////////////////////////////////////////////////////////
	public function playerTouchSign(PlayerInteractEvent $ev){
		if($ev->getBlock()->getId() != Block::SIGN_POST &&
			$ev->getBlock()->getId() != Block::WALL_SIGN) return;
		$tile = $ev->getPlayer()->getLevel()->getTile($ev->getBlock());
		if(!($tile instanceof Sign)) return;
		$sign = $tile->getText();
		if (!isset($this->cfg["signs"][$sign[0]])) return;
		$pl = $ev->getPlayer();
		if (!MPMU::access($pl,"killrate.signs.use")) return;
		$this->stats = [];
		$this->activateSign($pl,$tile);
	}
	public function placeSign(SignChangeEvent $ev){
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if($ev->getBlock()->getId() != Block::SIGN_POST &&
			$ev->getBlock()->getId() != Block::WALL_SIGN) return;
		$tile = $ev->getPlayer()->getLevel()->getTile($ev->getBlock());
		if(!($tile instanceof Sign)) return;
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$sign = $ev->getLines();
		if (!isset($this->cfg["signs"][$sign[0]])) return;
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$pl = $ev->getPlayer();
		if (!MPMU::access($pl,"killrate.signs.place")) {
			//echo __METHOD__.",".__LINE__."\n";//##DEBUG
			$l = $pl->getLevel();
			$l->setBlockIdAt($tile->getX(),$tile->getY(),$tile->getZ(),Block::AIR);
			$l->setBlockDataAt($tile->getX(),$tile->getY(),$tile->getZ(),0);
			$tile->close();
			return;
		}
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$pl->sendMessage(mc::_("Placed [KillRate] sign"));
		$this->stats = [];
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$this->getServer()->getScheduler()->scheduleDelayedTask(new PluginCallbackTask($this,[$this,"updateTimer"],[]),10);
	}
	public function updateTimer() {
		$this->stats = [];

		foreach ($this->getServer()->getLevels() as $lv) {
			if (count($lv->getPlayers()) == 0) continue;
			foreach ($lv->getTiles() as $tile) {
				if (!($tile instanceof Sign)) continue;
				$sign = $tile->getText();
				if (!isset($this->cfg["signs"][$sign[0]])) continue;
				foreach ($lv->getPlayers() as $pl) {
					$this->activateSign($pl,$tile);
				}
			}
		}
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
	}
	private function updateSign($pl,$tile,$text) {
		$pk = new TileEntityDataPacket();
		$data = $tile->getSpawnCompound();
		$data->Text1 = new String("Text1",$text[0]);
		$data->Text2 = new String("Text2",$text[1]);
		$data->Text3 = new String("Text3",$text[2]);
		$data->Text4 = new String("Text4",$text[3]);
		$nbt = new NBT(NBT::LITTLE_ENDIAN);
		$nbt->setData($data);

		$pk->x = $tile->getX();
		$pk->y = $tile->getY();
		$pk->z = $tile->getZ();
		$pk->namedtag = $nbt->write();
		$pl->dataPacket($pk);
	}
	public function activateSign($pl,$tile) {
		$sign = $tile->getText();
		$mode = $this->cfg["signs"][$sign[0]];
		switch ($mode) {
			case "stats":
				$name = $pl->getName();
				$text = ["","","",""];
				$text[0] = mc::_("Stats: %1%",$name);

				$l = 1;
				foreach (["Player"=>mc::_("Kills: "),
							 "points"=>mc::_("Points: ")] as $i=>$j) {
					$score = $this->dbm->getScore($name,$i);
					if ($score) {
						$score = $score["count"];
					} else {
						$score = "N/A";
					}
					$text[$l++] = $j.$score;
				}
				$text[$l++] = mc::_("Money: ").
					MoneyAPI::getMoney($this->money,$name);
				break;
			case "online-tops":
				$text = $this->topSign(true,"default",mc::_("Top Online"),$sign);
				break;
			case "rankings":
				$text = $this->topSign(false,"default",mc::_("Top Players"),$sign);
				break;
			case "rankings-names":
				$text = $this->topSign(false,"names",mc::_("Top Names"),$sign);
				break;
			case "rankings-points":
				$text = $this->topSign(false,"scores",mc::_("Top Scores"),$sign);
				break;
			case "online-top-names":
				$text = $this->topSign(true,"names",mc::_("On-line Names"),$sign);
				break;
			case "online-top-points":
				$text = $this->topSign(true,"scores",mc::_("On-line Scores"),$sign);
				break;
			default:
				return;

		}
		$this->updateSign($pl,$tile,$text);
	}

	protected function topSign($mode,$fmt,$title,$sign) {
		$col = "points";
		if ($sign[1] != "") $title = $sign[1];
		if ($sign[2] != "") $col = $sign[2];
		if ($sign[3] != "" && isset($this->cfg["formats"][$sign[3]])) {
			$fmt = $this->cfg["formats"][$sign[3]];
		} else {
			$fmt = $this->cfg["formats"][$fmt];
		}
		$text = ["","","",""];
		if ($title == "^^^") {
			$cnt = 4;
			$start = 0;
		} else {
			$text[0] = $title;
			$cnt = 3;
			$start = 1;
		}
		$res = $this->getRankings($cnt,$mode,$col);
		if ($res == null) {
			$text[2] = mc::_("NO STATS FOUND!");
		} else {
			$i = 1; $j = $start;
			foreach ($res as $r) {
				$tr = [
					"{player}" => $r["player"],
					"{count}" => $r["count"],
					"{sname}" => substr($r["player"],0,8),
					"{n}" => $i++,
				];
				$text[$j++] = strtr($fmt,$tr);
			}
		}
		return $text;
	}
	//////////////////////////////////////////////////////////////////////
	// API functions
	//////////////////////////////////////////////////////////////////////
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
	public function updateDb($perp,$vic,$incr = 1) {
		$score = $this->dbm->getScore($perp,$vic);
		if ($score) {
			$this->dbm->updateScore($perp,$vic,$score["count"]+$incr);
			return $score["count"]+$incr;
		}
		$this->dbm->insertScore($perp,$vic,$incr);
		return $incr;

	}
	/**
	 * @deprecated
	 */
	public function getScore($pn,$type = "points") {
		if ($pn instanceof Player) $pn = $pn->getName();
		$score = $this->dbm->getScore($pn,$type);
		if ($score) return $score["count"];
		return 0;
	}
	public function getScoreV2($pn,$type = "points") {
		$score = $this->dbm->getScore($pn,$type);
		if ($score) return $score["count"];
		return 0;
	}
	public function delScore($pn, $type = null) {
		$this->dbm->delScore($pl->getName(), $type);
	}
	public function getScores($pn) {
		return $this->dbm->getScores($pn);
	}
	public function getPlayerVarsV1(Player $player, array &$vars) {
		$vars["{score}"] = $this->getScore($player);
	}
	public function getSysVarsV1(array &$vars) {
		$ranks = $this->getRankings(10);
		if ($ranks == null) {
			$vars["{tops}"] = "N/A";
			$vars["{top10}"] = "N/A";
			$vars["{top10names}"] = "N/A";
		  $vars["{top10scores}"] = "N/A";
		} else {
			$vars["{tops}"] = "";
			$vars["{top10}"] = "";
			$vars["{top10names}"] = "";
		  $vars["{top10scores}"] = "";
			$i = 1; $q = "";
			foreach ($ranks as $r) {
				if ($i <= 3) {
					$vars["{tops}"] .= $q.$i.". ".substr($r["player"],0,8).
									" ".$r["count"];
					$q = "   ";
				}
				$vars["{top10}"] .= $i.". ".$r["player"]." ".$r["count"]."\n";
				$vars["{top10names}"] .= $r["player"]."\n";
			  $vars["{top10scores}"] .= $r["count"]."\n";
				++$i;
			}
		}
	}
}
