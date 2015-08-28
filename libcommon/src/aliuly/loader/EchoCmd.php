<?php
/**
 ** OVERVIEW:Sub Commands
 **
 ** COMMANDS
 **
 ** * echo : Shows the given text
 **   usage: /libcommon **echo** _[text]_
 **
 **/
namespace aliuly\loader;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use aliuly\common\mc;
use aliuly\common\BasicCli;
use aliuly\common\ExpandVars;
use aliuly\common\MPMU;


class EchoCmd extends BasicCli {
  protected $consts;

	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableSCmd("echo",["usage" => "[text]",
										"help" => mc::_("Show given text")]);
    $this->consts = ExpandVars::$consts;
    $this->consts['{libcommon}'] = MPMU::version();
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$data,array $args) {
    $cmdline = implode(" ",$args);
    $vars = $this->consts;
    ExpandVars::sysVars($this->owner->getServer(),$vars);
    if ($c instanceof Player) ExpandVars::playerVars($c,$vars);
    $cmdline = strtr($cmdline,$vars);
    $c->sendMessage($cmdline);
    return true;
	}
}
