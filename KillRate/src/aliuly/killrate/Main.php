<?php
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

use aliuly\killrate\common\mc;
use aliuly\killrate\common\mc2;
use aliuly\killrate\common\MPMU;
use aliuly\killrate\common\MoneyAPI;

use aliuly\killrate\api\KillRate as KillRateAPI;
use aliuly\killrate\api\KillRateScoreEvent;
use aliuly\killrate\api\KillRateResetEvent;

class Main extends PluginBase implements CommandExecutor,Listener {
	protected $dbm;
	public $api;

	protected $money;
	protected $signmgr;
	protected $achievements;
	protected $ranks;
	protected $kstreak;

	protected $settings;
	protected $prizes;

	//////////////////////////////////////////////////////////////////////
	//
	// Standard call-backs
	//
	//////////////////////////////////////////////////////////////////////
	public function onEnable(){
		$this->dbm = null;
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());

		mc2::plugin_init_alt($this,$this->getFile());

		$this->api = new KillRateAPI($this);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$defaults = [
			"version" => $this->getDescription()->getVersion(),
			//= cfg:features
			"features" => [
				"# signs" => "enable/disable signs",
				"signs" => true,
				"# ranks" => "Enable support for RankUp plugin",
				"ranks" => false,
				"# achievements" => "Enable PocketMine achievements",
				"achievements" => true,
				"# kill-streak" => "Enable kill-streak tracking.", // tracks the number of kills without dying
				"kill-streak" => false,
				"# rewards" => "award money.", // if true, money is awarded.  Requires an economy plugin
				"rewards" => true,
			],
			//= cfg:settings
			"settings" => [
				"# points" => "award points.", // if true points are awarded and tracked.
				"points" => true,
				"# min-kills" => "Minimum number of kills before declaring a kill-streak",
				"min-kills" => 7,
				"# reset-on-death" => "Reset counters on death.", // Set to false to disable, otherwise the number of deaths till reset. When the player dies X number of times, scores will reset.  (GAME OVER MAN!)
				"reset-on-death" => false,
				"# creative" => "track creative kills.", // if true, kills done by players in creative are scored
				"creative" => false,
				"# dynamic-updates" => "Update signs.", // Set to 0 or false to disable, otherwise sign update frequence in ticks
				"dynamic-updates" => 80,
				"# default-rank" => "Default rank (when resetting ranks)", // set to **false** to disable this feature
				"default-rank" => false,
			],
			//= cfg:values
			//:
			//: Configure awards for the different type of kills.  Format:
			//:
			//:     "entity": [ money, points ]
			//:
			//: The entity ( * ) is the default.
			"values" => [
				"<Example>" => [ "money" , "points" ],
				"*" => [ 1, 10 ],	// Default
				"Player" => [ 100, 100 ],
			],
			//= cfg:formats
			//: Sign formats used to show sign data.
			"formats" => [
				"default" => "{sname} {count}",
				"names" => "{n}.{player}",
				"scores" => "{count}",
			],
			//= cfg:database
			"database" => [
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
			],
			//= cfg:signs
			//: Placed signs text.
			//: These are used to configure sign texts.  Place signs with the
			//: words on the left, and the sign type (on the right) will be
			//: created
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
		$cfg = (new Config($this->getDataFolder()."config.yml",
										 Config::YAML,$defaults))->getAll();
		if (version_compare($cfg["version"],"2.1") < 0) {
			$this->getLogger()->warning(TextFormat::RED.mc::_("Configuration has been changed"));
			$this->getLogger()->warning(mc::_("It is recommended to delete old config.yml"));
		}

		$backend = __NAMESPACE__."\\".$cfg["database"]["backend"];
		$this->dbm = new $backend($this,$cfg["database"]);
		$this->getLogger()->info(mc::_("Using %1% as backend",$cfg["database"]["backend"]));

		$this->money = null;
		if (isset($cfg["features"]["rewards"])) {
			$this->money = MoneyAPI::moneyPlugin($this);
			if ($this->money) {
				MoneyAPI::foundMoney($this,$this->money);
			} else {
				MoneyAPI::noMoney($this);
				$this->money = null;
			}
		}
		$this->signmgr = $cfg["features"]["signs"] ? new SignMgr($this,$cfg) : null;

		$this->achievements = new AchievementsGiver($this,$cfg["features"]["achievements"]);
		$this->ranks = new RankMgr($this,$cfg["features"]["ranks"],$cfg["settings"]);
		$this->settings = $cfg["settings"];
		$this->prizes = $cfg["values"];
		$this->kstreak = new KillStreak($this,$cfg["features"]["kill-streak"],$cfg["settings"],$this->money);
	}
	public function onDisable() {
		if ($this->dbm !== null) $this->dbm->close();
		$this->dbm = null;
	}

	public function getMoneyPlugin() {
		return $this->money;
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
					case "give":
						if (!MPMU::access($sender,"killrate.cmd.give")) return true;
						if (count($args) == 1 || count($args) > 3) return false;
						if (count($args) == 2) {
							list($player,$points) = $args;
							$type = "points";
						} else {
							list($player,$points,$type) = $args;
						}
						if (!is_numeric($points)) return true;
						$player = $this->getServer()->getPlayer($player);
						if ($player == null) {
							$sender->sendMessage(TextFormat::RED.$args[0]." does not exist");
							return true;
						}
						$points = intval($points);
						$this->updateDb($player->getname(),$type,$points);
						$sender->sendMessage(TextFormat::GREEN.mc::_("Awarding %1% %2% to %3%", $points, $type,$player->getDisplayName() ));
						$player->sendMessage(TextFormat::YELLOW.mc::_("You have been awarded %1% %2% by %3%", $points, $type,$sender->getName() ));
						return true;
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
		if (isset($this->prizes[$vic])) {
			return $this->prizes[$vic];
		}
		if (isset($this->prizes["*"])) {
			return $this->prizes["*"];
		}
		return [0,0];
	}
	public function updateScores($player, $perp,$vic) {
		//echo "VIC=$vic PERP=$perp\n";//##DEBUG
		if ($this->settings["points"] || $this->money !== null){
			list($points,$money) = $this->getPrizes($vic);
			if (!$this->settings["points"]) $points = false;
			if (!$this->money !== null) $money = false;
		} else {
			list($points,$money) = [false,false];
		}
		$this->getServer()->getPluginManager()->callEvent(
				$ev = new KillRateScoreEvent($this,$player,$vic,$points,$money)
		);
		if ($ev->isCancelled()) return [false,false];
		if ($ev->getIncr())
			$kills = $this->updateDb($perp,$vic,$ev->getIncr());
		else
			$kills = null;
		$awards = [ false,false];
    $awards[0] = $points = $ev->getPoints();

		if ($points !== false && $points != 0)
			$newscore = $this->updateDb($perp,"points", $points);
		else
			$newscore = null;
		$awards[1] = $money = $ev->getMoney();
		if ($money !== false) MoneyAPI::grantMoney($this->money,$perp,$money);

		$this->achievements->awardKills($player,$vic, $kills);
		if ($newscore !== null) $this->ranks->promote($player,$newscore);

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
			if ($this->settings["reset-on-death"]
				 && $this->settings["reset-on-death"] > 0) {
				if ($deaths >= $this->settings["reset-on-death"]) {
					// We died too many times... reset scores...
					$this->getServer()->getPluginManager()->callEvent(
						$ev = new KillRateResetEvent($this,$pv)
					);
					if (!$ev->isCancelled()) {
						$this->delScore($pv->getName());
						$this->ranks->resetRank($pv);
						$pv->sendMessage(mc::_("GAME OVER!!!"));
						$this->getServer()->broadcastMessage(mc::n(
										mc::_("%1% died. RIP!", $pv->getDisplayName()),
										mc::_("%1% died %2% times. RIP!",  $pv->getDisplayName(), $deaths),
										$deaths));
					}
				}
			}
			$this->kstreak->endStreak($pv);
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
		if ($pp->isCreative() && !$this->settings["creative"]) return;

		$perp = $pp->getName();
		$vic = $pv->getName();
		if ($pv instanceof Player) {
			$vic = "Player";
			// OK killed a player... check for a kill streak...
			$pv->sendMessage(TextFormat::RED.mc::_("You were killed by %1%!",
																$pp->getName()));
			if ($this->kstreak->scoreStreak($pp)) {
				$this->achievements->awardSerialKiller($pp);
			}
		}
		$perp = $pp->getName();

		list ($points,$money) = $this->updateScores($pp,$perp,$vic);
		$this->announce($pp,$points,$money);
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
	public function setScore($pn,$val,$type = "points") {
		$score = $this->dbm->getScore($pn,$type);
		if ($score) {
			$this->dbm->updateScore($pn,$type,$val);
		}
		$this->dbm->insertScore($pn,$type,$val);
	}
	public function delScore($pn, $type = null) {
		//echo __METHOD__.",".__LINE__." pn=$pn\n";//##DEBUG
		$this->dbm->delScore($pn, $type);
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
