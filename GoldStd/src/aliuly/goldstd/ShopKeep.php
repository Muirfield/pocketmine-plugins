<?php
/**
 ** CONFIG:shop-keepers
 **/
namespace aliuly\goldstd;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\entity\Entity;
use pocketmine\level\Location;
use pocketmine\item\Item;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;

//use pocketmine\tile\Chest;
//use pocketmine\block\Block;
//use pocketmine\nbt\tag\Compound;
//use pocketmine\nbt\tag\Byte;
//use pocketmine\nbt\tag\Enum;
//use pocketmine\nbt\tag\String;
//use pocketmine\nbt\tag\Float;
//use pocketmine\nbt\tag\Int;
//use pocketmine\event\player\PlayerQuitEvent;
//use pocketmine\inventory\PlayerInventory;
//use pocketmine\inventory\ChestInventory;
//use pocketmine\inventory\CustomInventory;
//use pocketmine\inventory\InventoryType;
//use pocketmine\event\inventory\InventoryTransactionEvent;
//use pocketmine\event\inventory\InventoryCloseEvent;
//use pocketmine\inventory\BaseTransaction;

use aliuly\goldstd\common\Npc;
use aliuly\goldstd\common\mc;
use aliuly\goldstd\common\MPMU;
use aliuly\goldstd\common\PluginCallbackTask;
use aliuly\common\ShoppingCart;

class TraderNpc extends Npc {
}

class ShopKeep implements Listener {
	protected $owner;
	protected $keepers;
	protected $state;
	protected $cart;

	static public function defaults() {
		return [
			//= cfg:shop-keepers
			"# enable" => "enable/disable shopkeep functionality",
			"enable" => true,
			"# range" => "How far away to engage players in chat",
			"range" => 4,
			"# ticks" => "How often to check player positions",
			"ticks" => 20,
			"# freq" => "How often to  spam players (in seconds)",
			"freq" => 60,
		];
	}
	static public function cfEnabled($cfg) {
		return $cfg["enable"];
	}
	public function __construct(Plugin $plugin,$xfg) {
		$this->owner = $plugin;
		if (!$xfg["enable"]) {
			$this->keepers = null;
			return;
		}
		$this->keepers = [];
		$cfg = (new Config($plugin->getDataFolder()."shops.yml",
								Config::YAML))->getAll();

		$this->state = [];
		foreach ($cfg as $i=>$j) {
			$this->keepers[$i] = [];
			if (isset($j["messages"])) {
				$this->keepers[$i]["messages"] = $j["messages"];
			} else {
				$this->keepers[$i]["messages"] = [];
			}
			$this->keepers[$i]["attack"] = isset($j["attack"]) ? $j["attack"] : 5;
			$this->keepers[$i]["slim"] = isset($j["slim"]) ? $j["slim"] : false;
			$this->keepers[$i]["displayName"] = isset($j["display"]) ? $j["display"] : "default";
			// Load the skin in memory
			if (is_file($plugin->getDataFolder().$j["skin"])) {
				$this->keepers[$i]["skin"] =
					zlib_decode(file_get_contents($plugin->getDataFolder().$j["skin"]));
			} else {
				$this->keepers[$i]["skin"] = null;
			}
			if (isset($cfg[$i]["msgs"]))
				$this->keepers[$i]["msgs"] = $cfg[$i]["msgs"];

			$items = isset($cfg[$i]["items"]) && $cfg[$i]["items"] ?
				$cfg[$i]["items"] : [ "IRON_SWORD,2","APPLE,10,1" ];
			$this->keepers[$i]["items"] = [];
			foreach ($items as $n) {
				$t = explode(",",$n);
				if (count($t) < 2 || count($t) >3) {
					$plugin->getLogger()->error(mc::_("Item error: %1%",$n));
					continue;
				}
				$item = Item::fromString(array_shift($t));
				if ($item->getId() == Item::AIR) {
					$plugin->getLogger()->error(mc::_("Unknown Item error: %1%",$n));
					continue;
				}
				$price = intval(array_pop($t));
				if ($price <= 0) {
					$plugin->getLogger()->error(mc::_("Invalid price: %1%",$n));
					continue;
				}
				if (count($t)) {
					$qty = intval($t[0]);
					if ($qty <= 0 || $qty >= $item->getMaxStackSize()) {
						$plugin->getLogger()->error(mc::_("Bad quantity: %1%",$n));
						continue;
					}
					$item->setCount($qty);
				}
				$this->keepers[$i]["items"][implode(":",[$item->getId(),$item->getDamage()])] = [ $item,$price ];
			}
			if (count($this->keepers[$i]["items"])) continue;
			$plugin->getLogger()->error(mc::_("ShopKeep %1% disabled!",$i));
			unset($this->keepers[$i]);
			continue;
		}
		if (count($this->keepers) == 0) {
			$plugin->getLogger()->error(mc::_("No shopkeepers found!"));
			$this->keepers = null;
			return;
		}
		Entity::registerEntity(TraderNpc::class,true);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);

		$this->owner->getServer()->getScheduler()->scheduleRepeatingTask(
			new PluginCallbackTask($this->owner,[$this,"spamPlayers"],[$xfg["range"],$xfg["freq"]]),$xfg["ticks"]
		);
		$this->cart = new ShoppingCart($this->owner);
	}

	public function isEnabled() {
		return $this->keepers !== null;
	}
	//////////////////////////////////////////////////////////////////////
	public function subCmd($c,$args) {
		if (count($args) == 0) return false;
		$cmd = strtolower(array_shift($args));
		switch ($cmd) {
			case "spawn":
				if (count($args) > 0) {
					if (preg_match('/^(\d+),(\d+),(\d+),(\d+),(\d+)$/',$args[0],$mv)){
						$level = MPMU::inGame($c,false) ? $c->getLevel() : $this->owner->getServer()->getDefaultLevel();
						$pos = new Location($mv[1],$mv[2],$mv[3],$mv[4],$mv[5],$level);
						array_shift($args);
					} elseif (preg_match('/^(\d+),(\d+),(\d+),(\d+),(\d+),(\S+)$/',$args[0],$mv)){
						$level = $this->owner->getServer()->getLevelByName($mv[6]);
						if ($level === null) {
							$c->sendMessage(mc::_("World %1% not found",$mv[6]));
							return true;
						}
						$pos = new Location($mv[1],$mv[2],$mv[3],$mv[4],$mv[5],$level);
						array_shift($args);
					} elseif (preg_match('/^(\d+),(\d+),(\d+)$/',$args[0],$mv)){
						$level = MPMU::inGame($c,false) ? $c->getLevel() : $this->owner->getServer()->getDefaultLevel();
						$pos = new Location($mv[1],$mv[2],$mv[3],0.0,0.0,$level);
						array_shift($args);
					} elseif (($pos = $this->owner->getServer()->getPlayer($args[0])) == null) {
						if (!MPMU::inGame($c)) return true;
						$pos = $c;
					} else {
						array_shift($args);
					}
				}
				if (count($args) == 0) $args = ["default"];
				$shopkeep = implode(" ",$args);
				$ms = $this->spawn($pos,$shopkeep);
				if ($ms != "")
					$c->sendMessage($ms);
				else
					$c->sendMessage(mc::_("Spawned shopkeep: %1%",$shopkeep));
				return true;
			default:
				$c->sendMessage(mc::_("%1%: Unknown sub-command",$cmd));
		}
		return false;
	}

	//////////////////////////////////////////////////////////////////////

	public function spawn($pos,$name) {
		if (!$this->isEnabled())
			return mc::_("ShopKeeper functionality disabled");
		if (!isset($this->keepers[$name])) {
			return mc::_("ShopKeeper %1% not found",$name);
		}
		$trader = TraderNpc::spawnNpc($this->keepers[$name]["displayName"],
												$pos,TraderNpc::class,[
													"skin" => $this->keepers[$name]["skin"],
													"slim" => $this->keepers[$name]["slim"],
													"shop" => ["String",$name],
												]);
		$trader->spawnToAll();
		return "";
	}
	//////////////////////////////////////////////////////////////////////
	/**
	 * @priority LOW
	 */
	public function onEntityInteract(EntityDamageEvent $ev) {
		if ($ev->isCancelled()) return;
		if(!($ev instanceof EntityDamageByEntityEvent)) return;
		$giver = $ev->getDamager();
		if (!($giver instanceof Player)) return;
		$taker = $ev->getEntity();
		if (!($taker instanceof TraderNpc)) return;
		$ev->setCancelled(); // OK, now what...
		if ($giver->isCreative() || $giver->isSpectator()) {
			$giver->sendMessage(mc::_("No purchases while in %1% mode.",
																MPMU::gamemodeStr($giver->getGamemode())));
			return;
		}
		$shop = $taker->namedtag->shop->getValue();
		if (!isset($this->keepers[$shop])) {
			$this->owner->getLogger()->error(
				mc::_("Invalid shop %5% for NPC at %1%,%2%,%3% (%4%)",
						$taker->floorX(),$taker->floorY(),$taker->floorZ(),
						$taker->getLevel()->getName(),$shop));
			$giver->sendMessage(mc::_("Sorry, shop is closed!"));
			return;
		}

		$hand = $giver->getInventory()->getItemInHand();
		if ($this->owner->getCurrency() !== false ?
			 $hand->getId() == $this->owner->getCurrency() :
			 $hand->getId() == Item::GOLD_INGOT) {
			// OK, we want to buy stuff...

			$this->owner->getServer()->getScheduler()->scheduleDelayedTask(
				new PluginCallbackTask($this->owner,[$this,"startTrade"],
											  [$giver,$taker,$shop]),10);
		} else {
			if ($this->owner->isWeapon($hand)) {
				$this->shopMsg($giver,$shop,"under-attack");
				$giver->attack($this->keepers[$shop]["attack"],
										new  EntityDamageByEntityEvent(
												$taker,$giver,
												EntityDamageEvent::CAUSE_ENTITY_ATTACK,
												$this->keepers[$shop]["attack"],1.0));
			} else {
				$this->shopMsg($giver,$shop,"help-info");
			}
		}
	}
	/* Buy stuf...*/
	public function startTrade($buyer,$seller,$shop) {
		if (!MPMU::access($buyer,"goldstd.shopkeep.shop")) return;
		$this->cart->start($buyer,$this->keepers[$shop]["items"]);
	}
	protected function getShopMsg($pl,$shop,$msg,$args) {
		if (!isset($this->keepers[$shop]["messages"][$msg])) return $msg;

		$fmt = $this->keepers[$shop]["messages"][$msg];
		$msg = is_array($fmt) ? $fmt[array_rand($fmt)] : $fmt;

		if (count($args)) {
			$vars = [ "%%" => "%" ];
			$i = 1;
			foreach ($args as $j) {
				$vars["%$i%"] = $j;
				++$i;
			}
			$msg = strtr($msg,$vars);
		}
		return $msg;
	}
	protected function shopMsg($pl,$shop,...$args) {
		$msg = array_shift($args);
		$pl->sendMessage($this->getShopMsg($pl,$shop,$msg,$args));
	}


	public function spamPlayers($range,$freq) {
		$now = time();
		foreach ($this->owner->getServer()->getLevels() as $lv) {
			if (count($lv->getPlayers()) == 0) continue;
			foreach ($lv->getEntities() as $et) {
				if (!($et instanceof TraderNpc)) continue;
				// OK, this could be a shop...
				$shop = $et->namedtag->shop->getValue();
				if (!isset($this->keepers[$shop])) continue;
				$shopid = $shop."-".$et->getId();
				foreach ($lv->getPlayers() as $pl) {
					if ($pl->isCreative() || $pl->isSpectator()) continue;
					if ($et->distanceSquared($pl) > $range*$range) {
						if ($this->cart->getState("spam-$shopid",$pl,null) === null) continue;
						$this->cart->unsetState("spam-$shopid",$pl);
						$this->shopMsg($pl,$shop,"leaving");
						continue;
					}
					// In range check state
					if ($this->cart->getState(ShoppingCart::tag,$pl,null) !== null) continue;
					$spam = $this->cart->getState("spam-$shopid",$pl,null);
					if ($spam === null) {
						$this->shopMsg($pl,$shop,"welcome");
						$this->cart->setState("spam-$shopid",$pl,$now);
						continue;
					}
					if ($now < $spam+$freq) continue;
					$this->shopMsg($pl,$shop,"buystuff");
					$this->cart->setState("spam-$shopid",$pl,$now);
				}

			}
		}
	}
}
