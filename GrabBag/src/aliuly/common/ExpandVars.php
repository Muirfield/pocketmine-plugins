<?php
namespace aliuly\common;

use pocketmine\plugin\Plugin;
use pocketmine\Player;

use aliuly\common\ItemName;
use aliuly\common\MPMU;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use pocketmine\item\Item;

/**
 * Common variable expansion.  You can use this for custom messages and/or
 * custom commands.
 *
 * Plugins can extend this infrastructure by declaring the following functions:
 *
 * public function getSysVarsV1(array &$vars);
 *
 * public function getPlayerVarsV1(Player $player, array &$vars);
 *
 * Otherwise they can call the functions: registerSysVars or registerPlayerVars.
 *
 */
class ExpandVars {
  /** @const str getSysVarsFn This is the function signature for SysVars */
  const getSysVarsFn = "getSysVarsV1";
  /** @const str getPlayerVarsFn This is the function signature for PlayerVars */
  const getPlayerVarsFn = "getPlayerVarsV1";

  /** @var callable[] Callables to create player specific variables */
  protected $playerExtensions;
  /** @var callable[] Callables to create server wide variables */
  protected $sysExtensions;
  /** @var array API table */
  protected $apitable;
  /** @var str[] static _constants_ */
  protected $consts;
  /** @var Server pocketmine server context */
  protected $owner;
  /**
   * @param Server $server - server context
   */
  public function __construct(Plugin $owner) {
    $this->owner = $owner;
    $this->playerExtensions = null;
    $this->sysExtensions = null;
    $this->apitable = [];
    $this->consts = [
      "{NL}" => "\n",
      "{BLACK}" => TextFormat::BLACK,
      "{DARK_BLUE}" => TextFormat::DARK_BLUE,
      "{DARK_GREEN}" => TextFormat::DARK_GREEN,
      "{DARK_AQUA}" => TextFormat::DARK_AQUA,
      "{DARK_RED}" => TextFormat::DARK_RED,
      "{DARK_PURPLE}" => TextFormat::DARK_PURPLE,
      "{GOLD}" => TextFormat::GOLD,
      "{GRAY}" => TextFormat::GRAY,
      "{DARK_GRAY}" => TextFormat::DARK_GRAY,
      "{BLUE}" => TextFormat::BLUE,
      "{GREEN}" => TextFormat::GREEN,
      "{AQUA}" => TextFormat::AQUA,
      "{RED}" => TextFormat::RED,
      "{LIGHT_PURPLE}" => TextFormat::LIGHT_PURPLE,
      "{YELLOW}" => TextFormat::YELLOW,
      "{WHITE}" => TextFormat::WHITE,
      "{OBFUSCATED}" => TextFormat::OBFUSCATED,
      "{BOLD}" => TextFormat::BOLD,
      "{STRIKETHROUGH}" => TextFormat::STRIKETHROUGH,
      "{UNDERLINE}" => TextFormat::UNDERLINE,
      "{ITALIC}" => TextFormat::ITALIC,
      "{RESET}" => TextFormat::RESET,
      "{10SPACE}" => str_repeat(" ",10),
      "{20SPACE}" => str_repeat(" ",20),
      "{30SPACE}" => str_repeat(" ",30),
      "{40SPACE}" => str_repeat(" ",40),
      "{50SPACE}" => str_repeat(" ",50),
      "{MOTD}" => $owner->getServer()->getMotd(),
    ];
  }
  /**
   * If GrabBag is available, try to get a single shared instance of
   * ExpandVars
   */
  static public function getCommonVars(Plugin $owner) {
    $pm = $owner->getServer()->getPluginManager();
    if (($gb = $pm->getPlugin("GrabBag")) !== null) {
      if ($gb->isEnabled() && MPMU::apiCheck($gb->getDescription()->getVersion(),"2.3")) {
        $vars =  $gb->api->getVars();
        if ($vars instanceof ExpandVars) return $vars;
      }
    }
    return new ExpandVars($owner);
  }


  /**
   * Define additional constants on the fly...
   * @param str $name
   * @param str $value
   */
  public function define($str,$value) {
    $this->consts[$str] = $value;
  }
  public function getServer() {
    return $this->owner->getServer();
  }
  public function getConsts() {
    return $this->consts;
  }
  /**
   * Register API
   * @param str $apiname - API id
   * @param mixed $ptr - API object
   */
  public function registerApi($apiname,$ptr) {
    $this->apitable[$apiname] = $ptr;
  }
  /**
   * Return API entry, if not found it will throw a RuntimeException
   * @param str $apiname - API id
   * @param bool $exception - if true raise exemption on error
   * @return mixed
   */
  public function api($apiname,$exception = true) {
    if (isset($this->apitable[$apiname])) return $this->apitable[$apiname];
    throw new \RuntimeException("Missing API ".$apiname);
  }
  /**
   * Scan loaded plugins and identifies which plugins have an entry
   * point to variable expansions...
   */
   protected function autoloadExtensions($mode) {
    $tab = [];
    foreach ($this->getServer()->getPluginManager()->getPlugins() as $plug) {
      if (!$plug->isEnabled()) continue;
      $cb = [ $plug, $mode ];
      if (is_callable($cb)) $tab[] = $cb;
    }
    return $tab;
  }

  ///////////////////////////////////////////////////////////////////////////
  // System variables
  ///////////////////////////////////////////////////////////////////////////

  /**
   * Used to initialize the system wide variables table
   */
   protected function initSysVars() {
    if ($this->sysExtensions !== null) return;
    $this->sysExtensions = $this->autoloadExtensions(self::getSysVarsFn);
    $this->sysExtensions[] =  [ $this, "stdSysVars" ];
    if (\pocketmine\DEBUG > 1)  $this->sysExtensions[] = [ $this, "debugSysVars"];
    $pm = $this->getServer()->getPluginManager();
    if (($kr = $pm->getPlugin("KillRate")) !== null) {
      if (MPMU::apiCheck($kr->getDescription()->getVersion(),"1.1")) {
        $this->registerApi("KillRate-1.1",$kr);
        $this->sysExtensions[] = [ $this , "kr1SysVars" ];
      }
    }
  }

  /**
   * Register a callback function that define system wide variable expansions
   * @param Server $server - reference to pocketmine server
   * @param callable $fn - callable should have as argumens (Server $server, array &$vars)
   */
  public function registerSysVars(callable $fn) {
    $this->initSysVars();
    $this->sysExtensions[] = $fn;
  }

  /**
   * Main entry point for system wide variable defintions
   * @param array &$vars - receives variable defintions
   */
  public function sysVars(array &$vars) {
    $this->initSysVars();
    foreach ($this->sysExtensions as $cb) {
      $cb($vars);
    }
  }
  /**
   * Shorter entry point for system wide variable defintions
   * @param array &$vars - receives variable defintions
   */
  public function sysVarsShort(array &$vars) {
    foreach ($this->sysExtensions as $cb) {
      $cb($vars);
    }
  }

  ///////////////////////////////////////////////////////////////////////////
  // System variable definitions
  ///////////////////////////////////////////////////////////////////////////

  /**
   * Basic system wide variable definitions
   * @param array &$vars - variables
   */
  public function stdSysVars(array &$vars) {
    foreach ([
              "{tps}" => $this->getServer()->getTicksPerSecond(),
              "{tickUsage}" => $this->getServer()->getTickUsage(),
              "{numPlayers}" => count($this->getServer()->getOnlinePlayers()),
    ] as $a => $b) {
      $vars[$a] = $b;
    }
  }
  /**
   * @param array &$vars - variables
   */
  public function debugSysVars(&$vars) {
    $server = $this->getServer();
    // Enable debugging variables...
    $time = floor(microtime(true) - \pocketmine\START_TIME);
    $uptime = "";
    $q = "";
    foreach ([
      [ "sec", 60, "secs"],
      [ "min", 60,  "mins"],
      [ "hour", 24, "hours"],
      [ "day", 0, "days"],
    ] as $f) {
        if ($f[1]) {
          $e = floor($time % $f[1]);
          $time = floor($time / $f[1]);
        } else {
          $e = $time;
          $time = 0;
        }
        if ($e) {
          $r = $e == 1 ? $f[0] : $f[2];
          $uptime = $e." ".$r . $q . $uptime;
          $q = ", ";
        }
        if ($time == 0) break;
    }
    $vars["{uptime}"] = $uptime;
    $vars["{netup}"] = round($server->getNetwork()->getUpload()/1024,2);
    $vars["{netdown}"] = round($server->getNetwork()->getUpload()/1024,2);
    $vars["{threads}"] = Utils::getThreadCount();
    $mUsage = Utils::getMemoryUsage(true);
    $vars["{mainmem}"] = number_format(round($mUsage[0]/1024)/1024,2 );
    $vars["{memuse}"] = number_format(round($mUsage[1]/1024)/1024,2 );
    $vars["{maxmem}"] = number_format(round($mUsage[2]/1024)/1024,2 );
    $rUsage = Utils::getRealMemoryUsage();
    $vars["{heapmem}"] = number_format(round($rUsage[0]/1024)/1024,2 );
  }
  /**
   * KillRate v1.1 sysvars compatibility
   * @param array &$vars - variables
   */
  public function kr1SysVars(array &$vars) {
    $ranks = $this->api("killrate-1.1")->getRankings(10);
    if ($ranks == null) {
      $vars["{tops}"] = "N/A";
      $vars["{top10}"] = "N/A";
    } else {
      $vars["{tops}"] = "";
      $vars["{top10}"] = "";
      $i = 1; $q = "";
      foreach ($ranks as $r) {
        if ($i <= 3) {
          $vars["{tops}"] .= $q.$i.". ".substr($r["player"],0,8).
                   " ".$r["count"];
          $q = "   ";
        }
        $vars["{top10}"] .= $i.". ".$r["player"]." ".$r["count"]."\n";
        ++$i;
      }
    }

  }

  ///////////////////////////////////////////////////////////////////////////
  // Player variables
  ///////////////////////////////////////////////////////////////////////////

  /**
   * Used to initialize the player specific variables table
   */
  protected function initPlayerVars() {
    if ($this->$playerExtensions !== null) return;
    $this->playerExtensions = $this->autoloadExtensions(self::getPlayerVarsFn);
    $this->playerExtensions[] = [ $this , "stdPlayerVars" ];
    $this->playerExtensions[] = [ $this , "invPlayerVars" ];
    $pm = $this->getServer()->getPluginManager();
    if (($kr = $pm->getPlugin("KillRate")) !== null) {
      if (MPMU::apiCheck($kr->getDescription()->getVersion(),"1.1")) {
        $this->registerApi("KillRate-1.1",$kr);
        $this->playerExtensions[] = [ $this , "kr1PlayerVars" ];
      }
    }
    if (($pp = $pm->getPlugin("PurePerms")) !== null) {
      $this->registerApi("PurePerms",$pp);
      $this->playerExtensions[] = [ $this , "purePermsPlayerVars" ];
    }
    if (($ru = $pm->getPlugin("RankUp")) !== null) {
      $this->registerApi("RankUp",$ru);
      $this->playerExtensions[] = [ $this , "rankUpPlayerVars" ];
    }
    if (($mm = $pm->getPlugin("GoldStd")) !== null) {
      $this->registerApi("money", $mm);
      $this->playerExtensions[] = [ $this ,"moneyPlayerVarsGoldStd" ];
    } elseif (($mm = $pm->getPlugin("PocketMoney")) !== null) {
      $this->registerApi("money", $mm);
      $this->playerExtensions[] = [ $this , "moneyPlayerVarsPocketMoney" ];
    } elseif (($mm = $pm->getPlugin("MassiveEconomy")) !== null) {
      $this->registerApi("money", $mm);
      $this->playerExtensions[] = [ $this , "moneyPlayerVarsMassiveEconomy" ];
    } elseif (($mm = $pm->getPlugin("EconomyAPI")) !== null) {
      $this->registerApi("money", $mm);
      $this->playerExtensions[] = [ $this, "moneyPlayerVarsEconomysApi" ];
    }
  }
  /**
   * Register a callback function that define player specific variable expansions
   * @param callable $fn - callable should have as argumens (Player $player, array &$vars)
   */
  public function registerPlayerVars(callable $fn) {
    $this->initPlayerVars();
    $this->playerExtensions[] = $fn;
  }
  /**
   * Main entry point for player specifc variable defintions
   * @param Player $player - reference to pocketmine Player
   * @param array &$vars - receives variable defintions
   */
  public function playerVars(Player $player, array &$vars) {
    $this->initPlayerVars();
    foreach ($this->playerExtensions as $cb) {
      $cb($this,$player,$vars);
    }
  }
  /**
   * Shorter entry point for player specifc variable defintions
   * @param Player $player - reference to pocketmine Player
   * @param array &$vars - receives variable defintions
   */
  public function playerVarsShort(Player $player, array &$vars) {
    foreach ($this->playerExtensions as $cb) {
      $cb($this,$player,$vars);
    }
  }
  ///////////////////////////////////////////////////////////////////////////
  // Player variable definitions
  ///////////////////////////////////////////////////////////////////////////
  /**
   * Basic player specific variable definitions
   * @param Player $player - reference to pocketmine Player
   * @param array &$vars - receives variable defintions
   */
  public function stdPlayerVars(Player $player,array &$vars) {
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
  /** Inventory related variables
   * @param Player $player - reference to pocketmine Player
   * @param array &$vars - receives variable defintions
   */
  public function invPlayerVars(Player $player,array &$vars) {
    $item = clone $player->getInventory()->getItemInHand();
    if ($item->getId() == Item::AIR) {
      $vars["{item}"] = "";
      $vars["{itemid}"] = "";
    } else {
      $vars["{item}"] = ItemName::str($item);
      $vars["{itemid}"] = $item->getId();
    }
  }

  /** KillRate-1.1 compatible player variables
   * @param Player $player - reference to pocketmine Player
   * @param array &$vars - receives variable defintions
   */
  public function kr1PlayerVars(Player $player,array &$vars) {
    $vars["{score}"] = $this->api("killrate-1.1")->getScore($player);
  }
  /** PocketMoney Support
   * @param Player $player - reference to pocketmine Player
   * @param array &$vars - receives variable defintions
   */
  public function moneyPlayerVarsPocketMoney(Player $player,array &$vars) {
    $vars["{money}"] = $$this->api("money")->getMoney($player->getName());
  }
  /** MassiveEconomy Support
   * @param Player $player - reference to pocketmine Player
   * @param array &$vars - receives variable defintions
   */
  public function moneyPlayerVarsMassiveEconomy(Player $player,array &$vars) {
    $vars["{money}"] = $this->api("money")->getMoney($player->getName());
  }
  /** EconomysAPI Support
   * @param Player $player - reference to pocketmine Player
   * @param array &$vars - receives variable defintions
   */
  public function moneyPlayerVarsEconomysApi(Player $player,array &$vars) {
    $vars["{money}"] = $this->api("money")->mymoney($player->getName());
  }
  /** GoldStd Support
   * @param Player $player - reference to pocketmine Player
   * @param array &$vars - receives variable defintions
   */
  public function moneyPlayerVarsGoldStd(Player $player,array &$vars) {
    $vars["{money}"] = $this->api("money")->getMoney($player);
  }
  /** PurePerms compatibility
   * @param Player $player - reference to pocketmine Player
   * @param array &$vars - receives variable defintions
   */
  public function purePermsPlayerVars(Player $player,array &$vars) {
    $vars["{group}"] = $this->api("PurePerms")->getUser($player)->getGroup()->getName();
  }

  /** RankUp compatibility
   * @param Player $player - reference to pocketmine Player
   * @param array &$vars - receives variable defintions
   */
  public function rankUpPlayerVars(Player $player,array &$vars) {
    $vars["{rank}"] = $this->api("RankUp")->getPermManager()->getGroup();
  }
  ///////////////////////////////////////////////////////////////////////////
  // Misc Support functions
  ///////////////////////////////////////////////////////////////////////////
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

}
