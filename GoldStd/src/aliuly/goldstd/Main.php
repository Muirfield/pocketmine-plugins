<?php
/**
 ** CONFIG:main
 **/
namespace aliuly\goldstd;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandExecutor;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;

use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\utils\Config;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;

use aliuly\goldstd\common\mc;
use aliuly\goldstd\common\MPMU;


class Main extends PluginBase implements CommandExecutor,Listener {
	public static $defaults;
	protected $currency;
	protected $state;

	public function getItem($txt,$default) {
		$r = explode(":",$txt);
		if (count($r)) {
			if (!isset($r[1])) $r[1] = 0;
			$item = Item::fromString($r[0].":".$r[1]);
			if (isset($r[2])) $item->setCount(intval($r[2]));
			if ($item->getId() != Item::AIR) {
				return $item;
			}
		}
		$this->getLogger()->error(mc::_("%1%: Invalid item %2%, using default",
												  $msg,$txt));
		$item = Item::fromString($default.":0");
		$item->setCount(1);
		return $item;
	}

	//////////////////////////////////////////////////////////////////////
	//
	// Standard call-backs
	//
	//////////////////////////////////////////////////////////////////////
	public function onEnable(){
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		mc::plugin_init($this,$this->getFile());

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$defaults = [
			"version" => $this->getDescription()->getVersion(),

			"# settings" => "features",
			"settings" => [
				"# currency" => "Item to use for currency",
				"currency" => "GOLD_INGOT",
				"# signs" => "set to true to enable shops|casino signs",
				"signs" => true,
			],
			"# defaults" => "Default values for payments",
			"defaults" => [
				"# payment" => "default payment when tapping on a player",
				"payment" => 1,
				"# timeout" => "how long a transaction may last",
				"timeout" => 30,
			],
			"# signs" => "Text used to identify GoldStd signs",
			"signs" => SignMgr::defaults(),
		];
		$cf = (new Config($this->getDataFolder()."config.yml",
								Config::YAML,$defaults))->getAll();
		$this->currency = $this->getItem($cf["settings"]["currency"],
													Item::GOLD_INGOT,"currency")->getId();
		self::$defaults = $cf["defaults"];
		if ($cf["signs"]) {
			$this->getLogger()->info(TextFormat::AQUA.mc::_("Registered listener..."));
			new SignMgr($this,$cf["signs"]);
		}
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Economy/Money API
	//
	//////////////////////////////////////////////////////////////////////
	public function giveMoney($player,$money) {
		$pl = $this->getServer()->getPlayer($player);
		if (!$pl) return false;
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
		$pl = $this->getServer()->getPlayer($player);
		if (!$pl) return null;
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
		if ($money < 0) {
			return $this->takeMoney($p,-$money);
		} elseif ($money > 0) {
			return $this->giveMoney($p,$money);
		} else {
			return true;
		}
	}
	public function getMoney($player) {
		$pl = $this->getServer()->getPlayer($player);
		if (!$pl) return null;
		$g = 0;
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
	//////////////////////////////////////////////////////////////////////
	//
	// Manipulate internal state
	//
	//////////////////////////////////////////////////////////////////////
	protected function getAttr($pl,$attr, $def = null) {
		if ($def === null) $def = self::$defaults[$attr];
		if ($pl instanceof Player) $pl = $pl->getName();
		if (!isset($this->state[$pl])) {
			$this->state[$pl] = [ $attr => $def ];
		}
		if (!isset($this->state[$pl][$attr])) {
			$this->state[$pl][$attr] =  $def;
		}
		return $this->state[$pl][$attr];
	}
	protected function setAttr($pl,$attr, $val) {
		if ($pl instanceof Player) $pl = $pl->getName();
		if (!isset($this->state[$pl])) {
			$this->state[$pl] = [ $attr => $val ];
		}
		$this->state[$pl][$attr] =  $val;
		return;
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Command implementations
	//
	//////////////////////////////////////////////////////////////////////
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		switch($cmd->getName()) {
			case "pay":
				if (!MPMU::inGame($sender)) return false;
				if (count($args) == 1) {
					if (is_numeric($args[0])) {
						$money = intval($args[0]);
						if ($this->getMoney($sender->getName()) < $money) {
							$sender->sendMessage(mc::_("You do not have enough money"));
							return true;
						}
						$this->setAttr($sender,"payment",$money);
						$sender->sendMessage(mc::_("Next payout will be for %1%G",$money));
						return true;
					}
					return false;
				} elseif (count($args) == 0) {
					$sender->sendMessage(mc::_("Next payout will be for %1%G",
														$this->getAttr($sender,"payment")));
					return true;
				}
				return false;
			case "balance":
				if (!MPMU::inGame($sender)) return false;
				$sender->sendMessage(mc::_("You have %1%G",$this->getMoney($sender->getName())));
				return true;
		}
		return false;
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Event handlers
	//
	//////////////////////////////////////////////////////////////////////

	public function onPlayerQuitEvent(PlayerQuitEvent $e) {
		$pl = $e->getPlayer()->getName();
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
		$hand = $giver->getInventory()->getItemInHand();
		if ($hand->getId() != $this->currency) return;
		if ($taker instanceof Player) {
			$ev->setCancelled(); // OK, we want to pay, not to fight!
			$this->onPlayerPaid($giver,$taker);
		} //else paying an Entity!
	}
	/*
	 * Events
	 -  $this->getServer()->getPluginManager()->callEvent(new Event(xxx))
	*/
	public function onPlayerPaid(Player $giver,Player $taker) {
		$gg = $this->getAttr($giver,"payment");
		$this->setAttr($giver,"payment",self::$defaults["payment"]);
		if ($this->getMoney($giver->getName()) < $gg) {
			$giver->sendMessage(mc::_("You don't have that much money!"));
			return;
		}
		$this->takeMoney($giver->getName(),$gg);
		$this->giveMoney($taker->getName(),$gg);
		list($when,$amt,$ptaker) = $this->getAttr($giver,"counter",[0,0,""]);
		if (time() - $when < self::$defaults["timeout"] && $ptaker == $taker->getName()) {
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
											  $this->getMoney($giver->getName()),$amt));
			$taker->sendTip(mc::_("Received %2%G, you now have %1%G",
											  $this->getMoney($taker->getName()).$amt));
		} else {
			$giver->sendMessage(mc::_("Paid %2%G, you now have %1%G",
											  $this->getMoney($giver->getName()),$amt));
			$taker->sendMessage(mc::_("Received %2%G, you now have %1%G",
											  $this->getMoney($taker->getName()).$amt));
		}
	}
}
