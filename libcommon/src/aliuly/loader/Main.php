<?php
namespace aliuly\loader;

use pocketmine\plugin\PluginBase;
use aliuly\common\MPMU;

/**
 * This class is used for the PocketMine PluginManager
 */
class Main extends PluginBase{
	/**
	 * Provides the library version
	 * @return str
	 */
	public function api() {
		return MPMU::version();
	}
}
