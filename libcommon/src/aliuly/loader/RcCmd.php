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
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\Command;

use aliuly\common\BasicCli;
use aliuly\common\MPMU;
use aliuly\common\mc;
use aliuly\common\PMScript;
use aliuly\common\ExpandVars;

class RcCmd extends BasicCli {
	protected $interp;
	public function __construct($owner) {
		parent::__construct($owner);
		$this->interp = $owner->getInterp();
		$this->enableSCmd("rc",["usage" => "<script> [args]",
										"help" => mc::_("Runs the given PMScript")]);
	}
	public function getInterp() {
		return $this->interp;
	}
	public function autostart() {
		$script = $this->owner->getDataFolder()."autostart.pms";
		if (!file_exists($script)) return;
		$env = [];
		$args = [];
		$this->interp->define("{script}",array_shift($args));
		if ($this->interp->runScriptFile(new ConsoleCommandSender,$script,$args,$env) === false) {
			$c->sendMessage(mc::_("Compilation error"));
		}
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
		$env = [];
		$this->interp->define("{script}",array_shift($args));
		if ($this->interp->runScriptFile($c,$script,$args,$env) === false) {
			$c->sendMessage(mc::_("Compilation error"));
		}
    return true;
	}
}
