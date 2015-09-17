<?php
//= cmd:echo,Developer_Tools
//: shows the given text
//>  usage: **echo** _[text]_
//:
namespace aliuly\grabbag;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;

use aliuly\common\mc;
use aliuly\common\BasicCli;
use aliuly\common\PermUtils;
use aliuly\common\ExpandVars;

class CmdEcho extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
    PermUtils::add($this->owner, "gb.cmd.echo", "echo command", "true");
		PermUtils::add($this->owner, "gb.cmd.expand", "expand command", "op");
    PermUtils::add($this->owner, "gb.cmd.rem", "remark command", "true");
    $this->enableCmd("echo",
							  ["description" => mc::_("display text"),
								"usage" => mc::_("/echo [text]"),
								"permission" => "gb.cmd.echo"]);
		$this->enableCmd("expand",
							  ["description" => mc::_("display text with variable expansion"),
								"usage" => mc::_("/echo [text]"),
								"aliases" => ["exp"],
								"permission" => "gb.cmd.expand"]);
    $this->enableCmd("rem",
							  ["description" => mc::_("don't display text"),
								"usage" => mc::_("/rem [text]"),
								"permission" => "gb.cmd.rem"]);
	}
  public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
    if (strtolower($cmd->getName()) == "rem") return true;
    $cmdline = implode(" ",$args);
		if (strtolower($cmd->getName()) == "expand") {
			$def = $this->owner->api->getVars();
			$vars = $def->getConsts();
			$def->sysVars($vars);
			if ($sender instanceof Player) $def->playerVars($sender,$vars);
			$cmdline = strtr($cmdline,$vars);
		}
    $sender->sendMessage($cmdline);
    return true;
	}
}
