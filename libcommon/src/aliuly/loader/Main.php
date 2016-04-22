<?php
namespace aliuly\loader;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\plugin\PluginBase;

use aliuly\common\MPMU;

/**
 * This class is used for the PocketMine PluginManager
 */
class Main extends PluginBase implements CommandExecutor{
	/**
	 * Provides the library version
	 * @return str
	 */
	public function api() {
		return MPMU::version();
	}
	public function onEnable() {
		$pm = $this->getServer()->getPluginManager();
		if (($gb = $pm->getPlugin("GrabBag")) !== null) {
			$this->getLogger()->info("Running with GrabBag...");
		}
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Command dispatcher
	//
	//////////////////////////////////////////////////////////////////////
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		$sender->sendMessage("libcommon v".MPMU::version());
		return true;
	}
}
