<?php
namespace aliuly\common;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Inventory related utilities
 */
abstract class InvUtils {
	/**
	 * Clear players inventory
	 * @param Player $target
	 */
	static public function clearInventory(Player $target) {
		if ($target->isCreative() || $target->isSpectator()) return;
		$target->getInventory()->clearAll();
	}
	/**
	 * Clear hotbar
	 * @param Player $target
	 */
	static public function clearHotBar(Player $target) {
		$inv = $target->getInventory();
		for ($i=0;$i < $inv->getHotbarSize(); $i++) {
			$inv->setHotbarSlotIndex($i,-1);
		}
		// Make sure inventory is updated...
		$inv->sendContents($target);
	}
	/**
	 * Remove item from inventory....
	 * @param Player $target
	 * @param Item $item
	 * @param int|null $count
	 */
	static public function rmInvItem(Player $target, Item $item, $count = null) {
		$k = 0;
		foreach ($target->getInventory()->getContents() as $slot => &$inv) {
			if ($inv->getId() != $item->getId()) continue;
			if ($count !== null) {
				if ($inv->getCount() > $count) {
					$k += $count;
					$inv->setCount($inv->getCount()-$count);
					$target->getInventory()->setItem($slot,clone $inv);
					break;
				}
				$count -= $inv->getCount();
			}
			$k += $inv->getCount();
			$target->getInventory()->clear($slot);
			if ($count === 0) break;
		}
		$target->getInventory()->sendContents($target);
		return $k;
	}
	/**
	 * Count amount of items
	 * @param Player $target
	 * @param Item $item
	 * @return int
	 */
	static public function countInvItem(Player $target,Item $item) {
		$k = 0;
		foreach ($target->getInventory()->getContents() as $slot => &$inv) {
			if ($inv->getId() == $item->getId()) $k += $inv->getCount();
		}
		return $k;
	}

}
