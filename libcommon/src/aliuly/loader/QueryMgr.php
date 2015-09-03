<?php
//= cmd:query-add
//: Add a server for Query gathering
//> usage: /libcommon **query-add** _<server>_ _[port]_
//:
//: This command is available when **DEBUG** is enabled.
//:
//= cmd:query-list
//: Return the available Query data
//> usage: /libcommon **query-list**
//:
//: This command is available when **DEBUG** is enabled.
namespace aliuly\loader;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use aliuly\common\mc;
use aliuly\common\BasicCli;
use pocketmine\event\Listener;
use pocketmine\event\server\QueryRegenerateEvent;
use aliuly\common\QueryAsyncTask;
use aliuly\common\PluginCallbackTask;


/**
 * Basic/Example MOTD support implementation
 */
class QueryMgr extends BasicCli implements Listener {
  const freq = 200;
  protected $registered;
  protected $peers;

	public function __construct($owner) {
    $this->registered = false;
    $this->peers = [];
		parent::__construct($owner);
		$this->enableSCmd("query-add",["usage" => mc::_("<server> [port]"),
										"help" => mc::_("Schedule a server to be queried")]);
    $this->enableSCmd("query-list",["usage" => "",
              										"help" => mc::_("Show query data")]);
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$data,array $args) {
    switch ($scmd) {
      case "query-add":
        return $this->queryAdd($c,$args);
      case "query-list":
        return $this->queryList($c,$args);
    }
    return false;
  }
  private function queryAdd(CommandSender $c,array $args) {
		if (count($args) > 2 || count($args) == 0) return false;
    if (count($args) == 1) array_push($args,19132);// Add default port
    list($srv,$port) = $args;
    $c->sendMessage(mc::_("Scheduling QueryTask for %1%:%2%",$srv,$port));
    $this->launchTask($srv,$port);
    if (!$this->registered) {
      $this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
      $this->registered = true;
      $c->sendMessage(mc::_("Registering event listener..."));
    }
    return true;
  }
  private function queryList(CommandSender $c,array $args) {
    if (count($args) != 0) return false;
    $this->regenQuery($q = $this->owner->getServer()->getQueryInformation());
    $c->sendMessage(mc::_("Players: %1%/%2%", $q->getPlayerCount(),$q->getMaxPlayerCount()));
    $names = [];
    foreach ($this->owner->getServer()->getOnlinePlayers() as $pl) {
      $names[] = $pl->getName();
    }
    foreach ($this->peers as $i=>$j) {
      foreach ($j[2] as $n) $names[] = $n;
    }
    if (count($names)) $c->sendMessage(implode(", ",$names));
    return true;
  }

  public function launchTask($srv,$port) {
    $this->owner->getServer()->getScheduler()->scheduleAsyncTask(
      new QueryAsyncTask($this->owner,"asyncResults",$srv,$port,["QueryMgr","gotResults"])
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
    if (is_array($res["info"])) {
      $players = is_array($res["players"]) ? $res["players"] : [];
      $this->peers[implode(":",[$res["host"],$res["port"]])] = [
        $res["info"]["Players"],
        $res["info"]["MaxPlayers"],
        $players,
      ];
      $this->regenQuery($this->owner->getServer()->getQueryInformation());
    }
    $this->owner->getServer()->getScheduler()->scheduleDelayedTask(
      new PluginCallbackTask($this->owner, [$this,"launchTask"],[$res["host"],$res["port"]]),
      self::freq
    );

  }

}
