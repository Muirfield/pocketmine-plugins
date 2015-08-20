<?php
namespace aliuly\common;

use pocketmine\Server;
use pocketmine\Player;


//use pocketmine\item\Item;

/**
 * Common variable expansion.  You can use this for custom messages and/or
 * custom commands.
 *
 * Plugins can extend this infrastructure by declaring the following functions:
 *
 * static public function getSysVars(Server $server, array &$vars);
 *
 * static public function getPlayerVars(Player $player, array &$vars);
 *
 * Otherwise they can call the functions: registerSysVars or registerPlayerVars.
 *
 */
abstract class ExpandVars {
  /** @var callable[] Callables to create player specific variables */
  static protected $playerExtensions = null;
  /** @var callable[] Callables to create server wide variables */
  static protected $sysExtensions = null;

  /**
   * Convert bearings in degrees into points in compass
   * @param float $deg - yaw
   * @return str
   */
  static public function bearing($deg) {
    // Determine bearing
    if (22.5 <= $deg && $deg < 67.5) {
      return "NW";
    } elseif (67.5 <= $deg && $deg < 112.5) {
      return "N";
    } elseif (112.5 <= $deg && $deg < 157.5) {
      return "NE";
    } elseif (157.5 <= $deg && $deg < 202.5) {
      return "E";
    } elseif (202.5 <= $deg && $deg < 247.5) {
      return "SE";
    } elseif (247.5 <= $deg && $deg < 292.5) {
      return "S";
    } elseif (292.5 <= $deg && $deg < 337.5) {
      return "SW";
    } else {
      return "W";
    }
    return (int)$deg;
  }
  /**
   * Scan loaded plugins and identifies which plugins have an entry
   * point to variable expansions...
   */
  static protected function AutoloadExtensions($mode, Server $server) {
    $tab = [];
    foreach ($server->getPluginManager()->getPlugins() as $plug) {
      if (!$plug->isEnabled()) continue;
      $cb = [ $plug, $mode ];
      if (is_callable($cb)) $tab[] = $cb;
    }
    return $tab;
  }
  /**
   * Used to initialize the system wide variables table
   * @param Server $server - reference to PocketMine server
   */
  static protected function initSysVars(Server $server) {
    if (self::$sysExtensions !== null) return;
    self::$sysExtensions = self::AutoloadExtensions("getSysVars",$server);
    self::$sysExtensions[] = __CLASS__ ."\\stdSysVars";
    if (\pocketmine\DEBUG > 1) self::$sysExtensions[] = __CLASS__. "\\debugSysVars";
  }
  /**
   * Register a callback function that define system wide variable expansions
   * @param Server $server - reference to pocketmine server
   * @param callable $fn - callable should have as argumens (Server $server, array &$vars)
   */
  static public function registerSysVars(Server $server, callable $fn) {
    self::initSysVars($server);
    self::$sysExtensions[] = $fn;
  }
  /**
   * Main entry point for system wide variable defintions
   * @param Server $server - reference to pocketmine server
   * @param array &$vars - receives variable defintions
   */
  static public function sysVars(Server $server, array &$vars) {
    self::initSysVars($server);
    foreach (self::$sysExtensions as $cb) {
      $cb($server,$vars);
    }
  }
  /**
   * Basic system wide variable definitions
   * @param Server $server - pocketmine Server
   * @param array &$vars - variables
   */
  static public function stdSysVars(Server $server, array &$vars) {
    foreach ([
              "{tps}" => $server->getTicksPerSecond(),
              "{tickUsage}" => $server->getTickUsage(),
    ] as $a => $b) {
      $vars[$a] = $b;
    }
  }
  /**
   * @param Server $server - pocketmine Server
   * @param array &$vars - variables
   */
  static public function debugSysVars(Server $server, &$vars) {
    // Enable debugging variables...
    $time = microtime(true) - \pocketmine\START_TIME;
    $uptime = "";
    $q = "";
    foreach ([
      [ "sec", 60, 1, "secs"],
      [ "min", 60, 60, "mins"],
      [ "hour", 24, 60, "hours"],
      [ "day", 0, 24, "days"],
    ] as $f) {
        if ($f[1]) $e = floor($time % $f[1]);
        $time = floor($time / $f[2])
        if ($e) {
          $r = $e == 1 ? $f[0] : $f[3];
          $uptime = $e." ".$r . $q . $uptime;
          $q = ", ";
        }
    }
    $vars["{uptime}"] = $uptime;
    $vars["{netup}"] = round($server->getNetwork()->getUpload()/1024,2);
    $vars["{netdown}"] = round($server->getNetwork()->getUpload()/1024,2);
    $vars["{threads}"] = Utils::getThreadCount();
    $mUsage = Utils::getMemoryUsage(true);
    $vars["{mainmem}"] = number_format(round($mUsage[0]/1024)/1024,2 );
    $vars["{memuse}"] = number_format(round($mUsage[1]/1024)/1024,2 );
    $vars["{vmemuse}"] = number_format(round($mUsage[2]/1024)/1024,2 );
    $rUsage = Utils::getRealMemoryUsage();
    $vars["{heapmem}"] = number_format(round($rUsage[0]/1024)/1024,2 );
    $vars["{maxmem}"] = number_format(round($rUsage[2]/1024)/1024,2 );
  }
  /**
   * Used to initialize the player specific variables table
   * @param Server $server - reference to PocketMine server
   */
  static protected function initPlayerVars(Server $server) {
    if (self::$playerExtensions !== null) return;
    self::$playerExtensions = self::AutoloadExtensions("getPlayerVars",$server);
    self::$playerExtensions[] = __CLASS__ ."\\stdPlayerVars";
  }
  /**
   * Register a callback function that define player specific variable expansions
   * @param Server $server - reference to pocketmine server
   * @param callable $fn - callable should have as argumens (Player $player, array &$vars)
   */
  static public function registerPlayerVars(Server $server, callable $fn) {
    self::initPlayerVars($server);
    self::$playerExtensions[] = $fn;
  }
  /**
   * Main entry point for player specifc variable defintions
   * @param Player $player - reference to pocketmine Player
   * @param array &$vars - receives variable defintions
   */
  static public function playerVars(Player $player, array &$vars) {
    self::initPlayerVars($player->getServer());
    foreach (self::$playerExtensions as $cb) {
      $cb($player,$vars);
    }
  }
  /**
   * Basic player specific variable definitions
   * @param Player $player - reference to pocketmine Player
   * @param array &$vars - receives variable defintions
   */
  static public function stdPlayerVars(Player $player,array &$vars) {
    foreach ([
              "{player}" => $player->getName(),
              "{displayName}" => $player->getDisplayName(),
              "{world}" => $player->getLevel()->getName(),
              "{x}" => (int)$player->getX(),
              "{y}" => (int)$player->getY(),
              "{z}" => (int)$player->getZ(),
              "{yaw}" => (int)$player->getYaw(),
              "{pitch}" => (int)$player->getPitch(),
              "{bearing}" => self::bearing($player->getYaw()),
    ] as $a => $b) {
      $vars[$a] = $b;
    }
  }
  /**
   * @param Player $player - reference to pocketmine Player
   * @param array &$vars - receives variable defintions
   */
   static public function invPlayerVars(Player $player,array &$vars) {
     if (self::$krApi === false) return;
     if (self::$krApi === null) {
       self::$krApi = false; // Disable by default
       $kr = $player->getServer()->getPluginManager()->getPlugin("KillRate");

     }
   }
}



if (($kr = $pm->getPlugin("KillRate")) !== null) {
	if (version_compare($kr->getDescription()->getVersion(),"1.1") >= 0) {
		$vars["{score}"] = $kr->getScore($player);
		$ranks = $kr->getRankings(3);
		if ($ranks == null) {
			$vars["{tops}"] = "N/A";
		} else {
			$vars["{tops}"] = "";
			$i = 1; $q = "";
			foreach ($ranks as $r) {
				$vars["{tops}"] .= $q.($i++).". ".substr($r["player"],0,8).
									 " ".$r["count"];
				$q = "   ";
			}
		}
	}
}
if (($mm = $pm->getPlugin("PocketMoney")) !== null) {
	$vars["{money}"] = $mm->getMoney($player->getName());
} elseif (($mm = $pm->getPlugin("MassiveEconomy")) !== null) {
	$vars["{money}"] = $mm->getMoney($player->getName());
} elseif (($mm = $pm->getPlugin("EconomyAPI")) !== null) {
	$vars["{money}"] = $mm->mymoney($player->getName());
} elseif (($mm = $pm->getPlugin("GoldStd")) !== null) {
	$vars["{money}"] = $mm->getMoney($player);
}
