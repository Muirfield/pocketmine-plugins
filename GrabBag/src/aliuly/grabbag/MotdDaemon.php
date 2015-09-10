<?php
//= module:motd-task
//: Background task to ping configured remote servers
//:
//: This module will ping servers in the server list to retrieve
//: motd/cache
//
namespace aliuly\grabbag;

use aliuly\grabbag\common\BasicCli;
use aliuly\common\GetMotdAsyncTask;
use aliuly\common\PluginCallbackTask;
use aliuly\grabbag\api\GbAddServerEvent;
//use aliuly\grabbag\api\GbRemoveServerEvent;

class MotdDaemon extends BasicCli implements Listener {
  protected $ticks;
  protected $task;
	static public function defaults() {
		//= cfg:cmd-selector
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
      new PluginCallbackTask($this->owner, [$this,"pingNext"],[]),
      $this->ticks
    );
	}
  private function pickNext() {
    $oldest = null;
    $pid = null;
    $srvlst = $this->owner->getModule("ServerList")->getIds();
    foreach ($srvlst as $i) {
      if (!$this->owner->getModule("ServerList")->getServerAttr($id,"motd-task",true)) continue;
      $cc = $this->owner->getModule("ServerList")->getQueryData($i,"motd");
      if ($cc === null) return $i;
      if ($oldest !== null && $cc["age"] > $oldest) continue;
      $oldest = $cc["age"];
      $pid = $i;
    }
    return $pid;
  }
  public function pingNext() {
    $id = $this->pickNext();
    if ($id === null) {
      $this->task = null;
      return; // Nothing to ping, so we wait for an add event
    }
    $cc = $this->owner->getModule("ServerList")->getQueryData($id,"motd");
    if ($cc !== null) {
      $secs = microtime(true) - $cc["age"];
      if ($secs * 20 < $this->ticks) {
        // Too soon to poll...
        $this->owner->getServer()->getScheduler()->scheduleDelayedTask(
          new PluginCallbackTask($this->owner, [$this,"pingNext"],[]),
          $this->ticks - $secs * 20
        );
        // Schedule a ping later...
        return;
      }
    }
    $dat = $this->owner->getModule("ServerList")->getServer($id);
    $this->owner->getServer()->getScheduler()->scheduleAsyncTask(
      $t = new GetMotdAsyncTask($this->owner,"asyncResults",$dat["host"],$dat["port"],["motd-task","gotResults",$id])
    );
    $this->task = $t;
  }
  public function gotResults($res,$id) {
    if (is_array($res["results"]))
      $this->owner->getModule("ServerList")->addQueryData($id,"motd",$res["results"]);
    $this->pingNext();
  }
  public function onAddServerEvent(GbAddServerEvent $ev) {
    if ($this->task !== null) return;
    $this->owner->getServer()->getScheduler()->scheduleDelayedTask(
        new PluginCallbackTask($this->owner, [$this,"pingNext"],[]),
        5
    );
  }
}
