<?php

/**
 * Execute commands
 *
 * @name autocmds
 * @main aliuly\example\AutoCmds
 * @version 1.0.0
 * @api 1.12.0
 * @description Execute commands
 * @author aliuly
 * @softdepend libcommon
 */


namespace aliuly\example{
	use aliuly\common\Cmd;
	use pocketmine\plugin\PluginBase;
	use pocketmine\event\Listener;
	use pocketmine\event\player\PlayerJoinEvent;

	class AutoCmds extends PluginBase implements Listener{
		public function onEnable(){
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
			Cmd::console($this->getServer(),[
				"timings on",
			]);
		}
		public function onJoin(PlayerJoinEvent $ev) {
			$player = $ev->getPlayer();
			Cmd::exec($player,[
				"me has joined.",
				"me is really wonderful.",
			]);
			Cmd::console($this->getServer(),[
				"give ".$player->getName()." gold_ingot",
			]);
		}
	}
}
