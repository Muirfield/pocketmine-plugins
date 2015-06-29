<?php
namespace aliuly\goldstd;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase as Plugin;

use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\tile\Sign;
use pocketmine\entity\Effect;

use aliuly\goldstd\common\mc;
use aliuly\goldstd\common\MPMU;

class SignMgr implements Listener {
	protected $owner;
	protected $texts;

	static public function defaults() {
		return [
			"shop" => ["[SHOP]"],
			"casino" => ["[CASINO]"],
			"trade" => ["[TRADE]"],
			"effects" => ["[POTIONS]"],
		];
	}
	public function __construct(Plugin $plugin,$cfg) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		//print_r($cfg);//##DEBUG
		// Configure texts
		$this->texts = [];
		foreach ($cfg as $sn => $tab) {
			foreach ($tab as $z) {
				$this->texts[$z] = $sn;
			}
		}

	}
	//////////////////////////////////////////////////////////////////////
	// Manage signs
	//////////////////////////////////////////////////////////////////////
	private function parseItemLine($txt) {
		$txt = preg_split('/\s+/',$txt);
		if (count($txt) == 0) return null;
		$cnt = $txt[count($txt)-1];
		if (preg_match('/^x(\d+)$/',$cnt,$mv)) {
			$cnt = $mv[1];
			array_pop($txt);
		} else {
			$cnt = 1;
		}
		$item = Item::fromString(implode("_",$txt));
		if ($item->getId() == 0) return null;
		$item->setCount($cnt);
		return $item;
	}
	private function parseEffectLine($txt) {
		$txt = preg_split('/\s*:\s*/',$txt);
		if (count($txt) == 0 || count($txt) > 3) return null;
		if (!isset($txt[1]) || empty($txt[1])) $txt[1] = 60;
		if (!isset($txt[2]) || empty($txt[2])) $txt[2] = 1;
    if (is_numeric($txt[0])) {
			$effect = Effect::getEffect($txt[0]);
		} else {
			$effect = Effect::getEffectByName($txt[0]);
		}
		if ($effect === null) return null;
		$effect->setDuration($txt[1]*20);
		$effect->setAmplifier($txt[2]);
		$effect->setVisible(true);
		return $effect;
	}

	private function parsePriceLine($txt) {
		$n = intval(preg_replace('/[^0-9]/', '', $txt));
		if ($n == 0) return null;
		return $n;
	}
	private function parseCasinoLine($txt) {
		$txt = preg_replace('/^\s*odds:\s*/i','',$txt);
		$txt = preg_split('/\s*:\s*/',$txt);
		if (count($txt) != 2) return [null,null];
		list($odds,$payout) = $txt;
		$payout = $this->parsePriceLine($payout);
		if ($payout === null) return [null,null];
		return [$this->parsePriceLine($odds),$payout];
	}
	private function activateSign($pl,$tile) {
		$sign = $tile->getText();
		if (!MPMU::access($pl,"goldstd.signs.use")) return;
		$sn = $this->texts[$sign[0]];
		if (!MPMU::access($pl,"goldstd.signs.use.".$sn)) return;
		switch ($sn) {
			case "shop":
				$item = $this->parseItemLine($sign[1]);
				if ($item === null) {
					$pl->sendMessage(mc::_("Invalid item line"));
					return;
				}
				$price = $this->parsePriceLine($sign[2]);
				if ($price === null) {
					$pl->sendMessage(mc::_("Invalid price line"));
					return;
				}
				$money = $this->owner->getMoney($pl);
				if ($money < $price) {
					$pl->sendMessage(mc::_("[GoldStd] You do not have enough money"));
				} else {
					$this->owner->grantMoney($pl,-$price);
					$pl->getInventory()->addItem(clone $item);
					$pl->sendMessage(mc::_("[GoldStd] Item purchased"));
				}
				break;
			case "effects":
			  $effect = $this->parseEffectLine($sign[1]);
				if ($effect === null) {
					$pl->sendMessage(mc::_("Invalid effects line"));
					return false;
				}
				$price = $this->parsePriceLine($sign[2]);
				if ($price === null) {
					$pl->sendMessage(mc::_("Invalid price line"));
					return false;
				}
				$money = $this->owner->getMoney($pl);
				if ($money < $price) {
					$pl->sendMessage(mc::_("[GoldStd] You do not have enough money"));
				} else {
					$this->owner->grantMoney($pl,-$price);
					$pl->addEffect($effect);
					$pl->sendMessage(mc::_("[GoldStd] Potion %1% purchased",$effect->getName()));
				}

				break;
			case "trade":
				// This is what you get...
				$item1 = $this->parseItemLine($sign[1]);
				if ($item1 === null) {
					$pl->sendMessage(mc::_("Invalid item1 line"));
					return;
				}
				// This is what you pay...
				$item2 = $this->parseItemLine($sign[2]);
				if ($item2 === null) {
					$pl->sendMessage(mc::_("Invalid item2 line"));
					return;
				}
				/*** Check if the player has item2 in stock ***/
				$inv = $pl->getInventory();
				$cnt = 0;
				$slots = [];
				foreach ($pl->getInventory()->getContents() as $slot=>&$item) {
					if ($item2->getId() != $item->getId()) continue;
					if ($item2->getDamage() &&
						 ($item2->getDamage() != $item->getDamage())) continue;
					// OK, he got it...
					$cnt += $item->getCount();
					$slots[] = [$slot,$item->getCount()];
				}
				if ($cnt == 0) {
					$pl->sendMessage(mc::_("You do not have any %1%",
												  MPMU::itemName($item2)));
					return;
				}
				if ($cnt < $item2->getCount()) {
					$pl->sendMessage(mc::_("You do not have enough %1%",
												  MPMU::itemName($item2)));
					$pl->sendMessage(mc::_("You have %1%, you need %2%",
												 $cnt, $item2->getCount()));
					return;
				}
				$cnt = $item2->getCount(); // Take away stock...
				while ($cnt >= 0) {
					list($slot,$qty) = array_pop($slots);
					if ($qty > $cnt) {
						// More than enough...
						$newitem = clone $item2;
						$newitem->setCount($qty - $cnt);
						$cnt = 0;
						$pl->getInventory()->setItem($slot,$newitem);
						break;
					}
					if ($qty <= $cnt) {
						// Not enough, consume that slot completely...
						$cnt -= $qty;
						$pl->getInventory()->clear($slot);
					}
				}
				$pl->sendMessage(mc::n(
					mc::_("Gave away one %1%",MPMU::itemName($item2)),
					mc::_("Gave away %2% %1%s",
							MPMU::itemName($item2),$item2->getCount()),
					$item2->getCount()));
				// Give new stock...
				$pl->getInventory()->addItem(clone $item1);
				$pl->sendMessage(mc::n(
					mc::_("Got one %1%", MPMU::itemName($item1)),
					mc::_("Got %2% %1%s", MPMU::itemName($item1),$item1->getCount()),
					$item1->getCount()));
				break;
			case "casino":
				list($odds,$payout) = $this->parseCasinoLine($sign[1]);
				if ($odds === null) {
					$pl->sendMessage(mc::_("Invalid odds line"));
					return;
				}
				$price = $this->parsePriceLine($sign[2]);
				if ($price === null) {
					$pl->sendMessage(mc::_("Invalid price line"));
					return;
				}
				$money = $this->owner->getMoney($pl);
				if ($money < $price) {
					$pl->sendMessage(mc::_("[GoldStd] You do not have enough moneys"));
				} else {
					$pl->sendMessage(mc::_("[GoldStd] Betting %1%...",$price));
					$this->owner->grantMoney($pl,-$price);
					$rand = mt_rand(0,$odds);
					if ($rand == 1) {
						$pl->sendMessage(mc::_("[GoldStd] You WON!!! prize...%1%G",
													  $payout));
						$this->owner->grantMoney($pl,$payout);
					} else {
						$pl->sendMessage(mc::_("[GoldStd] BooooM!!! You lost"));
					}
				}
				break;
		}
	}

	private function validateSign($pl,$sign) {
		if (!MPMU::access($pl,"goldstd.signs.place")) return false;
		$sn = $this->texts[$sign[0]];
		if (!MPMU::access($pl,"goldstd.signs.place.".$sn)) return false;
		switch ($sn) {
			case "shop":
				$item = $this->parseItemLine($sign[1]);
				if ($item === null) {
					$pl->sendMessage(mc::_("Invalid item line"));
					return false;
				}
				$price = $this->parsePriceLine($sign[2]);
				if ($price === null) {
					$pl->sendMessage(mc::_("Invalid price line"));
					return false;
				}
				break;
			case "effects":
			  $effect = $this->parseEffectLine($sign[1]);
				if ($effect === null) {
					$pl->sendMessage(mc::_("Invalid effects line"));
					return false;
				}
				$price = $this->parsePriceLine($sign[2]);
				if ($price === null) {
					$pl->sendMessage(mc::_("Invalid price line"));
					return false;
				}
				break;
			case "trade":
				$ret = true;
				foreach ([1,2] as $i) {
					$item = $this->parseItemLine($sign[$i]);
					if ($item === null) {
						$pl->sendMessage(mc::_("Invalid item%1% line",$i));
						$ret = false;
					}
				}
				return $ret;
			case "casino":
				list($odds,$payout) = $this->parseCasinoLine($sign[1]);
				if ($odds === null) {
					$pl->sendMessage(mc::_("Invalid odds line"));
					return false;
				}
				$price = $this->parsePriceLine($sign[2]);
				if ($price === null) {
					$pl->sendMessage(mc::_("Invalid price line"));
					return false;
				}
				break;
		}
		return true;
	}

	//////////////////////////////////////////////////////////////////////
	//
	// Event handlers
	//
	//////////////////////////////////////////////////////////////////////
	// Sign functionality
	public function placeSign(SignChangeEvent $ev){
		if($ev->getBlock()->getId() != Block::SIGN_POST &&
			$ev->getBlock()->getId() != Block::WALL_SIGN) return;
		$sign = $ev->getPlayer()->getLevel()->getTile($ev->getBlock());
		if(!($sign instanceof Sign)) return;

		$sign = $ev->getLines();
		if (!isset($this->texts[$sign[0]])) return;
		if (!$this->validateSign($ev->getPlayer(),$sign)) {
			$ev->setLine(0,"[BROKEN]");
			return;
		}
		$ev->getPlayer()->sendMessage(mc::_("[GoldStd] placed sign"));
	}

	public function playerTouchSign(PlayerInteractEvent $ev){
		if($ev->getBlock()->getId() != Block::SIGN_POST &&
			$ev->getBlock()->getId() != Block::WALL_SIGN) return;
		//echo "TOUCHED\n";
		$sign = $ev->getPlayer()->getLevel()->getTile($ev->getBlock());
		if(!($sign instanceof Sign)) return;
		//echo __METHOD__.",".__LINE__."\n";
		$lines = $sign->getText();
		//print_r($lines);
		//print_r($this->texts);
		if (!isset($this->texts[$lines[0]])) return;
		//echo __METHOD__.",".__LINE__."\n";
		if ($ev->getPlayer()->isCreative() || $ev->getPlayer()->isSpectator()) {
			$ev->getPlayer()->sendMessage(mc::_("No trading possible, while in %1% mode",
												MPMU::gamemodeStr($ev->getPlayer()->getGamemode())));
			return;
		}

		$this->activateSign($ev->getPlayer(),$sign);
	}

}
