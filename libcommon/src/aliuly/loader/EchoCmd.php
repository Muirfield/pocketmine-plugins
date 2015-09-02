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
  protected $vars;

	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableSCmd("echo",["usage" => "[text]",
										"help" => mc::_("Show given text")]);
    $this->vars = new ExpandVars($this->owner);
    $this->vars->define("{libcommon}", MPMU::version());
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$data,array $args) {
    $cmdline = implode(" ",$args);
    $vars = $this->vars->getConsts();
    $this->vars->sysVars($vars);
    if ($c instanceof Player) $this->vars->playerVars($c,$vars);
    $cmdline = strtr($cmdline,$vars);
    $c->sendMessage($cmdline);
    return true;
	}
}
