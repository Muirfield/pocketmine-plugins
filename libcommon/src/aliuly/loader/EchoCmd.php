<?php
//= cmd:echo
//: shows the given text (variable substitutions are performed)
//>  usage: /libcommon **echo** _[text]_
//:
//: This command is available when **DEBUG** is enabled.
namespace aliuly\loader;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use aliuly\common\mc;
use aliuly\common\BasicCli;
use aliuly\common\ExpandVars;
use aliuly\common\MPMU;


class EchoCmd extends BasicCli {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableSCmd("echo",["usage" => "[text]",
										"help" => mc::_("Show given text")]);
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$data,array $args) {
    $cmdline = implode(" ",$args);
    $vars = $this->owner->getVars()->getConsts();
    $this->owner->getVars()->sysVars($vars);
    if ($c instanceof Player) $this->owner->getVars()->playerVars($c,$vars);
    $cmdline = strtr($cmdline,$vars);
    $c->sendMessage($cmdline);
    return true;
	}
}
