<?php
namespace aliuly\goldstd;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase as Plugin;

use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\tile\Sign;

use aliuly\goldstd\common\mc;
use aliuly\goldstd\common\MPMU;

class SignMgr implements Listener {
	protected $texts;

	static public function defaults() {
		return [
			"shop" => ["[SHOP]"],
			"casino" => ["[CASINO]"],
		];
	}
	public function __construct(Plugin $plugin,$cfg) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		print_r($cfg);//##DEBUG
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
					return false;
				}
				$price = $this->parsePriceLine($sign[2]);
				if ($price === null) {
					$pl->sendMessage(mc::_("Invalid price line"));
					return false;
				}
				$money = $this->owner->getMoney($pl->getName());
				if ($money < $price) {
					$pl->sendMessage(mc::_("[GoldStd] You do not have enough money"));
				} else {
					$this->owner->grantMoney($pl->getName(),-$price);
					$pl->getInventory()->addItem(clone $item);
					$pl->sendMessage(mc::_("[GoldStd] Item purchased"));
				}
				break;
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
				$money = $this->owner->getMoney($pl->getName());
				if ($money < $price) {
					$pl->sendMessage(mc::_("[GoldStd] You do not have enough moneys"));
				} else {
					$pl->sendMessage(mc::_("[GoldStd] Betting %1%...",$price));
					$this->owner->grantMoney($pl->getName(),-$price);
					$rand = mt_rand(0,$odds);
					if ($rand == 1) {
						$pl->sendMessage(mc::_("[GoldStd] You WON!!! prize...%1%G",
													  $payout));
						$this->owner->grantMoney($pl->getName(),$payout);
					} else {
						$pl->sendMessage(mc::_("[GoldStd] BooooM!!! You lost"));
					}
				}
				break;
		}
		return true;
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
		$this->activateSign($ev->getPlayer(),$sign);
	}

}

/*
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\network\protocol\EntityDataPacket;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\String;
use pocketmine\scheduler\CallbackTask;





	public function updateTimer() {
		foreach ($this->getServer()->getLevels() as $lv) {
			if (count($lv->getPlayers()) == 0) continue;
			foreach ($lv->getTiles() as $tile) {
				if (!($tile instanceof Sign)) continue;
				$sign = $tile->getText();
				if (!isset($this->texts[$sign[0]])) continue;
				$this->updateTile($tile);
			}
		}
	}
}
*/
