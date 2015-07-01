<?php
namespace aliuly\toybox;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;

use pocketmine\item\ItemBlock;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\event\block\BlockBreakEvent;
use aliuly\toybox\common\mc;

class TreeCapitator extends BaseCommand implements Listener {
	protected $items;
	protected $leaves;
	protected $itemwear;
	protected $broadcast;
	protected $creative;

	public function __construct($owner,$cfg) {
		parent::__construct($owner);
		$this->enableCmd("treecapitator",
							  ["description" => mc::_("Enable/Disable treecapitator"),
								"usage" => mc::_("/treecapitator"),
								"aliases" => ["tc"],
								"permission" => "toybox.treecapitator"]);
		$this->owner->getServer()->getPluginManager()->registerEvents($this,$this->owner);

		$this->leaves = $cfg["break-leaves"];
		if ($cfg["need-item"]) {
			$this->items = [];
			foreach ($cfg["ItemIDs"] as $i) {
				$item = $this->owner->getItem($i,false,"powertool");
				if ($item === null) continue;
				$this->items[$item->getId()] = $item->getId();
			}
			$this->itemwear = $cfg["item-wear"];
		} else {
			$this->items = false;
		}
		$this->creative = $cfg["creative"];
		$this->broadcast = $cfg["broadcast-use"];
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "treecapitator") return false;
		if (!$this->inGame($sender)) return true;
		if (count($args) != 0) return false;

		$state = $this->getState($sender,false);
		if ($state) {
			$sender->sendMessage(mc::_("TreeCapitator de-actived"));
			$this->setState($sender,false);
		} else {
			$sender->sendMessage(mc::_("TreeCapitator activated"));
			$this->setState($sender,true);
		}
		return true;
	}
	/////////////////////////////////////////////////////////////////////////
	//
	// Event handlers
	//
	/////////////////////////////////////////////////////////////////////////
	public function onBlockBreak(BlockBreakEvent $ev){
		if ($ev->isCancelled()) return;
		$pl = $ev->getPlayer();


		if (!$this->getState($pl,false)) return;

		if (!$pl->isCreative() || !$this->creative) {
			if ($this->items && !isset($this->items[$ev->getItem()->getId()])) {
				echo "Not using an Axe\n"; //##DEBUG
				return;
			}
		}
		if ($this->leaves) {
			$damage = $this->destroyTree($ev->getBlock());
		} else {
			$damage = $this->destroyTrunk($ev->getBlock());
		}
		if ($damage && $this->items && $this->itemwear) {
			$hand = $pl->getInventory()->getItemInHand();
			$hand->setDamage($hand->getDamage() + $this->itemwear * $damage);
			$pl->getInventory()->setItemInHand($hand);
			if ($this->broadcast)
				$this->owner->getServer()->broadcastMessage(mc::_(
																		  "%1% used TreeCapitator",$pl->getName()));
			else
				$pl->sendMessage(mc::_("Used TreeCapitator"));
		}
	}
	/////////////////////////////////////////////////////////////////////////
	//
	// Tree Capitation
	//
	/////////////////////////////////////////////////////////////////////////
	private function destroyTree(Block $bl) {
		$damage = 0;
		if ($bl->getId() != Block::WOOD) return $damage;
		$down = $bl->getSide(Vector3::SIDE_DOWN);
		if ($down->getId() == Block::WOOD) return $damage;
		$l = $bl->getLevel();

		$cX = $bl->getX();
		$cY = $bl->getY();
		$cZ = $bl->getZ();

		for ($y = $cY+1; $y < 128; ++$y) {
			if ($l->getBlockIdAt($cX,$y,$cZ) == Block::AIR) break;

			for ($x = $cX - 4; $x <= $cX + 4; ++$x) {
				for ($z = $cZ - 4; $z <= $cZ + 4; ++$z) {
					$bl = $l->getBlock(new Vector3($x,$y,$z));

					if ($bl->getId() != Block::WOOD &&
						 $bl->getId() != Block::LEAVES) continue;

					++$damage;
					if (mt_rand(1,10) < 3) {
						$l->dropItem($bl,new ItemBlock($bl));
					}
					$l->setBlockIdAt($x,$y,$z,0);
					$l->setBlockDataAt($x,$y,$z,0);
				}
			}
		}
		return $damage;
	}
	private function destroyTrunk(Block $bl) {
		$damage = 0;
		if ($bl->getId() != Block::WOOD) return $damage;
		$down = $bl->getSide(Vector3::SIDE_DOWN);
		if ($down->getId() == Block::WOOD) return $damage;
		$l = $bl->getLevel();
		for ($y= $bl->getY()+1; $y < 128; ++$y) {
			$x = $bl->getX();
			$z = $bl->getZ();
			$bl = $l->getBlock(new Vector3($x,$y,$z));
			if ($bl->getId() != Block::WOOD) break;
			++$damage;
			$l->dropItem($bl,new ItemBlock($bl));
			$l->setBlockIdAt($x,$y,$z,0);
			$l->setBlockDataAt($x,$y,$z,0);
		}
		return $damage;
	}
}
