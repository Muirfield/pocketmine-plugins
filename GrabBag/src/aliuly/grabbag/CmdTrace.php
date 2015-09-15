<?php
//= cmd:trace,Developer_Tools
//: controls event tracing
//>  usage: **trace** _[options]_
//:
//: Trace will show to the user the different events that are being
//: triggered on the server.  To reduce spam, events are de-duplicated.
//:
//: Sub commands:
//> * **trace**
//:   - Shows the current trace status
//> * **trace** **on**
//:   - Turns on tracing
//> * **trace** **off**
//:   - Turns off tracing
//> * **trace** **events** _[type|class]_
//:   - Show the list of the different event types and classes.  If a _type_
//:     or _class_ was specified, it will show the events defined for them.
//> * **trace** _<event|type|class>_ _[additional options]_
//:   - Will add the specified _event|type|class_ to the current user's
//:     trace session.
//> * **trace** _<-event|type|class>_ _[additional options]_
//:   - If you start the _event|type|class_ specification name with a
//:     **dash**, the _event|type|class_ will be removed from the current
//:     trace session.
//:

namespace aliuly\grabbag;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;

use aliuly\common\mc;
use aliuly\common\BasicCli;
use aliuly\common\PluginCallbackTask;
use aliuly\loader\TraceListener;
use aliuly\common\PermUtils;

use pocketmine\Player;
use pocketmine\event\HandlerList;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\utils\TextFormat;

class CmdTrace extends BasicCli implements CommandExecutor{
  protected $listener;
  protected $tracers;
  protected $timer_short;
  protected $timer_long;
  protected $timer_ticks;
  protected $timerTask;

	public function __construct($owner) {
    parent::__construct($owner);
    $this->listener = null;
    $this->tracers = null;
    $this->timer_short = 10;
    $this->timer_long = 60;
    $this->timer_ticks = 600;
    $this->timerTask = null;

    PermUtils::add($this->owner, "gb.cmd.tracer", "access event tracing", "op");

    $this->enableCmd("trace",
                ["description" => mc::_("Event tracing functionality"),
                "usage" => mc::_("/trace [options]"),
                "permission" => "gb.cmd.tracer",
                "aliases" => ["tr"]]);

	}
  public function onCommand(CommandSender $c,Command $cc,$label, array $args) {
    if (count($args) == 1) {
      switch (strtolower($args[0])) {
        case "on":
          if ($this->listener !== null) {
            $c->sendMessage(mc::_("Trace already on"));
            return true;
          }
          $this->tracers = [];
          $this->listener = new TraceListener($this->owner, [$this,"trace"]);
          $this->owner->getServer()->getPluginManager()->registerEvents($this->listener,$this->owner);
          $this->timerTask = new PluginCallbackTask($this->owner, [$this,"expireEvents"], []);
          $h = $this->owner->getServer()->getScheduler()->scheduleRepeatingTask($this->timerTask, $this->timer_ticks);
          $this->timerTask->setHandler($h);
          $this->owner->getServer()->broadcastMessage(mc::_("Tracing has been started"));
          return true;
        case "off":
          if ($this->timerTask !== null) {
            $this->owner->getServer()->getScheduler()->cancelTask($this->timerTask->getTaskId());
            $this->timerTask = null;
          }
          if ($this->listener === null) {
            $c->sendMessage(mc::_("Trace already off"));
            return true;
          }

          HandlerList::unregisterAll($this->listener);

          unset($this->listener);
          $this->listener = null;
          $this->tracers = null;
          $this->owner->getServer()->broadcastMessage(mc::_("Tracing has been stopped"));
          return true;
      }
    }
    if ($this->listener === null) {
      $c->sendMessage(mc::_("Tracing is off"));
      return true;
    }
    if (count($args) >= 1 && strtolower($args[0]) == "events") {
      if (count($args) == 1) {
        $lst = $this->listener->getList();
        $c->sendMessage(mc::_("Types: %1%", implode(", ",$lst["types"])));
        $c->sendMessage(mc::_("Classes: %1%", implode(", ",$lst["classes"])));
        return true;
      }
      array_shift($args);
      foreach ($args as $p) {
        $t = $this->listener->checkEvent($p);
        if ($t == null) {
          $c->sendMessage(mc::_("Unknown event: %1%",$p));
          return true;
        }
        $c->sendMessage(mc::_("Events: %1%", implode(", ", $t)));
      }
    }
    if ($c instanceof Player) {
      $n = strtolower($c->getName());
    } else {
      $n = "";
    }
    if (count($args) == 0) {
      $c->sendMessage(mc::_("Tracing is on"));
      $lst = [];
      //print_r($this->tracers);//##DEBUG
      foreach (array_keys($this->tracers) as $type) {
        if (isset($this->tracers[$type]["listeners"][$n])) $lst[] = $type;
      }
      if (count($lst) == 0) return true;
      $c->sendMessage(mc::n(
          mc::_("Tracing one event: %1%",$lst[0]),
          mc::_("Tracing %1% events: %2%", count($lst), implode(", ",$lst)),
          count($lst)
      ));
      return true;
    }
    list ($i,$j) = [0,0];
    foreach ($args as $k) {
      if ($k{0} == "-") {
        // Removing trace
        $k = substr($k,1);
        $types = $this->listener->checkEvent($k);
        if ($types == null) {
          $c->sendMessage(mc::_("Unknown event %1%",$k));
          continue;
        }
        $j += count($types);
        foreach ($types as $l) $this->rmTrace($l,$n);
      } else {
        // Adding traces
        $types = $this->listener->checkEvent($k);
        if ($types == null) {
          $c->sendMessage(mc::_("Unknown event %1%",$k));
          continue;
        }
        $i += count($types);
        foreach ($types as $l) {
          if (!isset($this->tracers[$l])) $this->tracers[$l] = [ "listeners" => []];
          $this->tracers[$l]["listeners"][$n] = $n;
        }
      }
    }
    if ($i) $c->sendMessage(mc::n(
      mc::_("Adding one event trace"),
      mc::_("Adding %1% event traces", $i),
      $i
    ));
    if ($j) $c->sendMessage(mc::n(
      mc::_("Removing one event trace"),
      mc::_("Removing %1% event traces", $j),
      $j
    ));
    return true;
	}
  protected function rmTrace($type,$n) {
    if (!isset($this->tracers[$type])) return false;
    if ($n !== null && isset($this->tracers[$type]["listeners"][$n])) {
      unset($this->tracers[$type]["listeners"][$n]);
    }
    if (count($this->tracers[$type]["listeners"]) == 0) {
      unset($this->tracers[$type]);
      return false;
    }
    return true;
  }
  protected function broadcastEvent($type,$msg) {
    $tx = TextFormat::YELLOW.mc::_("Event(%1%):",TextFormat::AQUA.$type.TextFormat::YELLOW).TextFormat::GREEN.$msg;
    foreach ($this->tracers[$type]["listeners"] as $n) {
      if ($n === "") {
        $this->owner->getLogger()->info($tx);
        continue;
      }
      $pl = $this->owner->getServer()->getPlayer($n);
      if ($pl === null) {
        $this->rmTrace($type,$n);
        continue;
      }
      $pl->sendMessage($tx);
    }
  }
  public function trace(Event $ev) {
    if ($ev instanceof PlayerQuitEvent) {
      // Remove registered listener
      $n = strtolower($ev->getPlayer()->getName());
      foreach (array_keys($this->tracers) as $type) {
        $this->rmTrace($type,$n);
      }
    }
    $type = explode("\\",get_class($ev));
    $type = $type[count($type)-1];
    if (!isset($this->tracers[$type])) return;
    $now = microtime(true);
    if (isset($this->tracers[$type]["history"])) {
      if ($now - $this->tracers[$type]["history"]["last"] > $this->timer_short) {
        $this->broadcastEvent($type,mc::_("repeated %1% times since %2% seconds ago", $this->tracers[$type]["history"]["count"]+1, intval($now - $this->tracers[$type]["history"]["first"])));
        $this->tracers[$type]["history"]["first"] = $now;
        $this->tracers[$type]["history"]["last"] = $now;
        $this->tracers[$type]["history"]["count"] = 0;
      } else {
        $this->tracers[$type]["history"]["last"] = $now;
        ++$this->tracers[$type]["history"]["count"];
      }
    } else {
      $this->tracers[$type]["history"] = [
        "count" => 0,
        "first" => $now,
        "last" => $now,
      ];
      $this->broadcastEvent($type,mc::_("triggered"));
    }
  }
  public function expireEvents() {
    echo __METHOD__.",".__LINE__."\n";//##DEBUG
    $now = microtime(true);
    foreach (array_keys($this->tracers) as $type) {
      if (!$this->rmTrace($type,null)) continue;
      if (!isset($this->tracers[$type]["history"])) continue;
      if ($now - $this->tracers[$type]["history"]["last"] < $this->timer_long) continue;
      if ($this->tracers[$type]["history"]["count"] > 1)
        $this->broadcastEvent($type,mc::_("triggered %1% times since %2% seconds ago", $this->tracers[$type]["history"]["count"], intval($now - $this->tracers[$type]["history"]["last"])));
      unset($this->tracers[$type]["history"]);
    }
  }
}
