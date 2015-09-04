<?php
//= cmd:motd-add
//: Add a server for MOTD querying
//> usage: /libcommon **motd-add** _<server>_ _[port]_
//:
//: This command is available when **DEBUG** is enabled.
//:
//= cmd:motd-stat
//: Return the servers MOTD values
//> usage: /libcommon **motd-stat**
//:
//: This command is available when **DEBUG** is enabled.
namespace aliuly\loader;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use aliuly\common\mc;
use aliuly\common\BasicCli;
use pocketmine\event\Listener;
use pocketmine\event\server\QueryRegenerateEvent;
use aliuly\common\GetMotdAsyncTask;
use aliuly\common\PluginCallbackTask;



/**
 * Basic/Example MOTD support implementation
 */
class MotdMgr extends BasicCli implements Listener {
  const freq = 200;
  protected $registered;
  protected $peers;

	public function __construct($owner) {
    $this->registered = false;
    $this->peers = [];
		parent::__construct($owner);
		$this->enableSCmd("motd-add",["usage" => mc::_("<server> [port]"),
										"help" => mc::_("Schedule a server to be query by MOTD")]);
    $this->enableSCmd("motd-stat",["usage" => "",
              										"help" => mc::_("Return MOTD values")]);
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$data,array $args) {
    switch ($scmd) {
      case "motd-add":
        return $this->motdAdd($c,$args);
      case "motd-stat":
        return $this->motdStat($c,$args);
    }
    return false;
  }
  private function motdAdd(CommandSender $c,array $args) {
		if (count($args) > 2 || count($args) == 0) return false;
    if (count($args) == 1) array_push($args,19132);// Add default port
    list($srv,$port) = $args;
    $c->sendMessage(mc::_("Scheduling GetMOTDtask for %1%:%2%",$srv,$port));
    $this->launchTask($srv,$port);
    if (!$this->registered) {
      $this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
      $this->registered = true;
      $c->sendMessage(mc::_("Registering event listener..."));
    }
    return true;
  }
  private function motdStat(CommandSender $c,array $args) {
    if (count($args) != 0) return false;
    $this->regenQuery($q = $this->owner->getServer()->getQueryInformation());
    $c->sendMessage(mc::_("Players: %1%/%2%", $q->getPlayerCount(),$q->getMaxPlayerCount()));
    return true;
  }

  public function launchTask($srv,$port) {
    $this->owner->getServer()->getScheduler()->scheduleAsyncTask(
      new GetMotdAsyncTask($this->owner,"asyncResults",$srv,$port,["MotdMgr","gotResults"])
    );

  }
  public function regenQuery(QueryRegenerateEvent $ev) {
    $players = count($this->owner->getServer()->getOnlinePlayers());
    $maxplayers = $this->owner->getServer()->getMaxPlayers();
    foreach ($this->peers as $i=>$j) {
      $players += $j[0];
      $maxplayers += $j[1];
    }
    $ev->setPlayerCount($players);
    $ev->setMaxPlayerCount($maxplayers);
  }
  public function gotResults($res) {
    if (is_array($res["results"])) {
      $this->peers[implode(":",[$res["host"],$res["port"]])] = [
        $res["results"]["players"],
        $res["results"]["max-players"],
      ];
      $this->regenQuery($this->owner->getServer()->getQueryInformation());
    }
    $this->owner->getServer()->getScheduler()->scheduleDelayedTask(
      new PluginCallbackTask($this->owner, [$this,"launchTask"],[$res["host"],$res["port"]]),
      self::freq
    );

  }

}
