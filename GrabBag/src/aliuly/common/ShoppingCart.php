<?php
namespace aliuly\common;

use aliuly\common\Session;
use aliuly\common\PluginCallbackTask;
use aliuly\common\mc;

use pocketmine\plugin\PluginBase;
use pocketmine\item\Item;

use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\inventory\InventoryCloseEvent;

use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\InventoryType;
use pocketmine\inventory\BaseTransaction;

use pocketmine\tile\Chest;
use pocketmine\tile\Tile;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\EnumTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;

use aliuly\common\MPMU;

/**
 * Implements ShoppingCart iterations
 */
class ShoppingCart extends Session {
  const tag = "shopping-cart";
  protected $fakeChest;

  /**
   * @param PluginBase $owner - plugin that owns this session
   */
  public function __construct(PluginBase $owner) {
    parent::__construct($owner);	// We do it here so to prevent the registration of listeners
    $this->fakeChest = null;
	}
  private function getFakeChestTile() {
    if ($this->fakeChest === null) {
      $this->fakeChest = new Chest($this->plugin->getServer()->getDefaultLevel()->getChunk(0,0),
                                    new CompoundTag("FakeChest", [
                                      new EnumTag("Items",[]),
                                      new StringTag("id", Tile::CHEST),
                                      new IntTag("x",0),
                                      new IntTag("y",0),
                                      new IntTag("z",0),
                                    ]));
    }
    return $this->fakeChest;
  }

	//////////////////////////////////////////////////////////////////////
  /**
	 * Handle player quit events.  Restore player's inventory before resetting
	 * state.
   *
   * @param PlayerQuitEvent $ev - Quit event
	 */

  public function onPlayerQuit(PlayerQuitEvent $ev) {
    echo __METHOD__.",".__LINE__."\n";//##DEBUG
    $n = MPMU::iName($ev->getPlayer());
    $xx = $this->getState(self::tag,$ev->getPlayer(),null);
    if ($xx !== null) $this->restoreInv($ev->getPlayer());
    parent::onPlayerQuit($ev);
    echo __METHOD__.",".__LINE__."\n";//##DEBUG
	}
  /**
   * Starts a Shopping Cart session.
   *
   * @param Player $buyer - player starting buying session
   * @param array $shop - Shop items
   * @param ChestTile|null $tile - Shop chest to use, otherwise null
   *
   * The $shop array should contain the following:
   *
   *      "id:meta" => [ pocketmine\item\Item, price ],
   *
   */
	public function start($buyer,$shop,$tile = null) {
    if ($tile === null) $tile = $this->getFakeChestTile();

		if ($this->getState(self::tag,$buyer,null) !== null) return;
		echo  __METHOD__.",".__LINE__."\n";//##DEBUG
		$inv = [ "player" => [], "chest" => $tile ];
		$inv["money"] = $this->owner->getMoney($buyer);
		var_dump($inv["money"]);//##DEBUG
		$inv["shop"] = $shop;

		foreach ($buyer->getInventory()->getContents() as $slot=>&$item) {
			$inv["player"][$slot] = implode(":",[ $item->getId(),
															  $item->getDamage(),
															  $item->getCount() ]);
		}
		$inv["chest"] = new ChestInventory($tile,$buyer);
		$contents = [];
		foreach ($shop as $idmeta=>$it) {
			$item = clone $it[0];
			$item->setCount($it[1]); // Count is the price of the item...
			$contents[] = $item;
		}
		$inv["chest"]->setContents($contents);
		$this->setState(self::tag,$buyer,$inv);
		$buyer->getInventory()->clearAll();
		$buyer->addWindow($inv["chest"]);
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
	}
  /**
   * Handle inventory transactions
   *
   * @param InventoryTransactionEvent $ev
   */
	public function onTransaction(InventoryTransactionEvent $ev) {
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$tg = $ev->getTransaction();
		$pl = null;
		$ti = null;
		foreach($tg->getInventories() as $i) {
			echo "INV ".get_class($i)."\n";//##DEBUG
			if ($i instanceof PlayerInventory) {
				$pl = $i->getHolder();
			} else {
				$ti = $i;
			}
		}
		if ($pl == null || $ti == null) return;
    $xx = $this->getState(self::tag,$pl,null);
    if ($xx == null) return; // This is a normal Chest transaction...

		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		echo "PLAYER ".$pl->getName()." is buying\n";//##DEBUG
    echo "FROM SHOP ".$xx["shop"]."\n";//##DEBUG

		$added = [];
		foreach ($tg->getTransactions() as $t) {
			if ($t->getInventory() instanceof PlayerInventory) {
				// Handling PlayerInventory changes...
				foreach ($this->playerInvTransaction($t) as $nt) {
					$added[] = $nt;
				}
				continue;
			}
			foreach ($this->chestInvTransaction($t) as $nt) {
				$added[] = $nt;
			}
		}
		echo __METHOD__.",".__LINE__."\n";//##DEBUG

		// Test if the transaction is valid...
		// Make a copy of current inventory
		$tsinv = [];
		foreach ($pl->getInventory()->getContents() as $slot=>$item) {
			if ($item->getId() == Item::AIR) continue;
			$tsinv[$slot] = [implode(":",[$item->getId(),$item->getDamage()]),
								  $item->getCount()];
		}
		//var_dump($tsinv);//##DEBUG

		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		//print_r($tsinv);//##DEBUG
		// Apply transactions to copy
		foreach ([$tg->getTransactions(),$added] as &$tset) {
			foreach ($tset as &$nt) {
				if ($nt->getInventory() instanceof PlayerInventory) {
					$item = clone $nt->getTargetItem();
					$slot = $nt->getSlot();
					if ($item->getId() == Item::AIR) {
						if(isset($tsinv[$slot])) unset($tsinv[$slot]);
					} else {
						$tsinv[$slot] = [ implode(":",
														  [ $item->getId(),
															 $item->getDamage()]),
												$item->getCount() ];
					}
				}
			}
		}
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		//		print_r($tsinv);//##DEBUG
		echo $pl->getName()." has ".$xx["money"]."G\n";//##DEBUG
		$total = 0;
		var_dump($xx["money"]);//##DEBUG
		var_dump($tsinv);//##DEBUG

		foreach ($tsinv as $slot=>$item) {
			list($idmeta,$cnt) = $item;
			echo "slot=$slot idmeta=$idmeta cnt=$cnt\n";//##DEBUG

      if (!isset($xx["shop"][$idmeta])) {
        $pl->sendMessage(mc::_("Item shop error: %1%", $idmeta));
        $ev->setCancelled();
				return;
      }
			list($i,$price) = $xx["shop"][$idmeta];
			echo "-$idmeta - $cnt/".$i->getCount()." *  $price ...".(round($cnt/$i->getCount())*$price)."\n";//##DEBUG
			$total += round($cnt/$i->getCount())*$price;
		}
		echo "TOTAL=$total\n";//##DEBUG
		if ($total > $xx["money"]) {
      $pl->sendMessage(mc::_("You do not have enough money."));
      $pl->sendMessage(mc::_("You have %1%.  You need %2%", $xx["money"], $total));
			$ev->setCancelled();
			return;
		}
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		foreach ($added as $nt) {
			$tg->addTransaction($nt);
		}
		// Make sure inventory is properly synced
		foreach($tg->getInventories() as $i) {
			$this->owner->getServer()->getScheduler()->scheduleDelayedTask(
				new PluginCallbackTask($this->owner,[$i,"sendContents"],[$pl]),5);
			$this->owner->getServer()->getScheduler()->scheduleDelayedTask(
				new PluginCallbackTask($this->owner,[$i,"sendContents"],[$pl]),10);
			$this->owner->getServer()->getScheduler()->scheduleDelayedTask(
				new PluginCallbackTask($this->owner,[$i,"sendContents"],[$pl]),15);
		}
	}
  /**
   * Close inventory event
   *
   * @param InventoryCloseEvent $ev
   */
	public function onClose(InventoryCloseEvent $ev) {
		$pl = $ev->getPlayer();
		$xx = $this->getState(self::tag,$pl,null);
		if ($xx == null) return;

		// Compute shopping basket
		$basket = [];
		$total = 0;
		foreach ($pl->getInventory()->getContents() as $slot=>$item) {
			if ($item->getId() == Item::AIR) continue;
			$idmeta = implode(":",[$item->getId(),$item->getDamage()]);
      if (!isset($xx["shop"][$idmeta])) continue;
			list($i,$price) = $xx["shop"][$idmeta];
			$total += round($item->getCount()/$i->getCount())*$price;
			$basket[] = [ $item->getId(),$item->getDamage(), $item->getCount() ];
		}
		// Restore original inventory...
		$this->restoreInv($pl);
		// Check-out
		if (count($basket) == 0) {
      $pl->sendMessage(mc::_("No items purchased"));
			return;
		}
		if ($total < $this->owner->getMoney($pl)) {
			$this->owner->grantMoney($pl,-$total);
      $pl->sendMessage(mc::n(
          mc::_("Bought one item for %1%G", $total),
          mc::_("Bought %1% items for %2%G", count($basket), $total),
          count($basket)
      ));
			foreach ($basket as $ck) {
				list($id,$meta,$cnt) = $ck;
				$pl->getInventory()->addItem(Item::get($id,$meta,$cnt));
			}
		} else {
      $pl->sendMessage(mc::_("Not enough money."));
      $pl->sendMessage(mc::_("You need %1%G", $total));
		}
	}
	private function playerInvTransaction($t) {
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$src = clone $t->getSourceItem();
		$dst = clone $t->getTargetItem();
		// This becomes nothing... not much to do...
		if ($dst->getCount() == 0 || $dst->getId() == Item::AIR) return [];
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$srccnt = $src->getId() == Item::AIR ? 0 : $src->getCount();
		$dstcnt = $dst->getId() == Item::AIR ? 0 : $dst->getCount();
		// This is a weird transaction...
		if ($srccnt == $dstcnt && $src->getId() == $dst->getId()) return [];
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$idmeta = implode(":",[$dst->getId(),$dst->getDamage()]);
		$pl = $t->getInventory()->getHolder();
		echo $pl->getName()."\n";//##DEBUG
		$xx = $this->getState(self::tag,$pl,null);
		if ($xx == null) return []; // Oops...
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		list($i,$price) = $xx["shop"][$idmeta];
		echo "i=".$i->getCount()." at ".$price."\n";//##DEBUG
		echo "dstcnt=$dstcnt srccnt=$srccnt\n";//##DEBUG
		if ($dstcnt > $srccnt) {
			// Increase
			$newcnt = $srccnt+$i->getCount();
			if ($newcnt > $i->getMaxStackSize()) $newcnt -= $i->getCount();
		} elseif ($dstcnt < $srccnt) {
			// Decrease
			$newcnt = floor($dstcnt/$i->getCount())*$i->getCount();
		}
		if ($newcnt == $dstcnt) return [];
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		echo "NEWCNT: $newcnt\n";//##DEBUG
		if ($newcnt == 0) {
			$dst = Item::get(Item::AIR,0,0);
		} else {
			$dst->setCount($newcnt);
		}
		return [ new BaseTransaction($t->getInventory(),
											  $t->getSlot(),
											  clone $t->getTargetItem(),
											  clone $dst) ];
	}
	private function chestInvTransaction($t) {
		// Moving stock to buyer
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$src = clone $t->getSourceItem();
		$dst = clone $t->getTargetItem();
		if ($dst->getId() == Item::AIR) {
			// Inventory never runs out!
			return [ new BaseTransaction($t->getInventory(),
												  $t->getSlot(),
												  clone $t->getTargetItem(),
												  clone $src) ];
		}
		if ($src->getId() == Item::AIR) {
			// Do not accept new Inventory!
			return [ new BaseTransaction($t->getInventory(),
												  $t->getSlot(),
												  clone $dst,
												  clone $src) ];
		}
		if ($dst->getCount() > 1) {
			// Inventory never increases!
			$dst->setCount(1);
			return [ new BaseTransaction($t->getInventory(),
												  $t->getSlot(),
												  clone $t->getTargetItem(),
												  clone $dst) ];
		}
		return [];
	}
  /**
   * Restore player's inventory
   *
   * @param Player $pl
   */
	public function restoreInv($pl) {
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$inv = $this->getState(self::tag,$pl,null);
		if ($inv === null) return;
		$pl->getInventory()->clearAll();
		foreach ($inv["player"] as $slot=>$itdat) {
			list($id,$meta,$cnt) = explode(":",$itdat);
			$item = Item::get($id,$meta,$cnt);
			$pl->getInventory()->setItem($slot,$item);
		}
		$this->unsetState(self::tag,$pl);
	}

}
