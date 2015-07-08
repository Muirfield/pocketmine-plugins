<?php
namespace aliuly\loader;

use aliuly\common\BasicPlugin;
use aliuly\common\MPMU;

/**
 * This class is used for the PocketMine PluginManager
 */
class Main extends BasicPlugin{
	/**
	 * Provides the library version
	 * @return str
	 */
	public function api() {
		return MPMU::version();
	}
	public function onEnable() {
		if (\pocketmine\DEBUG > 1) {
			if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
			$mft = explode("\n",trim($this->getResourceContents("manifest.txt")));
			foreach ($mft as $f) {
				if (file_exists($this->getDataFolder().$f)) continue;
				$txt = $this->getResourceContents("examples/".$f);
				file_put_contents($this->getDataFolder().$f,$txt);
			}
		}
	}
}
