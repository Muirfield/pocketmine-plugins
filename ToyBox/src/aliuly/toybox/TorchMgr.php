<?php
namespace aliuly\toybox;

use pocketmine\event\Listener;
use pocketmine\Player;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\Server;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\item\Item;
use aliuly\toybox\common\mc;

class TorchMgr implements Listener {
	public $owner;
	protected $item;
	protected $block;

	private function spawn(Position $p,$id,$meta) {
		$bl = $p->getLevel()->getBlock($p);
		$pk = new UpdateBlockPacket();
		$pk->x = $p->x;
		$pk->y = $p->y;
		$pk->z = $p->z;
		$pk->block = $id;
		$pk->meta = $meta;
		Server::broadcastPacket($p->getLevel()->getUsingChunk($p->x >> 4,
																				$p->z >> 4), $pk);
		return [$bl->getId(),$bl->getDamage()];
	}
	private function spawnTorch(Player $pl) {
		$state = $this->owner->getState("Torch",$pl,null);
		if($state){
			list($pos,$id,$meta) = $state;
			$this->spawn($pos,$id,$meta); // Restore block
		}
		$pos = new Position(floor($pl->getX()),floor($pl->getY()+1),
								  floor($pl->getZ()),$pl->getLevel());
		list($id,$meta) = $this->spawn($pos,$this->block,0);
		$this->owner->setState("Torch",$pl,[$pos,$id,$meta]);
	}
	private function deSpawnTorch(Player $pl) {
		$state = $this->owner->getState("Torch",$pl,null);
		if($state){
			list($pos,$id,$meta) = $state;
			$this->spawn($pos,$id,$meta); // Restore block
		}
		$this->owner->unsetState("Torch",$pl);
	}

	public function __construct($plugin,$cfg) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);

		$this->item = $this->owner->getItem($cfg["item"],Item::TORCH,"torch-item")->getId();
		$this->block = $this->owner->getItem($cfg["block"],Item::TORCH,"torch-block")->getId();
	}
	public function onItemHeld(PlayerItemHeldEvent $e) {
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$pl = $e->getPlayer();
		if(!$this->owner->getState("Torch",$pl,null)) return;
		if ($e->getItem()->getId() != $this->item) {
			$this->deSpawnTorch($pl);
		}
	}
	/**
	 * @priority HIGHEST
	 */
	public function onMove(PlayerMoveEvent $e) {
		if ($e->isCancelled()) return;
		$pl = $e->getPlayer();
		if(!$this->owner->getState("Torch",$pl,null)) return;
		$this->spawnTorch($pl);
	}

	public function onInteract(PlayerInteractEvent $e) {
		// Activate torch
		$pl = $e->getPlayer();

		if (!$pl->hasPermission("toybox.torch")) return;
		$bl = $e->getBlock();
		if ($bl->isSolid()) return;

		$hand = $pl->getInventory()->getItemInHand();
		if ($hand->getID() != $this->item) return;

		$state = $this->owner->getState("Torch",$pl,null);
		if ($state) {
			$this->deSpawnTorch($pl);
			$pl->sendMessage(mc::_("Torch de-activated"));
		} else {
			$this->spawnTorch($pl);
			$pl->sendMessage(mc::_("Torch activated"));
		}
	}
	public function onJoin(PlayerJoinEvent $e) {
		$pl = $e->getPlayer();
		foreach($this->owner->getServer()->getOnlinePlayers() as $online){
			if(!$this->owner->getState("Torch",$online,null)) continue;
			$this->spawnTorch($online);
		}
	}
}
