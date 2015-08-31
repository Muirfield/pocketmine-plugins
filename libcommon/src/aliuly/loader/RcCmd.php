<?php
/**
 ** OVERVIEW:Sub Commands
 **
 ** COMMANDS
 **
 ** * rc : Runs the given script
 **   usage: /libcommon **rc** _<script>_ _[args]_
 **
 **/
namespace aliuly\loader;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use aliuly\common\BasicCli;
use aliuly\common\MPMU;
use aliuly\common\mc;
use aliuly\common\PMScript;
use aliuly\common\ExpandVars;

class RcCmd extends BasicCli {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableSCmd("rc",["usage" => "<script> [args]",
										"help" => mc::_("Runs the given PMScript")]);
    PMScript::$consts = ExpandVars::$consts;
    PMScript::$consts['{libcommon}'] = MPMU::version();
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$data,array $args) {
    if (count($args) == 0) return false;
    $script = $this->owner->getDataFolder().$args[0];
    if (!preg_match("/\\.[pP][mM][sS]\$/",$script)) $script .= ".pms";
    if (!file_exists($script)) {
      $c->sendMessage(mc::_("%1%: not found",$args[0]));
      return true;
    }
    $script = realpath($script);
    if (substr($script,0,strlen($this->owner->getDataFolder())) != $this->owner->getDataFolder() ) {
      $c->sendMessage(mc::_("Invalid script path: %1%",$args[0]));
      return true;
    }
    $txt = file_get_contents($script);

    PMScript::run($this->owner->getServer(), $c, $txt, $args);
    return true;
	}
}
