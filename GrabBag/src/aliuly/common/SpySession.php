<?php

namespace aliuly\common;
use aliuly\common\Session;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\server\RemoteServerCommandEvent;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use aliuly\common\MPMU;

//use aliuly\common\MPMU;

/**
 * Spying/logging session
 */
class SpySession extends Session {
  const RCON = "*RCON*";
  const CONSOLE = "*CONSOLE*";
  protected $conlog;
  protected $privacy;
  protected $exPerms;
  protected $notice;

  /**
   * @param PluginBase $owner - plugin that owns this session
   */
  public function __construct(PluginBase $owner, $privacy= null, $exPerms = null, $notice = null) {
    parent::__construct($owner);	// We do it here so to prevent the registration of listeners
    $this->conlog = null;
    $this->privacy = $privacy;
    $this->exPerms = $exPerms;
    $this->notice = is_null($notice) || is_array($notice) ? $notice :  [ $notice ];
  }
  /**
   * Enable/disable console logging
   * @param callable $cb - Callback to execute when logging, otherwise null to disable
   */
  public function setLogging($cb) {
    $this->conlog = is_callable($cb) ? $cb : null;
  }
  /**
   * Return if logging is enabled or not...
   * @return bool
   */
  public function isLogging() {
    return is_callable($this->conlog);
  }
  /**
   * Enable/disable tapping
   * @param Player $pl - Player that will receive taps
   * @param callable $cb - Player being spied upon, either a string or Player instance
   */
  public function configTap(Player $pl, $cb) {
    $n = MPMU::iName($pl);
    if (is_callable($cb)) {
      if (!isset($this->state[$n])) {
        $this->state[$n] = [];
      }
      $this->state[$n]["callback"] = $cb;
    } else {
      if (isset($this->state[$n])) unset($this->state[$n]);
    }
  }
  /**
   * Check if a player is Spying
   * @param Player $pl - Player that is tapping
   * @return bool
   */
  public function isTapping(Player $pl) {
    $n = MPMU::iName($pl);
    return isset($this->state[$n]);
  }
  /**
   * Return taps
   * @param Player $pl - Player that will receive taps
   * @return str[]
   */
  public function getTaps(Player $pl) {
    $n = MPMU::iName($pl);
    if (!isset($this->state[$n])) return null;
    if (!isset($this->state[$n]["taps"])) return null;
    return array_keys($this->state[$n]["taps"]);
  }

  /**
   * Start/Stop tapping
   * @param Player $pl - Player that will receive taps
   * @param Player|str|null $n - Start tapping a player, console, everything, etc...
   */
  public function setTap(Player $pl,$v,$ena = true) {
    $v = MPMU::iName($v);
    $n = MPMU::iName($pl);
    if ($v === null) {
      // Global tap!
      if ($ena) {
        if (isset($this->state[$n]["taps"])) unset($this->state[$n]["taps"]);
      } else {
        $this->state[$n]["taps"] = [];
      }
      return;
    }
    if (!isset($this->state[$n]["taps"])) $this->state[$n]["taps"] = [];
    if ($ena) {
      $this->state[$n]["taps"][$v] = $v;
    } else {
      if (isset($this->state[$n]["taps"][$v])) unset($this->state[$n]["taps"][$v]);
    }
  }


  protected function logEvent($n,$msg) {
  	//
		// First we apply hard-coded white-washing rules
		//
		foreach ([
			// SimpleAuth related commands
			'/\/login\s*.*/' => '/login **CENSORED**',
			'/\/register\s*.*/' => '/register **CENSORED**',
			'/\/unregister\s*.*/' => '/register **CENSORED**',
			// SimpleAuthHelper related commands
			'/\/chpwd\s*.*/' => '/chpwd **CENSORED**',
		] as $re => $txt) {
			$msg = preg_replace($re,$txt,$msg);
		}
    if (is_array($this->privacy)) {
      foreach ($this->privacy as $re => $txt) {
        $msg = preg_replace($re,$txt,$msg);
      }
    }

    if ($this->conlog !== null) {
      $log = $this->conlog;
      $log($n,$msg);
    }
    foreach ($this->state as $ls=>&$tt) {
      if (isset($tt["taps"]) && !isset($tt["taps"][$n])) continue;
      $log = $tt["callback"];
      $log($ls,$n,$msg);
    }
  }
	/**
	 * @priority MONITOR
	 */
	public function onPlayerCmd(PlayerCommandPreprocessEvent $ev) {
		if ($ev->isCancelled()) return;
    if ($this->exPerms !== null && $ev->getPlayer()->hasPermission($this->exPerms)) return;
    $this->logEvent(MPMU::iName($ev->getPlayer()),$ev->getMessage());
	}
	/**
	 * @priority MONITOR
	 */
	public function onRconCmd(RemoteServerCommandEvent $ev) {
    $this->logEvent(self::RCON,$ev->getMessage());
	}
	/**
	 * @priority MONITOR
	 */
	public function onConsoleCmd(ServerCommandEvent $ev) {
		$this->logEvent(self::CONSOLE,$ev->getCommand());
	}
  public function onJoin(PlayerJoinEvent $ev) {
    if ($this->notice === null) return;
    foreach ($this->notice as $ln) {
      $ev->getPlayer()->sendMessage($ln);
    }
  }
}
