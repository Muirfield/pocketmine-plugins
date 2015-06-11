<?php
namespace aliuly\loader;

use pocketmine\plugin\PluginBase;
use aliuly\common\MPMU;

class Main extends PluginBase{
	public function api() {
		return MPMU::version();
	}
}
