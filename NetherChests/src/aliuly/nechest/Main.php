<?php
namespace aliuly\nechest;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use pocketmine\utils\Config;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\Player;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\DoubleChestInventory;
use pocketmine\inventory\ChestInventory;
use pocketmine\tile\Tile;
use pocketmine\tile\Chest;
use pocketmine\math\Vector3;


use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\block\BlockPlaceEvent;

// OPEN
//- PlayerInteractEvent;
//- InventoryOpenEvent;

//PUT IN CHEST|GET FROM CHEST
//- InventoryTransactionEvent;
//- EntityInventoryChangeEvent;

// CLOSE
//- InventoryCloseEvent;


//
//use pocketmine\level\Level;
//use pocketmine\event\entity\EntityLevelChangeEvent;
//use pocketmine\block\Block;
//use pocketmine\Server;
//use pocketmine\utils\TextFormat;
//use pocketmine\scheduler\CallbackTask;


class Main extends PluginBase implements Listener {
	protected $chests;	// Array with active chests
	protected $base_block = 87;
	protected $pp_int = 30;

	protected static function iName($player) {
		return strtolower($player->getName());
	}
	protected static function chestId($obj) {
		if ($obj instanceof ChestInventory) $obj = $obj->getHolder();
		if ($obj instanceof Chest) $obj = $obj->getBlock();
		return implode(":",[$obj->getLevel()->getName(),(int)$obj->getX(),(int)$obj->getY(),(int)$obj->getZ()]);
	}

	public function onEnable(){
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->chests = [];
		$this->getServer()->getScheduler()->scheduleRepeatingTask(
			new ParticleTask($this),$this->pp_int);
	}
	private function saveInventory(Player $player,Inventory $inv) {
		$n = trim(strtolower($player->getName()));
		if ($n === "") return false;
		$d = substr($n,0,1);
		if (!is_dir($this->getDataFolder().$d)) mkdir($this->getDataFolder().$d);

		$path =$this->getDataFolder().$d."/".$n.".yml";
		$cfg = new Config($path,Config::YAML);
		$yaml = $cfg->getAll();
		$ln = trim(strtolower($player->getLevel()->getName()));

		$yaml[$ln] = [];

		foreach ($inv->getContents() as $slot=>&$item) {
			$yaml[$ln][$slot] = implode(":",[ $item->getId(),
														 $item->getDamage(),
														 $item->getCount() ]);
		}
		$inv->clearAll();
		$cfg->setAll($yaml);
		$cfg->save();
		return true;
	}
	private function loadInventory(Player $player,Inventory $inv) {
		$n = trim(strtolower($player->getName()));
		if ($n === "") return false;
		$d = substr($n,0,1);
		$path =$this->getDataFolder().$d."/".$n.".yml";
		if (!is_file($path)) return false;

		$cfg = new Config($path,Config::YAML);
		$yaml = $cfg->getAll();
		$ln = trim(strtolower($player->getLevel()->getName()));

		if (!isset($yaml[$ln])) return false;

		$inv->clearAll();
		foreach($yaml[$ln] as $slot=>$t) {
			list($id,$dam,$cnt) = explode(":",$t);
			$item = Item::get($id,$dam,$cnt);
			$inv->setItem($slot,$item);
		}
		return true;
	}
	private function lockChest(Player $player,$obj){
		$cid = self::chestId($obj);
		if (isset($this->chests[$cid])) return false;
		$this->chests[$cid] = self::iName($player);
		return true;
	}
	private function unlockChest(Player $player,$obj){
		$cid = self::chestId($obj);
		if (!isset($this->chests[$cid])) return false;
		if ($this->chests[$cid] != self::iName($player)) return false;
		unset($this->chests[$cid]);
		return true;
	}

	public function isNeChest(Inventory $inv) {
		if ($inv instanceof DoubleChestInventory) return false;
		if (!($inv instanceof ChestInventory)) return false;
		$tile = $inv->getHolder();
		if (!($tile instanceof Chest)) return false;
		$bl = $tile->getBlock();
		if ($bl->getId() != Block::CHEST) return false;
		if ($bl->getSide(Vector3::SIDE_DOWN)->getId() != $this->base_block) return false;
		return true;
	}
	public function onBlockPlaceEvent(BlockPlaceEvent $ev) {
		if ($ev->isCancelled()) return;
		$bl = $ev->getBlock();
		if ($bl->getId() != Block::CHEST || $bl->getSide(Vector3::SIDE_DOWN)->getId() != $this->base_block) return;
		$ev->getPlayer()->sendMessage("Placed a Nether Chest");
	}

	public function onPlayerQuitEvent(PlayerQuitEvent $ev) {
		$pn = self::iName($ev->getPlayer());
		foreach (array_keys($this->chests) as $cid) {
			if ($this->chests[$cid] == $pn) unset($this->chests[$cid]);
		}
	}
	public function onInventoryCloseEvent(InventoryCloseEvent $ev) {
		$player = $ev->getPlayer();
		$inv = $ev->getInventory();
		if (!$this->isNeChest($inv)) return;
		if ($this->unlockChest($player,$inv)) {
			$player->sendMessage("Closing NetherChest!");
			$this->saveInventory($player,$inv);
		}
	}
	public function onInventoryOpenEvent(InventoryOpenEvent $ev) {
		if ($ev->isCancelled()) return;
		$player = $ev->getPlayer();
		$inv = $ev->getInventory();
		if (!$this->isNeChest($inv)) return;
		if (!$this->lockChest($player,$inv)) {
			$player->sendTip("That Nether Chest is in use!");
			$ev->setCancelled();
			return;
		}
		$player->sendMessage("Opening NetherChest!");
		$this->loadInventory($player,$inv);
	}

}
