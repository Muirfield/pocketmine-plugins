<?php
//= cmd:echo,Developer_Tools
//: shows the given text
//>  usage: **echo** _[text]_
//:
namespace aliuly\grabbag;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use aliuly\common\mc;
use aliuly\common\BasicCli;
use aliuly\common\PermUtils;

class CmdEcho extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
    PermUtils::add($this->owner, "gb.cmd.echo", "echo command", "true");
    PermUtils::add($this->owner, "gb.cmd.rem", "remark command", "true");
    $this->enableCmd("echo",
							  ["description" => mc::_("display text"),
								"usage" => mc::_("/echo [text]"),
								"permission" => "gb.cmd.echo"]);
    $this->enableCmd("rem",
							  ["description" => mc::_("don't display text"),
								"usage" => mc::_("/rem [text]"),
								"permission" => "gb.cmd.rem"]);
	}
  public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
    if (strtolower($cmd->getName()) == "rem") return true;
    $cmdline = implode(" ",$args);
    $sender->sendMessage($cmdline);
    return true;
	}
}
