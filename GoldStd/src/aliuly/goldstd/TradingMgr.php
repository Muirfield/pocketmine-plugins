<?php
/**
 ** CONFIG:defaults
 **
 ** Default values for paying players by tapping
 **
 **/
namespace aliuly\goldstd;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase as Plugin;

use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;

use aliuly\goldstd\common\mc;
use aliuly\goldstd\common\MPMU;

class TradingMgr implements Listener {
	protected $defaults;
	protected $state;
	protected $owner;
	protected $goods;
	protected $currency;
	static public function defaults() {
		return [
			"# payment" => "default payment when tapping on a player",
			"payment" => 1,
			"# timeout" => "how long a transaction may last",
			"timeout" => 30,
		];
	}

	public function __construct(Plugin $plugin,$currency,$goods,$dfts) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->goods = [];
		foreach ($goods as $cf) {
			$item = Item::fromString($cf);
			if (($item = $item->getId()) == Item::AIR) {
				$plugin->getLogger()->error(mc::_("Invalid trade-good: %1%",$cf));
				continue;
			}
			$this->goods[$item] = $item;
		}
		$this->defaults = $dfts;
		$this->currency = $currency;
		$this->state = [];
	}

	//////////////////////////////////////////////////////////////////////
	//
	// Manipulate internal state
	//
	//////////////////////////////////////////////////////////////////////
	public function getAttr($pl,$attr, $def = null) {
		if ($def === null) $def = $this->defaults[$attr];
		if ($pl instanceof Player) $pl = MPMU::iName($pl);
		if (!isset($this->state[$pl])) {
			$this->state[$pl] = [ $attr => $def ];
		}
		if (!isset($this->state[$pl][$attr])) {
			$this->state[$pl][$attr] =  $def;
		}
		return $this->state[$pl][$attr];
	}
	public function setAttr($pl,$attr, $val) {
		if ($pl instanceof Player) $pl = MPMU::iName($pl);
		if (!isset($this->state[$pl])) {
			$this->state[$pl] = [ $attr => $val ];
		}
		$this->state[$pl][$attr] =  $val;
		return;
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Event handlers
	//
	//////////////////////////////////////////////////////////////////////
	public function onPlayerQuitEvent(PlayerQuitEvent $e) {
		$pl = MPMU::iName($e->getPlayer());
		if (isset($this->state[$pl])) unset($this->state[$pl]);
	}
	/**
	 * @priority LOWEST
	 */
	public function onPlayerPayment(EntityDamageEvent $ev) {
		if ($ev->isCancelled()) return;
		if(!($ev instanceof EntityDamageByEntityEvent)) return;
		$giver = $ev->getDamager();
		$taker = $ev->getEntity();
		if (!($giver instanceof Player)) return;
		if ($giver->isCreative() || $giver->isSpectator()) return;

		$hand = $giver->getInventory()->getItemInHand();
		if ($hand->getId() == $this->currency) {
			if ($taker instanceof Player) {
				if ($taker->isCreative() || $taker->isSpectator()) {
					$giver->sendMessage(mc::_("No trading possible, %1% is in %2% mode",
													  $taker->getDisplayName(),
													  MPMU::gamemodeStr($taker->getGamemode())));
					return;
				}
				$ev->setCancelled(); // OK, we want to pay, not to fight!
				$this->onPlayerPaid($giver,$taker);
			} //else paying an Entity!
			return;
		}
		if (isset($this->goods[$hand->getId()])) {
			if ($taker instanceof Player) {
				if ($taker->isCreative() || $taker->isSpectator()) {
					$giver->sendMessage(mc::_("No trading possible, %1% is in %2% mode",
													  $taker->getDisplayName(),
													  MPMU::gamemodeStr($taker->getGamemode())));
					return;
				}
				$ev->setCancelled(); // OK, we are trading
				$this->onPlayerTrade($giver,$taker);
			} //else trading with entity...
			return;
		}
	}

	/*
	 * Item exchange...
	 */

	/*
	 * Events
	 -  $this->getServer()->getPluginManager()->callEvent(new Event(xxx))
	*/
	public function onPlayerPaid(Player $giver,Player $taker) {
		$gg = $this->getAttr($giver,"payment");
		$this->setAttr($giver,"payment",$this->defaults["payment"]);
		if ($this->owner->getMoney($giver->getName()) < $gg) {
			$giver->sendMessage(mc::_("You don't have that much money!"));
			return;
		}
		$this->owner->grantMoney($giver->getName(),-$gg);
		$this->owner->grantMoney($taker->getName(),$gg);
		list($when,$amt,$ptaker) = $this->getAttr($giver,"counter",[0,0,""]);
		if (time() - $when < $this->defaults["timeout"] && $ptaker == $taker->getName()) {
			// Still the same transaction...
			$amt += $gg;
		} else {
			// New transaction!
			$when = time();
			$amt = $gg;
			$ptaker = $taker->getName();
		}
		$this->setAttr($giver,"counter",[$when,$amt,$ptaker]);

		if (MPMU::apiVersion("1.12.0")) {
			$giver->sendTip(mc::_("Paid %2%G, you now have %1%G",
											  $this->owner->getMoney($giver->getName()),$amt));
			$taker->sendTip(mc::_("Received %2%G, you now have %1%G",
											  $this->owner->getMoney($taker->getName()).$amt));
		} else {
			$giver->sendMessage(mc::_("Paid %2%G, you now have %1%G",
											  $this->owner->getMoney($giver->getName()),$amt));
			$taker->sendMessage(mc::_("Received %2%G, you now have %1%G",
											  $this->owner->getMoney($taker->getName()).$amt));
		}
	}
	public function onPlayerTrade(Player $giver,Player $taker) {
		$good = clone $giver->getInventory()->getItemInHand();
		$gift = clone $good;
		$gift->setCount(1);
		$taker->getInventory()->addItem($gift);
		$good->setCount($n = $good->getCount()-1);
		$slot = $giver->getInventory()->getHeldItemSlot();
		if ($n <= 0) {
			$giver->getInventory()->clear($slot);
		} else {
			$giver->getInventory()->setItem($slot,$good);
		}
		$item = MPMU::itemName($good);
		if (MPMU::apiVersion("1.12.0")) {
			$giver->sendTip(mc::_("Gave one %1%",$item));
			$taker->sendTip(mc::_("Received %1%",$item));
		} else {
			$giver->sendMessage(mc::_("Gave one %1%",$item));
			$taker->sendMessage(mc::_("Received %1%",$item));
		}

	}
}
