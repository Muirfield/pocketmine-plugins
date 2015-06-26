<?php
namespace aliuly\toybox;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\item\Item;


//use pocketmine\math\Vector3;

class CloakClock implements Listener {
	public $owner;
	protected $item;

	private function activate(Player $pl) {
			$pl->sendMessage("CloakClock activated");
			$this->owner->setState("CloakClock",$pl,true);
			foreach($this->owner->getServer()->getOnlinePlayers() as $online){
				if($online->hasPermission("toybox.cloakclock.inmune")) continue;
				$online->hidePlayer($pl);
			}
	}
	private function deactivate(Player $pl) {
			$pl->sendMessage("CloakClock de-actived");
			$this->owner->setState("CloakClock",$pl,false);
			foreach($this->owner->getServer()->getOnlinePlayers() as $online){
				$online->showPlayer($pl);
			}
	}

	public function __construct($plugin,$i) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->item = $this->owner->getItem($i,Item::CLOCK,"cloakclock")->getId();
	}
	public function onItemHeld(PlayerItemHeldEvent $e) {
		$pl = $e->getPlayer();
		if (!$this->owner->getState("CloakClock",$pl,false)) return;
		if ($e->getItem()->getId() != $this->item) $this->deactivate($pl);
	}
	public function onPlayerJoin(PlayerJoinEvent $e) {
		$pl = $e->getPlayer();
		if ($pl->hasPermission("toybox.cloakclock.inmune")) return;
		foreach($this->owner->getServer()->getOnlinePlayers() as $online){
			if ($this->owner->getState("CloakClock",$online,false)) {
				$pl->hidePlayer($online);
			}
		}
	}
	public function onPlayerInteract(PlayerInteractEvent $e) {
		// Activate cloak
		$pl = $e->getPlayer();

		if (!$pl->hasPermission("toybox.cloakclock.use")) return;

		$hand = $pl->getInventory()->getItemInHand();
		if ($hand->getID() != $this->item) return;

		$state = $this->owner->getState("CloakClock",$pl,false);
		if ($state) {
			$this->deactivate($pl);
		} else {
			$this->activate($pl);
		}
	}
}
