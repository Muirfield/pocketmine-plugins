<?php
/**
 ** CONFIG:monitor-settings
 **/
namespace aliuly\helper;

use pocketmine\scheduler\PluginTask;
use pocketmine\event\Listener;
use aliuly\helper\Main as HelperPlugin;
use aliuly\helper\common\mc;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerJoinEvent;

class DbMonitorTask extends PluginTask implements Listener{
  protected $canary;
  protected $ok;
  protected $dbm;
  protected $fix;

  static public function defaults() {
		return [
      "# canary-account" => "account to query",//this account is tested to check database proper operations
      "canary-account" => "steve",
      "# check-interval" => "how to often to check database (seconds)",
      "check-interval" => 600,
		];
	}
	public function __construct(HelperPlugin $owner,$cfg){
		parent::__construct($owner);
    $this->canary = $cfg["canary-account"];
    if ($owner->auth->isEnabled()) {
      $this->dbm = $owner->auth->getDataProvider();
      $this->ok = true; // Assume things are OK...
      if (!$this->pollDB()) {
        // If this fails then canary account doesn't exist yet... create it
        $player = $this->getOwner()->getServer()->getOfflinePlayer($this->canary);
        if ($player === null) {
          throw new \RuntimeException("canary account definition error!");
          return;
        }
        $err = $this->dbm->registerPlayer($player,"N/A");
        if ($err === null) {
          throw new \RuntimeException("Unable to register canary account!");
        }
      }
    } else {
      $this->ok = false;
    }

    $owner->getServer()->getScheduler()->scheduleRepeatingTask($this,$cfg["check-interval"]*20);
    $owner->getServer()->getPluginManager()->registerEvents($this, $owner);
	}
  private function setStatus($mode) {
    if ($this->ok === $mode) return;
    $this->ok = $mode;
    if ($mode) {
      $this->getOwner()->getLogger()->info(mc::_("Restored database connection"));
      $this->getOwner()->getServer()->broadcastMessage(TextFormat::GREEN.mc::_("Database connectivity restored!"));
    } else {
      $this->getOwner()->getLogger()->error(mc::_("LOST DATABASE CONNECTION!"));
      $this->getOwner()->getServer()->broadcastMessage(TextFormat::RED.mc::_("Detected loss of database connectivity!"));
    }
  }
  private function enableAuth($mgr,$auth) {
    if ($auth === null) return false; // OK, this is weird!
    if ($auth->isEnabled()) return true;
    $this->getOwner()->getLogger()->info(mc::_("Enabling SimpleAuth"));
    $mgr->enablePlugin($auth);
    if (!$auth->isEnabled()) return false;
    $this->dbm = $auth->getDataProvider();
    return true;
  }
  private function pollDB() {
    //echo __METHOD__.",".__LINE__."\n";//##DEBUG
    $player = $this->getOwner()->getServer()->getOfflinePlayer($this->canary);
    if ($player == null) return true;//Automatically assume things are OK :)
    try {
      return $this->dbm->isPlayerRegistered($player);
    } catch (\Exception $e) {
      $this->getOwner()->getLogger()->error(mc::_("DBM Error: %1%",$e->getMessage()));
    }
    return false;
  }

	public function onRun($currentTicks){
    //echo __METHOD__.",".__LINE__."\n";//##DEBUG
    $mgr = $this->getOwner()->getServer()->getPluginManager();
    $auth = $mgr->getPlugin("SimpleAuth");
    if ($auth === null) return; // OK, this is weird!

    if (!$auth->isEnabled()) {
      if (!$this->enableAuth($mgr,$auth)) return; // Ouch...
    }
    if ($this->pollDB()) {
      $this->setStatus(true);
      return;
    }
    /*
     * Lost connection to database...
     */
    $this->setStatus(false);
    /*
     * let's try to reconnect by resetting SimpleAuth
     */
    if ($auth->isEnabled()) {
      $this->getOwner()->getLogger()->info(mc::_("Disabling SimpleAuth"));
      $mgr->disablePlugin($auth);
    }
    if (!$auth->isEnabled()) {
      $this->getOwner()->getLogger()->info(mc::_("Enabling SimpleAuth"));
      if (!$this->enableAuth($mgr,$auth)) return; // Ouch...
    }
    if ($this->pollDB()) $this->setStatus(true);
	}
  public function onConnect(PlayerLoginEvent $ev) {
    $this->onRun(0);
  }
  public function onJoin(PlayerJoinEvent $ev) {
    if ($this->ok) return;
    $ev->getPlayer()->kick(mc::_("Database is experiencing technical difficulties"));
  }
}
