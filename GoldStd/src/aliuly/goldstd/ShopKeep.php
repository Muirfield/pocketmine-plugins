<?php
/**
 ** CONFIG:shop-keepers
 **/
namespace aliuly\goldstd;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\item\Item;
use pocketmine\entity\Entity;
use pocketmine\Player;
use pocketmine\level\Location;

use pocketmine\tile\Chest;
use pocketmine\block\Block;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\String;
//use pocketmine\nbt\tag\Float;
use pocketmine\nbt\tag\Int;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\CustomInventory;
use pocketmine\inventory\InventoryType;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\inventory\BaseTransaction;

use aliuly\goldstd\common\Npc;
use aliuly\goldstd\common\mc;
use aliuly\goldstd\common\MPMU;
use aliuly\goldstd\common\PluginCallbackTask;

use pocketmine\utils\Config;


class TraderNpc extends Npc {
}

class TraderInventory extends CustomInventory {
	protected $client;
	public function __construct($holder,$client) {
		$this->client = $client;
		parent::__construct($holder,InventoryType::get(InventoryType::CHEST),
								  [],null,"Trader Inventory");
	}
	public function getClient() { return $this->client; }
}


class ShopKeep implements Listener {
	protected $owner;
	protected $keepers;
	protected $state;

	static public function defaults() {
		return [
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
	static public function cfEnabled($cf){
		return $cf["enable"];
	}
	public function __construct(Plugin $plugin,$xfg) {
		$this->owner = $plugin;
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
		$pos->y = $trader->getFloorY()-2;
		if ($pos->getY() <= 0) $pos->y = 1;
		$nbt = new Compound("", [
			"id" => new String("id","Chest"),
			"x" => new Int("x",$pos->getX()),
			"y" => new Int("y",$pos->getY()),
			"z" => new Int("z",$pos->getZ()),
			"CustomName" => new String("CustomName",$name),
			"Items" => new Enum("Items",[]),
		]);
		$pos->getLevel()->setBlock($pos,Block::get(Block::CHEST));
		$chest = new Chest($pos->getLevel()->getChunk($pos->getX()>>4,$pos->getZ()>>4), $nbt);
		return "";
	}
	//////////////////////////////////////////////////////////////////////
	public function getState($label,$player,$default) {
		$n = MPMU::iName($player);
		if (!isset($this->state[$n])) return $default;
		if (!isset($this->state[$n][$label])) return $default;
		return $this->state[$n][$label];
	}
	public function setState($label,$player,$val) {
		$n = MPMU::iName($player);
		if (!isset($this->state[$n])) $this->state[$n] = [];
		$this->state[$n][$label] = $val;
	}
	public function unsetState($label,$player) {
		$n = MPMU::iName($player);
		if (!isset($this->state[$n])) return;
		if (!isset($this->state[$n][$label])) return;
		unset($this->state[$n][$label]);
	}

	//////////////////////////////////////////////////////////////////////
	public function onQuit(PlayerQuitEvent $ev) {
		$n = MPMU::iName($ev->getPlayer());
		if (isset($this->state[$n])) {
			if (isset($this->state[$n]["trade-inv"]))
				$this->restoreInv($ev->getPlayer());
			unset($this->state[$n]);
		}
	}

	/**
	 * @priority LOWEST
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
		$l = $seller->getLevel();
		$tile = null;
		for($i=-2;$i<=0 && $tile == null;$i--) {
			$pos = $seller->add(0,$i,0);
			$tile = $l->getTile($pos);
			if ($tile instanceof Chest) {
				break;
			} else {
				$tile = null;
			}
		}
		if ($tile == null) {
			$this->owner->getLogger()->error(
				mc::_("Error trading with NPC at %1%,%2%,%3% (%4%)",
						$seller->floorX(),$seller->floorY(),$seller->floorZ(),
						$seller->getLevel()->getName()));
			$buyer->sendMessage(mc::_("Sorry, nothing happens..."));
			return;
		}
		$inv = [ "player" => [], "chest" => null ];
		$inv["money"] = $this->owner->getMoney($buyer);
		var_dump($inv["money"]);//##DEBUG
		$inv["shop"] = $shop;

		foreach ($buyer->getInventory()->getContents() as $slot=>&$item) {
			$inv["player"][$slot] = implode(":",[ $item->getId(),
															  $item->getDamage(),
															  $item->getCount() ]);
		}
		$inv["chest"] = new TraderInventory($tile,$buyer);
		$contents = [];
		foreach ($this->keepers[$shop]["items"] as $idmeta=>$it) {
			$item = clone $it[0];
			$item->setCount(1);
			$contents[] = $item;
		}
		$inv["chest"]->setContents($contents);
		$this->setState("trade-inv",$buyer,$inv);
		$buyer->getInventory()->clearAll();
		$buyer->addWindow($inv["chest"]);
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
	}
	public function onTransaction(InventoryTransactionEvent $ev) {
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$tg = $ev->getTransaction();
		$pl = null;
		$ti = null;
		foreach($tg->getInventories() as $i) {
			echo "INV ".get_class($i)."\n";//##DEBUG
			if ($i instanceof PlayerInventory) {
				$pl = $i->getHolder();
			}
			if ($i instanceof TraderInventory) {
				$ti = $i;
			}
		}
		if ($ti == null) return; // This does not involve us!
		if ($pl == null) {
			$this->owner->getLogger()->error(
				mc::_("Unable to identify player in inventory transaction")
			);
			return;
		}
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		echo "PLAYER ".$pl->getName()." is buying\n";//##DEBUG

		// Calculate total $$
		$xx = $this->getState("trade-inv",$pl,null);
		if ($xx == null) return; // This is a normal Chest transaction...
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
			foreach ($this->traderInvTransaction($t) as $nt) {
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
			if (!isset($this->keepers[$xx["shop"]]["items"][$idmeta])) {
				$this->shopMsg($pl,$xx["shop"],"inventory-error");
				$ev->setCancelled();
				return;
			}
			list($i,$price) = $this->keepers[$xx["shop"]]["items"][$idmeta];
			echo "-$idmeta - $cnt/".$i->getCount()." *  $price ...".(round($cnt/$i->getCount())*$price)."\n";//##DEBUG
			$total += round($cnt/$i->getCount())*$price;
		}
		echo "TOTAL=$total\n";//##DEBUG
		if ($total > $xx["money"]) {
			$this->shopMsg($pl,$xx["shop"],"not-enough-g",$total, $xx["money"]);
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
	public function onClose(InventoryCloseEvent $ev) {
		$pl = $ev->getPlayer();
		$xx = $this->getState("trade-inv",$pl,null);
		if ($xx == null) return;

		// Compute shopping basket
		$basket = [];
		$total = 0;
		foreach ($pl->getInventory()->getContents() as $slot=>$item) {
			if ($item->getId() == Item::AIR) continue;
			$idmeta = implode(":",[$item->getId(),$item->getDamage()]);
			if (!isset($this->keepers[$xx["shop"]]["items"][$idmeta])) continue;
			list($i,$price) = $this->keepers[$xx["shop"]]["items"][$idmeta];
			$total += round($item->getCount()/$i->getCount())*$price;
			$basket[] = [ $item->getId(),$item->getDamage(), $item->getCount() ];
		}
		// Restore original inventory...
		$this->restoreInv($pl);
		// Check-out
		if (count($basket) == 0) {
			$this->shopMsg($pl,$xx["shop"],"next-time");
			return;
		}
		if ($total < $this->owner->getMoney($pl)) {
			$this->owner->grantMoney($pl,-$total);
			if (count($basket) == 1)
				$this->shopMsg($pl,$xx["shop"],"bought-items1",$total,count($basket));
			else
				$this->shopMsg($pl,$xx["shop"],"bought-itemsX",$total,count($basket));
			foreach ($basket as $ck) {
				list($id,$meta,$cnt) = $ck;
				$pl->getInventory()->addItem(Item::get($id,$meta,$cnt));
			}
			$this->shopMsg($pl,$xx["shop"],"thank-you",$total,count($basket));
		} else {
			$this->shopMsg($pl,$xx["shop"],"no-money",$total,count($basket));
		}
	}
	public function playerInvTransaction($t) {
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
		$xx = $this->getState("trade-inv",$pl,null);
		if ($xx == null) return []; // Oops...
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		list($i,$price) = $this->keepers[$xx["shop"]]["items"][$idmeta];
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
	public function traderInvTransaction($t) {
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


	public function restoreInv($pl) {
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$inv = $this->getState("trade-inv",$pl,null);
		if ($inv === null) return;
		$pl->getInventory()->clearAll();
		foreach ($inv["player"] as $slot=>$itdat) {
			list($id,$meta,$cnt) = explode(":",$itdat);
			$item = Item::get($id,$meta,$cnt);
			$pl->getInventory()->setItem($slot,$item);
		}
		$this->unsetState("trade-inv",$pl);
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
						if ($this->getState("spam-$shopid",$pl,null) === null) continue;
						$this->unsetState("spam-$shopid",$pl);
						$this->shopMsg($pl,$shop,"leaving");
						continue;
					}
					// In range check state
					if ($this->getState("trade-inv",$pl,null) !== null) continue;
					$spam = $this->getState("spam-$shopid",$pl,null);
					if ($spam === null) {
						$this->shopMsg($pl,$shop,"welcome");
						$this->setState("spam-$shopid",$pl,$now);
						continue;
					}
					if ($now < $spam+$freq) continue;
					$this->shopMsg($pl,$shop,"buystuff");
					$this->setState("spam-$shopid",$pl,$now);
				}

			}
		}
	}
}
