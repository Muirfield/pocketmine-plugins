<?php
namespace aliuly\killrate;

use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;
use aliuly\killrate\Main;
use aliuly\killrate\common\mc;

class ShowMessageTask extends PluginTask{
	public function __construct(Main $plugin){
		parent::__construct($plugin);
	}

	public function getPlugin(){
		return $this->owner;
	}

	public function onRun($currentTick){
		$plugin = $this->getPlugin();
		if ($plugin->isDisabled()) return;

		foreach ($plugin->getServer()->getOnlinePlayers() as $pl) {
			$pl->sendPopup(TextFormat::ITALIC . TextFormat::GRAY .
								mc::_("Score: %1%", $plugin->getScore($pl)));
		}
	}

}
