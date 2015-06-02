<?php
namespace aliuly\worldprotect;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\PluginCommand;

use pocketmine\utils\TextFormat;
use aliuly\worldprotect\common\BasicCli;

abstract class BaseWp extends BasicCli {
	protected $wcfg;

	public function __construct($owner) {
		parent::__construct($owner);
		$this->wcfg = [];
	}
	//
	// Config look-up cache
	//
	public function setCfg($world,$value) {
		$this->wcfg[$world] = $value;
	}
	public function unsetCfg($world) {
		if (isset($this->wcfg[$world])) unset($this->wcfg[$world]);
	}
	public function getCfg($world,$default) {
		if (isset($this->wcfg[$world])) return $this->wcfg[$world];
		return $default;
	}
}
