<?php
namespace aliuly\common;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use aliuly\common\mc;
use aliuly\common\BasicHelp;

use pocketmine\event\player\PlayerQuitEvent;

/**
 * Simple extension to the PocketMine PluginBase class
 */
abstract class BasicPlugin extends PluginBase implements Listener {
	protected $modules = [];
	protected $scmdMap = [];
	protected $state = [];

	/**
	 * Given some defaults, this will load optional features
	 *
	 * @param str $ns - namespace used to search for classes to load
	 * @param array $mods - optional module definition
	 * @param array $defaults - default options to use for config.yml
	 * @param str $xhlp - optional help format.
	 * @return array
	 */
	protected function modConfig($ns,$mods,$defaults,$xhlp="") {
		if (!isset($defaults["features"])) $defaults["features"] = [];
		foreach ($mods as $i => $j) {
			$defaults["features"][$i] = $j[1];
		}
		$cfg=(new Config($this->getDataFolder()."config.yml",
									  Config::YAML,$defaults))->getAll();
		$this->modules = [];
		foreach ($cfg["features"] as $i=>$j) {
			if (!isset($mods[$i])) {
				$this->getLogger()->info(mc::_("Unknown feature \"%1%\" ignored.",$i));
				continue;
			}
			if (!$j) continue;
			$class = $mods[$i][0];
			if(strpos($class,"\\") === false) $class = $ns."\\".$class;
			if (isset($cfg[$i]))
				$this->modules[$i] = new $class($this,$cfg[$i]);
			else
				$this->modules[$i] = new $class($this);
		}
		$c = count($this->modules);
		if ($c == 0) {
			$this->getLogger()->info(mc::_("NO features enabled"));
			return;
		}
		$this->state = [];
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info(mc::n(mc::_("Enabled one feature"),
													 mc::_("Enabled %1% features",$c),
													 $c));
		if (count($this->scmdMap) && count($this->scmdMap["mgrs"])) {
			$this->modules[] = new BasicHelp($this,$xhlp);
		}
		return $cfg;
	}

	/**
	 * Save a config section to the plugins' config.yml
	 *
	 * @param str $key - section to save
	 * @param mixed $settings - settings to save
	 */
	public function cfgSave($key,$settings) {
		$cfg=new Config($this->getDataFolder()."config.yml",Config::YAML);
		$dat = $cfg->getAll();
		$dat[$key] = $settings;
		$cfg->setAll($dat);
		$cfg->save();
	}
	/**
	 * Used to initialize sub-command table
	 */
	protected function initSCmdMap() {
		$this->scmdMap = [
			"mgrs" => [],
			"help" => [],
			"usage" => [],
			"alias" => [],
			"permission" => [],
		];
	}
	/**
	 * Dispatch commands using sub command table
	 */
	protected function dispatchSCmd(CommandSender $sender,Command $cmd,array $args,$data=null) {
		if (count($args) == 0) {
			$sender->sendMessage(mc::_("No sub-command specified"));
			return false;
		}
		$scmd = strtolower(array_shift($args));
		if (isset($this->scmdMap["alias"][$scmd])) {
			$scmd = $this->scmdMap["alias"][$scmd];
		}
		if (!isset($this->scmdMap["mgrs"][$scmd])) {
			$sender->sendMessage(mc::_("Unknown sub-command %2% (try /%1% help)",$cmd->getName(),$scmd));
			return false;
		}
		if (isset($this->scmdMapd["permission"][$scmd])) {
			if (!$sender->hasPermission($this->scmdMapd["permission"][$scmd])) {
				$sender->sendMessage(mc::_("You are not allowed to do this"));
				return true;
			}
		}
		$callback = $this->scmdMap["mgrs"][$scmd];
		if ($callback($sender,$cmd,$scmd,$data,$args)) return true;
		if (isset($this->scmdMap["mgrs"]["help"])) {
			$callback = $this->scmdMap["mgrs"]["help"];
			return $callback($sender,$cmd,$scmd,$data,["usage"]);
		}
		return false;
	}
	/** Look-up sub command map
	 * @return array
	 */
	public function getSCmdMap() {
		return $this->scmdMap;
	}
	/**
	 * Register a sub command
	 * @param str $cmd - sub command
	 * @param callable $callable - callable to execute
	 * @param array $opts - additional options
	 */
	public function registerSCmd($cmd,$callable,$opts) {
		$cmd = strtolower($cmd);
		$this->scmdMap["mgrs"][$cmd] = $callable;

		foreach (["help","usage","permission"] as $p) {
			if(isset($opts[$p])) {
				$this->scmdMap[$p][$cmd] = $opts[$p];
			}
		}
		if (isset($opts["aliases"])) {
			foreach ($opts["aliases"] as $alias) {
				$this->scmdMap["alias"][$alias] = $cmd;
			}
		}
	}
	/**
	 * Handle player quit events.  Free's data used by the state tracking
	 * code.
	 */
	public function onPlayerQuit(PlayerQuitEvent $ev) {
		$n = strtolower($ev->getPlayer()->getName());
		if (isset($this->state[$n])) unset($this->state[$n]);
	}
	/**
	 * Get a player state for the desired module/$label.
	 *
	 * @param str $label - state variable to get
	 * @param Player|str $player - Player instance or name
	 * @param mixed $default - default value to return is no state found
	 * @return mixed
	 */
	public function getState($label,$player,$default) {
		if ($player instanceof CommandSender) $player = $player->getName();
		$player = strtolower($player);
		if (!isset($this->state[$player])) return $default;
		if (!isset($this->state[$player][$label])) return $default;
		return $this->state[$player][$label];
	}
	/**
	 * Set a player related state
	 *
	 * @param str $label - state variable to set
	 * @param Player|str $player - player instance or their name
	 * @param mixed $val - value to set
	 * @return mixed
	 */
	public function setState($label,$player,$val) {
		if ($player instanceof CommandSender) $player = $player->getName();
		$player = strtolower($player);
		if (!isset($this->state[$player])) $this->state[$player] = [];
		$this->state[$player][$label] = $val;
		return $val;
	}
	/**
	 * Clears a player related state
	 *
	 * @param str $label - state variable to clear
	 * @param Player|str $player - intance of Player or their name
	 */
	public function unsetState($label,$player) {
		if ($player instanceof CommandSender) $player = $player->getName();
		$player = strtolower($player);
		if (!isset($this->state[$player])) return;
		if (!isset($this->state[$player][$label])) return;
		unset($this->state[$player][$label]);
	}

	/**
	 * Gets the contents of an embedded resource on the plugin file.
	 *
	 * @param string $filename
	 * @return string|null
	 */
	public function getResourceContents($filename){
		$fp = $this->getResource($filename);
		if($fp === null){
			return null;
		}
		$contents = stream_get_contents($fp);
		fclose($fp);
		return $contents;
	}
}
