<?php
//= module:query-task
//: Background task to query configured remote servers
//:
//: This module will query servers in the server list to retrieve
//: query cacheable data.
//
namespace aliuly\grabbag;

use pocketmine\event\Listener;

use aliuly\common\BasicCli;
use aliuly\common\QueryAsyncTask;
use aliuly\common\PluginCallbackTask;
use aliuly\grabbag\api\GbAddServerEvent;
//use aliuly\grabbag\api\GbRemoveServerEvent;

class QueryDaemon extends BasicCli implements Listener {
  protected $ticks;
  protected $task;
	static public function defaults() {
		//= cfg:query-task
		return [
      "# ticks" => "how often tasks are fired...",
      "ticks" => 20*10,
		];
	}
	public function __construct($owner, $cfg) {
		parent::__construct($owner);
		$this->ticks = $cfg["ticks"];
    $this->task = null;

    $this->owner->getServer()->getScheduler()->scheduleDelayedTask(
      new PluginCallbackTask($this->owner, [$this,"queryNext"],[]),
      $this->ticks
    );
	}
  private function pickNext() {
    $oldest = null;
    $pid = null;
    $srvlst = $this->owner->getModule("ServerList");
    foreach ($srvlst->getIds() as $i) {
      if ($srvlst->getServerAttr($i,"no-query-task",false)) continue;
      foreach (["info","players"] as $dd) {
        $cc = $srvlst->getQueryData($i,"query.".$dd);
        if ($cc === null) return $i;
        if ($oldest !== null && $cc["age"] > $oldest) continue;
        $oldest = $cc["age"];
        $pid = $i;
      }
    }
    return $pid;
  }
  public function queryNext() {
    $id = $this->pickNext();
    if ($id === null) {
      $this->task = null;
      return; // Nothing to ping, so we wait for an add event
    }
    $ticks = null;
    $srvlst = $this->owner->getModule("ServerList");
    foreach (["info","players"] as $dd) {
      $cc = $srvlst->getQueryData($id,"query.".$dd);
      if ($cc !== null) {
        $when = $this->ticks  - (microtime(true) - $cc["age"]) * 20;
        if ($ticks === null || $when < $ticks) $ticks = $when;
      }
    }
    if ($ticks !== null && $ticks > 0) {
      // Too soon to poll...
      $this->owner->getServer()->getScheduler()->scheduleDelayedTask(
        new PluginCallbackTask($this->owner, [$this,"queryNext"],[]),
        $ticks
      );
      return;
    }
    $host = $srvlst->getServerAttr($id,"query-host");
    $port = $srvlst->getServerAttr($id,"port");

    $this->owner->getServer()->getScheduler()->scheduleAsyncTask(
      $t = new QueryAsyncTask($this->owner,"asyncResults",$host,$port,["query-task","gotResults",$id])
    );
    $this->task = $t;
  }
  public function gotResults($res,$id) {
    foreach (["info","players"] as $dd) {
      if (!is_array($res[$dd])) continue;
      $this->owner->getModule("ServerList")->addQueryData($id,"query.".$dd,$res[$dd]);
    }
    $this->queryNext();
  }
  public function onAddServerEvent(GbAddServerEvent $ev) {
    if ($this->task !== null) return;
    $this->owner->getServer()->getScheduler()->scheduleDelayedTask(
        new PluginCallbackTask($this->owner, [$this,"queryNext"],[]),
        5
    );
  }
}
