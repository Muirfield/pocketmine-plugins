<?php
//= module:gm-save-inv
//: Will save inventory contents when switching gamemodes.
//:
//: This is useful for when you have per world game modes so that
//: players going from a survival world to a creative world and back
//: do not loose their inventory.

namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\player\PlayerQuitEvent;

use aliuly\worldprotect\common\PluginCallbackTask;

class GmSaveInv extends BaseWp implements Listener {
	const TICKS = 10;

	public function __construct(Plugin $plugin) {
		parent::__construct($plugin);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function loadInv($player,$inv = null) {
		if ($inv == null) {
			$inv = $this->getState($player,null);
			if ($inv == null) return;
			$this->unsetState($player);
		}
		foreach ($inv as $slot=>$t) {
			list($id,$dam,$cnt) = explode(":",$t);
			$item = Item::get($id,$dam,$cnt);
			$player->getInventory()->setItem($slot,$item);
		}
		$player->getInventory()->sendContents($player);
	}
	public function saveInv($player) {
		$inv = [];
		foreach ($player->getInventory()->getContents() as $slot=>&$item) {
			$inv[$slot] = implode(":",[ $item->getId(),
												 $item->getDamage(),
												 $item->getCount() ]);
		}
		$this->setState($player,$inv);
	}
	/**
	 * @priority LOWEST
	 */
	public function onQuit(PlayerQuitEvent $ev) {
		$player = $ev->getPlayer();
		$sgm = $this->owner->getServer()->getGamemode();
		if ($sgm == 1 || $sgm == 3) return; // Don't do much...
		$pgm = $player->getGamemode();
		if ($pgm == 0 || $pgm == 2) return; // No need to do anything...
		$inv = $this->getState($player,null);
		if ($inv == null) return;
		$this->unsetState($player);

		// Switch gamemodes to survival/adventure so the inventory gets
		// saved...
		$player->setGamemode($sgm);
		$this->loadInv($player,$inv);
	}
	public function onGmChange(PlayerGameModeChangeEvent $ev) {
		$player = $ev->getPlayer();

		$newgm = $ev->getNewGamemode();
		$oldgm = $player->getGamemode();
		if (($newgm == 1 || $newgm == 3) && ($oldgm == 0 || $oldgm == 2)) {
			// We need to save inventory
			$this->saveInv($player);
		}
		if (($newgm == 0 || $newgm == 2) && ($oldgm == 1 || $oldgm == 3)) {
			// Need to restore inventory (but later!)
			$inv = $this->getState($player,null);
			if ($inv == null) return; // No inventory on file!
			$this->owner->getServer()->getScheduler()->scheduleDelayedTask(new PluginCallbackTask($this->owner,[$this,"loadInv"],[$player]),self::TICKS);
		}
	}
}
