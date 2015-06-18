<?php
/**
 ** CONFIG:main
 **/
namespace aliuly\goldstd;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\Player;

use pocketmine\utils\Config;

use pocketmine\item\Item;

use aliuly\goldstd\common\mc;
use aliuly\goldstd\common\MPMU;
use aliuly\goldstd\common\MoneyAPI;

class Main extends PluginBase implements CommandExecutor {
	protected $currency;
	protected $trading;
	protected $keepers;
	protected $api;

	public function getCurrency() { return $this->currency; }

	public function onEnable(){
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		mc::plugin_init($this,$this->getFile());

		$defaults = [
			"version" => $this->getDescription()->getVersion(),
			"# settings" => "features",
			"settings" => [
				"# currency" => "Item to use for currency",// false or zero disables currency exchange.
				"currency" => "GOLD_INGOT",
				"# signs" => "set to true to enable shops|casino signs",
				"signs" => true,
			],
			"# trade-goods" => "List of tradeable goods",
			"trade-goods" => [],
			"defaults" => TradingMgr::defaults(),
			"# signs" => "Text used to identify GoldStd signs",
			"signs" => SignMgr::defaults(),
			"shop-keepers" => ShopKeep::defaults(),
		];
		$cf = (new Config($this->getDataFolder()."config.yml",
								Config::YAML,$defaults))->getAll();
		if ($cf["settings"]["currency"]) {
			$item = Item::fromString($cf["settings"]["currency"]);
			if ($item->getId() == Item::AIR) {
				$this->getLogger()->error(TextFormat::RED.
												  mc::_("Invalid currency item"));
				$this->currency = Item::GOLD_INGOT;
			} else {
				$this->currency = $item->getId();
			}
			$this->api = null;
		} else {
			// No currency defined, so we use an external API
			$pm = $this->getServer()->getPluginManager();
			if(!($money = $pm->getPlugin("PocketMoney"))
				&& !($money = $pm->getPlugin("EconomyAPI"))
				&& !($money = $pm->getPlugin("MassiveEconomy"))){
				$this->api = null;
				$this->getLogger()->warning(TextFormat::YELLOW.
													 mc::_("Using GOLD_INGOT as currency"));
				$this->currency = Item::GOLD_INGOT;
			} else {
				$this->api = $money;
				$this->currency = false;
				$this->getLogger()->info(TextFormat::BLUE.
												mc::_("Using Money API of %1%",
														$money->getFullName()));
			}
		}
		if ($this->currency || $cf["trade-goods"]) {
			$this->trading = new TradingMgr($this,
													  $cf["trade-goods"],
													  $cf["defaults"]);
		} else {
			$this->trading = null;
			$this->getLogger()->warning(TextFormat::RED.
											 mc::_("Goods trading disabled!"));
		}
		if ($cf["signs"]) {
			new SignMgr($this,$cf["signs"]);
		} else {
			$this->getLogger()->warning(TextFormat::RED.
											 mc::_("SignShops disabled"));
		}
		if ($cf["shop-keepers"]) {
			$this->saveResource("default.skin");
			$this->keepers = new ShopKeep($this,$cf["shop-keepers"]);
			if (!$this->keepers->isEnabled()) {
				$this->keepers = null;
			}
		} else {
			$this->keepers = null;
			$this->getLogger()->warning(TextFormat::RED.
											 mc::_("Shop-Keepers disabled"));
		}
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Command implementations
	//
	//////////////////////////////////////////////////////////////////////
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		switch($cmd->getName()) {
			case "pay":
				if (!$this->trading) {
					$sender->sendMessage(mc::_("PAY command has been disabled"));
					return true;
				}
				if (!MPMU::inGame($sender)) return false;
				if ($sender->isCreative() || $sender->isSpectator()) {
					$sender->sendMessage(mc::_("You cannot use this in creative or specator mode"));
					return true;
				}
				if (count($args) == 1) {
					if (is_numeric($args[0])) {
						$money = intval($args[0]);
						if ($this->getMoney($sender->getName()) < $money) {
							$sender->sendMessage(mc::_("You do not have enough money"));
							return true;
						}
						$this->trading->setAttr($sender,"payment",$money);
						$sender->sendMessage(mc::_("Next payout will be for %1%G",$money));
						return true;
					}
					return false;
				} elseif (count($args) == 0) {
					$sender->sendMessage(mc::_("Next payout will be for %1%G",
														$this->trading->getAttr($sender,"payment")));
					return true;
				}
				return false;
			case "balance":
				if (!MPMU::inGame($sender)) return false;
				if ($sender->isCreative() || $sender->isSpectator()) {
					$sender->sendMessage(mc::_("You cannot use this in creative or specator mode"));
					return true;
				}
				$sender->sendMessage(mc::_("You have %1%G",$this->getMoney($sender->getName())));
				return true;
			case "shopkeep":
				if ($this->keepers) return $this->keepers->subCmd($sender,$args);
				$sender->sendMessage(mc::_("shopkeep command disabled"));
				return true;
		}
		return false;
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Economy/Money API
	//
	//////////////////////////////////////////////////////////////////////
	public function giveMoney($player,$money) {
		if ($this->api) return MoneyAPI::grantMoney($this->api,$player,$money);
		if ($player instanceof Player) {
			$pl = $player;
			$player = $pl->getName();
		} else {
			$pl = $this->getServer()->getPlayer($player);
			if (!$pl) return false;
		}
		if ($pl->isCreative() || $pl->isSpectator()) return false;
		while ($money > 0) {
			$item = Item::get($this->currency);
			if ($money > $item->getMaxStackSize()) {
				$item->setCount($item->getMaxStackSize());
			} else {
				$item->setCount($money);
			}
			$money -= $item->getCount();
			$pl->getInventory()->addItem(clone $item);
		}
		return true;
	}
	public function takeMoney($player,$money) {
		if ($this->api) return MoneyAPI::grantMoney($this->api,$player,-$money);
		if ($player instanceof Player) {
			$pl = $player;
			$player = $pl->getName();
		} else {
			$pl = $this->getServer()->getPlayer($player);
			if (!$pl) return false;
		}
		if ($pl->isCreative() || $pl->isSpectator()) return false;
		foreach ($pl->getInventory()->getContents() as $slot => &$item) {
			if ($item->getId() != $this->currency) continue;
			if ($item->getCount() > $money) {
				$item->setCount($item->getCount() - $money);
				$pl->getInventory()->setItem($slot,clone $item);
				break;
			}
			$money -= $item->getCount();
			$pl->getInventory()->clear($slot);
			if (!$money) break;
		}
		if ($money) return $money; // They don't have enough money!
		return true;
	}
	public function grantMoney($p,$money) {
		if ($this->api) return MoneyAPI::grantMoney($this->api,$p,$money);
		if ($money < 0) {
			return $this->takeMoney($p,-$money);
		} elseif ($money > 0) {
			return $this->giveMoney($p,$money);
		} else {
			return true;
		}
	}
	public function getMoney($player) {
		if ($this->api) return MoneyAPI::getMoney($this->api,$player);
		if ($player instanceof Player) {
			$pl = $player;
			$player = $pl->getName();
		} else {
			$pl = $this->getServer()->getPlayer($player);
			if (!$pl) return null;
		}
		$g = 0;
		if ($pl->isCreative() || $pl->isSpectator()) return null;
		foreach ($pl->getInventory()->getContents() as $slot => &$item) {
			if ($item->getId() != $this->currency) continue;
			$g += $item->getCount();
		}
		return $g;
	}
	public function setMoney($player,$money) {
		$now = $this->getMoney($player);
		if ($money < $now) {
			return $this->takeMoney($player, $now - $money);
		} elseif ($money > $now) {
			return $this->giveMoney($player, $money - $now);
		} elseif ($money == $now) return true; // Nothing to do!
		$this->getLogger()->error("INTERNAL ERROR AT ".__FILE__.",".__LINE__);
		return false;
	}
}
