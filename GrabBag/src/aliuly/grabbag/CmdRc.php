<?php
//= cmd:rc,Developer_Tools
//: Runs the given script
//> usage: **rc** _<script>_ _[args]_
//:
//: This command will execute PMScripts present in the **GrabBag**
//: folder.  By convention, the ".pms" suffix must be used for the file
//: name, but the ".pms" is ommitted when issuing this command.
//:
//: The special script **autostart.pms** is executed automatically
//: when the **GrabBag** plugin gets enabled.
//:
//: By default only scripts in the Plugin directory are executed.
//: You can disable this feature with the command:
//:
//:      rc --no-limit-path
//:
//: To resume limiting:
//:
//:      rc --limit-path
//:
namespace aliuly\grabbag;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;

use aliuly\common\BasicCli;
use aliuly\common\MPMU;
use aliuly\common\mc;
use aliuly\common\PMScript;
use aliuly\common\ExpandVars;
use aliuly\common\PermUtils;
use aliuly\common\PluginCallbackTask;

class CmdRc extends BasicCli implements CommandExecutor {
	protected $limitPath;
	protected $env;

	public function __construct($owner) {
		parent::__construct($owner);
		$this->limitPath = true;
		PermUtils::add($this->owner, "gb.cmd.pmscript", "access rc (pmscript) command", "op");

		$this->enableCmd("rc",
								["description" => mc::_("Runs the given PMScript"),
								"usage" => mc::_("/rc <script> [args]"),
								"permission" => "gb.cmd.pmscript"]);
		$this->owner->getServer()->getScheduler()->scheduleDelayedTask(
					new PluginCallbackTask($this->owner,[$this,"autostart"],[]), 5
		);
		$this->env = [];
	}
	public function setEnv($key,$val) {
		$this->env[$key] = $val;
	}
	public function getEnv($key, $def = null) {
		if (!isset($this->env[$key])) return $def;
		return $this->env[$key];
	}
	public function unsetEnv($key) {
		if (isset($this->env[$key])) unset($this->env[$key]);
	}
	public function getInterp() {
		return $this->owner->api->getInterp();
	}
	public function autostart() {
		$script = $this->owner->getDataFolder()."autostart.pms";
		if (!file_exists($script)) return;
		$env = $this->env;
		$args = [];
		$args[] = $env["script"] = "autostart";

		if ($this->getInterp()->runScriptFile(new ConsoleCommandSender,$script,$args,$env) === false) {
			$c->sendMessage(mc::_("Compilation error"));
		}
	}
	public function onCommand(CommandSender $c,Command $cc,$label, array $args) {
    if (count($args) == 0) return false;
		if (count($args) == 1) {
			if (strtolower($args[0]) == "--limit-path") {
				$c->sendMessage(mc::_("Script path restricted to plugin data folder"));
				$this->limitPath = true;
				return true;
			}
			if (strtolower($args[0]) == "--no-limit-path") {
				$c->sendMessage(mc::_("Removing Script path restrictions"));
				$this->limitPath = false;
				return true;
			}
		}

    $script = $this->owner->getDataFolder().$args[0];
    if (!preg_match("/\\.[pP][mM][sS]\$/",$script)) $script .= ".pms";
    if (!file_exists($script)) {
      $c->sendMessage(mc::_("%1%: not found",$args[0]));
      return true;
    }
		if ($this->limitPath) {
    	$script = realpath($script);
    	if (substr($script,0,strlen($this->owner->getDataFolder())) != $this->owner->getDataFolder() ) {
      	$c->sendMessage(mc::_("Invalid script path: %1%",$args[0]));
      	return true;
    	}
		}
		$env = $this->env;
		$env["script"] = $args[0];
		if ($this->getInterp()->runScriptFile($c,$script,$args,$env) === false) {
			$c->sendMessage(mc::_("Compilation error"));
		}
    return true;
	}
}
