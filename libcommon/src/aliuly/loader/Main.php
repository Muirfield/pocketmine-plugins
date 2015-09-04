<?php
namespace aliuly\loader;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use aliuly\common\BasicPlugin;
use aliuly\common\BasicHelp;
use aliuly\common\MPMU;
use aliuly\common\mc;
use aliuly\common\ExpandVars;
use aliuly\common\PMScript;
use aliuly\common\PluginCallbackTask;

/**
 * This class is used for the PocketMine PluginManager
 */
class Main extends BasicPlugin implements CommandExecutor{
	protected $vars;
	protected $interp;
	/**
	 * Provides the library version
	 * @return str
	 */
	public function api() {
		return MPMU::version();
	}
	public function getVars() {
		return $this->vars;
	}
	public function getInterp() {
		if ($this->interp === null) {
			$this->interp  = new PMScript($this,$this->getVars());
		}
		return $this->interp;
	}
	public function onEnable() {
		mc::plugin_init($this,$this->getFile());

		MPMU::addCommand($this,$this,"libcommon", [
			"description" => mc::_("LibCommon Command Line interface"),
			"usage" => mc::_("/libcommon <subcommand> [options]"),
			"aliases" => ["lc"],
			"permission" => "libcommon.debug.command",
		]);

		$this->interp = null;
		$this->vars = new ExpandVars($this);
		$this->vars->define("{libcommon}", MPMU::version());

		$this->modules = [];
		//echo __METhOD__.",".__LINE__."\n";//##DEBUG
		foreach ([
			"Version",
			"RcCmd",
		] as $mod) {
			//echo __METhOD__.",".__LINE__." - $mod\n";//##DEBUG
			$class = __NAMESPACE__."\\".$mod;
			$this->modules[$mod] = new $class($this);
		}
		MPMU::addCommand($this,$this,"echo", [
			"description" => mc::_("Basic echo command"),
			"usage" => mc::_("/libcommon <subcommand> [options]"),
			"permission" => "libcommon.echo.command",
		]);

		if (\pocketmine\DEBUG > 1) {
			//echo __METhOD__.",".__LINE__."\n";//##DEBUG
			// Create example folders
			if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
			$mft = explode("\n",trim($this->getResourceContents("manifest.txt")));
			foreach ($mft as $f) {
				if (file_exists($this->getDataFolder().$f)) continue;
				$txt = $this->getResourceContents("examples/".$f);
				file_put_contents($this->getDataFolder().$f,$txt);
			}
			//echo __METhOD__.",".__LINE__."\n";//##DEBUG
			foreach ([
				"DumpMsgs",
				"EchoCmd",
				"MotdMgr",
				"QueryMgr",
			] as $mod) {
				//echo __METhOD__.",".__LINE__." - $mod\n";//##DEBUG

				$class = __NAMESPACE__."\\".$mod;
				$this->modules[$mod] = new $class($this);
			}
		}
		$this->modules["BasicHelp"] = new BasicHelp($this);

		// Auto start scripts...
		if (isset($this->modules["RcCmd"]))	{
			// Schedule to run this later so that other Plugins
			// get a change to start...
			$this->getServer()->getScheduler()->scheduleDelayedTask(
				new PluginCallbackTask($this,[$this->modules["RcCmd"],"autostart"]),15
			);
		}
	}
	public function asyncResults($res, $module, $cbname, ...$args) {
		if (!isset($this->modules[$module])) return;
		$cb = [ $this->modules[$module], $cbname ];
		$cb($res, ...$args);
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Command dispatcher
	//
	//////////////////////////////////////////////////////////////////////
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		if ($cmd->getName() == "echo") {
			$sender->sendMessage(implode(" ",$args));
			return true;
		}
		if ($cmd->getName() != "libcommon") return false;
		return $this->dispatchSCmd($sender,$cmd,$args);
	}
}
