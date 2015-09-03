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
			"description" => "LibCommon Command Line interface",
			"usage" => "/libcommon <subcommand> [options]",
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
		] as $mod) {
			//echo __METhOD__.",".__LINE__." - $mod\n";//##DEBUG
			$mod = __NAMESPACE__."\\".$mod;
			$this->modules[$mod] = new $mod($this);
		}

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
				"RcCmd",
			] as $mod) {
				//echo __METhOD__.",".__LINE__." - $mod\n";//##DEBUG

				$class = __NAMESPACE__."\\".$mod;
				$this->modules[$mod] = new $class($this);
			}
		}
		$this->modules["BasicHelp"] = new BasicHelp($this);

		// Auto start scripts...
		if (isset($this->modules["RcCmd"]))	$this->modules["RcCmd"]->autostart();
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Command dispatcher
	//
	//////////////////////////////////////////////////////////////////////
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		if ($cmd->getName() != "libcommon") return false;
		return $this->dispatchSCmd($sender,$cmd,$args);
	}
}
