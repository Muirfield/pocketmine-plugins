<?php
//= cmd:log,Server_Management
//: toggle server logging (chat-scribe)
//> usage: **log** **[on|off]**
//:
//: without arguments will return the logging mode.  Otherwise **on** will
//: enable logging, **off** will disable logging.
//= cmd:spy,Player_Management
//: spy on a player in-game (chat-scribe)
//> usage: **spy** **[start|stop|status]** _[player]_
//:
//: This command is useful for a help-desk type function.  Let's you locale_lookup
//: over the shoulder of a player and see what commands they are entering.

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\player\PlayerJoinEvent;

use aliuly\common\BasicCli;
use aliuly\common\mc;
use aliuly\common\MPMU;
use aliuly\common\PermUtils;
use aliuly\common\SpySession;

class CmdSpy extends BasicCli implements CommandExecutor {
  protected $spy;
  protected $msg;

  //= cfg:chat-scribe
  static public function defaults() {
    return [
      "# privacy" => "RE => text : used to clean-up logs",
      "privacy" => [],
      "# notice" => "Show this text whenever a player logs-in",
      "notice" => "NOTICE: activites on this system may be logged",
    ];
  }
  public function getSpySession() {
    return $this->spy;
  }

	public function __construct($owner,$cfg) {
		parent::__construct($owner);

		PermUtils::add($this->owner, "gb.cmd.log", "Allow players to enable logging", "op");
    PermUtils::add($this->owner, "gb.cmd.spy", "Allow players to enable spying", "op");
    PermUtils::add($this->owner, "gb.spy.privacy", "Players with this permission do not have logging/spying", "false");
    $this->enableCmd("log",
							  ["description" => mc::_("enables server logging"),
								"usage" => mc::_("/log [on|off]"),
								"permission" => "gb.cmd.log"]);
		$this->enableCmd("spy",
							  ["description" => mc::_("spy on player in-game"),
								"usage" => mc::_("/spy <status|start|stop> [player]"),
								"permission" => "gb.cmd.spy"]);
    $this->spy = new SpySession($this->owner, $cfg["privacy"], "gb.spy.privacy", $cfg["notice"]);
	}

	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
    switch ($cmd->getName()) {
      case "log":
        return $this->logCmd($sender,$args);
      case "spy":
        return $this->spyCmd($sender,$args);
    }
    return false;
	}
  private function spyCmd(CommandSender $sender, $args) {
    if (count($args) == 0) return false;
    switch (strtolower($args[0])) {
      case "status":
        if (count($args) > 2) return false;
        if (count($args) == 1) {
          if (!MPMU::inGame($sender)) return true;
          $pl = $sender;
        } else {
          if (($pl = MPMU::getPlayer($sender,$args[0])) == null) return true;
        }
        if (!$this->spy->isTapping($pl)) {
          $sender->sendMessage(mc::_("%1% is not spying", $pl->getName()));
          return true;
        }
        $taps = $this->spy->getTaps($pl);
        if ($taps == null) {
          $sender->sendMessage(mc::_("%1% has a global tap", $pl->getName()));
          return true;
        }
        $sender->sendMessage(mc::_("%1% spying on: %2%", $pl->getName(),implode(", ",$taps)));
        return true;
      case "start":
      case "stop":
        if (count($args) != 2) return false;
        if (!MPMU::inGame($sender)) return true;
        $ena = strtolower($args[0]) == "start";
        if ($ena) {
          if (!$this->spy->isTapping($sender)) {
            $this->spy->configTap($sender,[$this,"spyCb"]);
          }
        }
        switch (strtolower($args[1])) {
          case "--all":
            $this->spy->setTap($sender, null, $ena);
            if ($ena) {
              $this->owner->getServer()->broadcastMessage(mc::_("%1% activated a global tap", $sender->getName()));
            } else {
              $this->spy->configTap($sender,null);
              $this->owner->getServer()->broadcastMessage(mc::_("%1% stopped tapping", $sender->getName()));
            }
            return true;
          case "--console":
            $this->spy->setTap($sender,SpySession::CONSOLE,$ena);
            $this->spy->setTap($sender,SpySession::RCON,$ena);
            $this->owner->getServer()->broadcastMessage($ena ?
                            mc::_("%1% activated a console tap", $sender->getName()) :
                            mc::_("%1% stopped a console tap")
                            );
            break;
          default:
            if (($pl = MPMU::getPlayer($sender,$args[1])) == null) return true;
            $this->spy->setTap($sender,$pl,$ena);
            $this->owner->getServer()->broadcastMessage($ena ?
                            mc::_("%1% is spying on %2%", $sender->getName(), $pl->getName()) :
                            mc::_("%1% stopped spying on %2%", $sender->getName(), $pl->getName())
                            );
            break;
        }
        if (!$ena) {
          if (count($this->spy->getTaps($sender)) == 0) $this->spy->configTap($sender,null);
        }
        return true;
    }
    return false;
  }
  private function logCmd(CommandSender $sender,$args) {
    if (count($args) == 0)  {
      $sender->sendMessage($this->spy->isLogging() ?
              mc::_("Command logging is on") :
              mc::_("Command logging is off")
      );
      return true;
    }
    if (count($args) != 1) return false;
    switch (strtolower($args[0])) {
      case "on":
        $this->spy->setLogging([$this,"conLogCb"]);
        $this->owner->getServer()->broadcastMessage(mc::_("Console logging activated!"));
        return true;
      case "off":
        $this->spy->setLogging(null);
        $this->owner->getServer()->broadcastMessage(mc::_("Console logging de-activated!"));
        return true;
    }
    return false;
  }
  public function conLogCb($n,$msg) {
    $this->owner->getLogger()->info(mc::_("LOG:%1%> %2%", $n, $msg));
  }
  public function spyCb($l,$t,$msg) {
    $pl = $this->owner->getServer()->getPlayer($l);
    if ($pl === null) return;
    $pl->sendMessage(mc::_("SPY:%1%> %2%", $t, $msg));
  }

}
