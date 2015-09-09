<?php
//= cmd:ftserver,Teleporting
//: Travel to remove servers
//> usage: **ftserver** _<serverid>_
//:
//: Teleport to servers defined with the **/servers** command.

namespace aliuly\grabbag;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\utils\TextFormat;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\PermUtils;
use aliuly\grabbag\common\MPMU;

class CmdFtServers extends BasicCli implements CommandExecutor {

	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.ftserver", "Allow user to use Fast Transfer", "op");

		$this->enableCmd("ftserver",
							  ["description" => mc::_("Teleport to server via FastTransfer"),
								"usage" => mc::_("/ftserver <serverid>"),
								"permission" => "gb.cmd.ftserver",
                "aliases" => ["fts"]]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
    if ($cmd->getName() != "ftserver") return false;
    if (count($args) != 1) return false;
    $id = $args[0];
    if (($dat = $this->owner->getModule("ServerList")->getServer($id)) === null) {
			$c->sendMessage(TextFormat::RED.mc::_("%1% does not exist",$id));
			return false;
		}
    if (!MPMU::inGame($sender)) return true;
    $host = $dat["host"];
		$port = $dat["port"];
    if (MPMU::callPlugin($this->owner->getServer(),"FastTransfer","transferPlayer",[$sender,$host,$port]) === null) {
      $this->getLogger()->error(TextFormat::RED.mc::_("FAST TRANSFER ERROR"));
      return true;
    }
    return true;
	}

}
