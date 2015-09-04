<?php
//= cmd:version
//: shows the libcomonn version
//>  usage: /libcommon **version**
namespace aliuly\loader;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use aliuly\common\mc;
use aliuly\common\MPMU;
use aliuly\common\BasicCli;

class Version extends BasicCli {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableSCmd("version",["usage" => "",
										"help" => mc::_("Show libcommon version")]);
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$data,array $args) {
		if (count($args) != 0) return false;
    $c->sendMessage(mc::_("Version: %1%", MPMU::version()));
    return true;
	}
}
