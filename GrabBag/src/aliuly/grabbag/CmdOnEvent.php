<?php
//= cmd:onevent,Developer_Tools
//: Run command on event
//> usage: usage: **onevent** _<event>_ _[cmd]_
//:
//: This command will make it so a command will be executed whenever an
//: event is fired.  Options:
//> * **onevent**
//:   - show registered events
//> * **onevent** _<event>_
//:   - Show what command will be executed.
//: * **onevent** _<event>_ _<command>_
//:   - Will schedule for _command_ to be executed.
//: * **onevent** _<event>_ **--none**
//:   - Will remove the given event handler
//:
namespace aliuly\grabbag;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\Command;
use pocketmine\event\HandlerList;
use pocketmine\Player;
use pocketmine\command\CommandExecutor;

use aliuly\common\BasicCli;
use aliuly\common\MPMU;
use aliuly\common\mc;
use aliuly\common\Cmd;
use aliuly\common\PermUtils;

class CmdOnEvent extends BasicCli implements CommandExecutor{
  protected $listeners;
	public function __construct($owner) {
		parent::__construct($owner);
    $this->listeners = [];

    PermUtils::add($this->owner, "gb.cmd.onevent", "access onevent command", "op");

    $this->enableCmd("onevent",
                ["description" => mc::_("Run a command when an event is trigered"),
                "usage" => mc::_("/onevent <event> [command]"),
                "permission" => "gb.cmd.onevent"]);

    $evtab = $this->owner->getResourceContents("events.txt");
    foreach (explode("\n",$evtab) as $ln) {
      $ln = trim($ln);
      if ($ln == "" || $ln{0} == ";" || $ln{0} == "#") continue;
      $ln = preg_split('/\s+/',$ln);
      if (count($ln) != 4) continue;
      if (strtolower($ln[3]) != "yes") continue;
      $type = $ln[0];
      $event = $ln[1];
      $this->listeners[strtolower($event)] = [
        "type" => $type,
        "event" => $event,
        "listener" => null,
        "listener_class" => null,
        "command" => null,
        "count" => 0,
      ];
    }
	}
  private function listCmd(CommandSender $c) {
    // Display registered events...
    $tab = [];
    foreach ($this->listeners as $i=>$j) {
      if ($j["command"] === null) continue;
      $tab[] = $j["event"];
    }
    $c->sendMessage(mc::_("Events(%1%): %2%", count($tab), implode(", ", $tab)));
    return true;
  }
  private function showCmd(CommandSender $c, $n) {
    // Display command for event
    if ($this->listeners[$n]["command"] === null) {
      $c->sendMessage(mc::_("No command defined for %1%", $this->listeners[$n]["event"]));
      return true;
    }
    $c->sendMessage(mc::_("%1%(%2%): %3%", $this->listeners[$n]["event"],$this->listeners[$n]["count"], $this->listeners[$n]["command"]));
    return true;
  }
  private function rmCmd(CommandSender $c, $n) {
    // Removing defined command...
    if ($this->listeners[$n]["command"] === null) {
      $c->sendMessage(mc::_("No command defined for %1%", $this->listeners[$n]["event"]));
      return true;
    }
    $this->listeners[$n]["command"] = null;
    HandlerList::unregisterAll($this->listeners[$n]["listener"]);
    $c->sendMessage(mc::_("Command removed for %1%", $this->listeners[$n]["event"]));
    return true;
  }
  private function registerCmd(CommandSender $c, $n,$cmdline) {
    // Schedule this command to be execute on specific event
    if ($this->listeners[$n]["listener"] == null) {
      // Create listener object...
      $this->listeners[$n]["listener_class"] = "Listener4".$this->listeners[$n]["event"];
      $classtxt = $this->owner->getResourceContents("EventListener.php");
      $classtxt = preg_replace('/<?php/',"",$classtxt);
      $classtxt = strtr($classtxt,[
        "{ClassName}" => $this->listeners[$n]["listener_class"],
        "{EventClass}" => $this->listeners[$n]["event"],
        "{EventId}" => $n,
        "{EventClassPath}" => "\\pocketmine\\event\\".$this->listeners[$n]["type"]."\\".$this->listeners[$n]["event"],
      ]);
      echo $classtxt."\n";//##DEBUG
      if (eval($classtxt) === false) {
        $c->sendMessage(mc::_("Error defining listener class %1%", $this->listeners[$n]["listener_class"]));
        return true;
      }
      $newlistener = eval("return new ".$this->listeners[$n]["listener_class"]."(\$this->owner,\$this);");
      if ($newlistener === false) {
        $c->sendMessage(mc::_("Error creating listener class %1%",$this->listeners[$n]["listener_class"]));
      }
      $this->listeners[$n]["listener"] = $newlistener;
    }
    if ($this->listeners[$n]["command"] == null) {
      // No listener been regsitered
      $this->owner->getServer()->getPluginManager()->registerEvents($this->listeners[$n]["listener"],$this->owner);
      $this->listeners[$n]["command"] = "rem";
    }
    $this->listeners[$n]["command"] = $cmdline;
    $c->sendMessage(mc::_("Command configured for %1%", $this->listeners[$n]["event"]));
    return true;
  }
  public function onCommand(CommandSender $c,Command $cc,$label, array $args) {
    if (count($args) == 0) return $this->listCmd($c);
    if (!isset($this->listeners[$n = strtolower($args[0])])) {
      $c->sendMessage(mc::_("Unknown event %1%",$args[0]));
      return true;
    }
    if (count($args) == 1) return $this->showCmd($c,$n);
    if (count($args) == 2 && $args[1] == "--none") return $this->rmCmd($c,$n);
    array_shift($args);
    return $this->registerCmd($c,$n,implode(" ",$args));
	}
  public function dispatchEvent($n, $ev) {
    if ($this->listeners[$n]["command"] === null) return;
    if (is_callable([$ev,"getPlayer"])) {
      $ctx = $ev->getPlayer();
    } elseif (is_callable([$ev,"getEntity"]) && ($ev->getEntity() instanceof Player)) {
      $ctx = $ev->getEntity();
    } else {
      $ctx = new ConsoleCommandSender;
    }
    Cmd::opexec($ctx,$cmdline);
  }
}
