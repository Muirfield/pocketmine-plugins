<?php
//= cmd:wall,Server_Management
//: shows the given text to all servers
//>  usage: **wall** _[text]_
//:
//: This will broadcast the given message to all the servers lited in
//: _"serverlist"_ that have **rcon-pw** defined.  You must have **rcon**
//: enabled and all servers should be running **GrabBag** with **wall**
//: support.
//:
namespace aliuly\grabbag;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\RemoteConsoleCommandSender;

use aliuly\common\mc;
use aliuly\common\BasicCli;
use aliuly\common\PermUtils;
use aliuly\common\MPMU;

class CmdWall extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
    PermUtils::add($this->owner, "gb.cmd.wall", "broadcast command", "op");
    $this->enableCmd("wall",
							  ["description" => mc::_("display text"),
								"usage" => mc::_("/wall [text]"),
								"permission" => "gb.cmd.wall"]);
	}
  public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
    if (strtolower($cmd->getName()) != "wall") return false;
    if (count($args) == 0) return false;
    if (($sender instanceof RemoteConsoleCommandSender) && ($who = MPMU::startsWith($args[1],"--rpc=")) !== null) {
      array_shift($args);
      $msg = implode(" ",$args);
      if ($msg != "") $this->owner->getServer()->broadcastMesage(mc::_("WALL:%1% %2%", $who , $msg));
      return true;
    }
    $msg = implode(" ",$args);
    $who = $sender->getName();

    $this->owner->getServer()->broadcastMesage(mc::_("WALL:%1% %2%", $who, $msg));
    $lst = $this->owner->getModule("ServerList");
    foreach ($lst->getIds() as $id) {
      if ($lst->getServerAttr($id,"rcon-pw") === null) continue;
      $host = $lst->getServerAttr($id,"rcon-host");
      $port = $lst->getServerAttr($id,"rcon-port");
      $auth = $lst->getServerAttr($id,"rcon-pw");
      $this->owner->getServer()->getScheduler()->scheduleAsyncTask(
        new RconTask($this->owner,"rconDone",
                      [$host,$port,$auth],
                      "wall --rpc=".$who." ".$msg, [null])

      );
    }
    return true;
	}
}
