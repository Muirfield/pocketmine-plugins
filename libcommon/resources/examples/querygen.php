<?php

/**
 * Example How to Use QueryAsyncTask
 *
 * @name querygen
 * @main aliuly\example\QueryGen
 * @version 1.0.0
 * @api 1.12.0
 * @description
 * @author aliuly
 * @softdepend libcommon
 */


namespace aliuly\example{
	use pocketmine\plugin\PluginBase;
	use pocketmine\event\Listener;
	use pocketmine\event\server\QueryRegenerateEvent;
  use aliuly\common\QueryAsyncTask;
	use aliuly\common\PluginCallbackTask;

	class QueryGen extends PluginBase implements Listener{
		const freq = 200;
		protected $players;
		protected $maxplayers;

		public function onEnable(){
			$this->players = [];
			$this->maxplayers = [];
			foreach (["namco:19132"] as $cfg) {
				$r = explode(":",$cfg,2);
				if (!isset($r[1])) $r[1] = 19132;
				list($host,$port) = $r;
				$this->launchQuery($host,$port);
			}
			$this->getServer()->getPluginManager()->registerEvents($this, $this);

		}
		public function launchQuery($host,$port) {
			$this->getServer()->getScheduler()->scheduleAsyncTask(
				new QueryAsyncTask($this,"gotQuery",$host,$port,[$host,$port])
			);
		}
		public function gotQuery($results,$host,$port) {
			if (is_array($results)) {
				if (isset($results["info"])) {
					if (isset($results["info"]["Players"])) {
					$this->players[implode(":",[$host,$port])] = $results["info"]["Players"];
					}
					if (isset($results["info"]["MaxPlayers"])) {
						$this->maxplayers[implode(":",[$host,$port])] = $results["info"]["MaxPlayers"];
					}
				}
			} elseif ($results === null) {
				$this->getLogger()->error("Unknown error");
			} else {
				$this->getLogger()->error($results);
			}
			$this->getServer()->getScheduler()->scheduleDelayedTask(
				new PluginCallbackTask($this, [$this,"launchQuery"],[$host,$port]),
				self::freq
	  	);
		}
		public function onQuery(QueryRegenerateEvent $ev) {
			$players = count($this->getServer()->getOnlinePlayers());
			$maxplayers = $this->getServer()->getMaxPlayers();

			foreach ($this->players as $count) {
				$players += $count;
			}
			foreach ($this->maxplayers as $count) {
				$maxplayers += $count;
			}
			$ev->setPlayerCount($players);
			$ev->setMaxPlayerCount($maxplayers);
		}
	}
}
