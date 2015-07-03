<?php
namespace aliuly\toybox;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;

use pocketmine\item\ItemBlock;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use aliuly\toybox\common\mc;

class VeinMiner extends BaseCommand implements Listener {
	protected $items;
	protected $itemwear;
	protected $creative;
	protected $blocklimit;
	protected $broadcast;
	protected $sides;
	protected $revert;

	public function __construct($owner,$cfg) {
		parent::__construct($owner);
		$this->enableCmd("veinminer",
							  ["description" => mc::_("Enable/Disable VeinMiner"),
								"usage" => mc::_("/veinminer"),
								"aliases" => ["vm"],
								"permission" => "toybox.veinminer"]);

		$this->owner->getServer()->getPluginManager()->registerEvents($this,$this->owner);
		if ($cfg["need-item"]) {
			$this->items = [];
			foreach ($cfg["ItemIDs"] as $i) {
				$item = $this->owner->getItem($i,false,"veinminer");
				if ($item === null) continue;
				$this->items[$item->getId()] = $item->getId();
			}
			$this->itemwear = $cfg["item-wear"];
		} else {
			$this->items = false;
		}
		$this->creative = $cfg["creative"];
		$this->blocklimit = $cfg["max-blocks"];
		$this->broadcast = $cfg["broadcast-use"];

		$this->sides = [];
		foreach ([Vector3::SIDE_NORTH,Vector3::SIDE_SOUTH,
							Vector3::SIDE_EAST,Vector3::SIDE_WEST,
							Vector3::SIDE_UP,Vector3::SIDE_DOWN] as $dir) {
			$this->sides[$dir] = $dir;
		}
		$this->revert[Vector3::SIDE_NORTH] = Vector3::SIDE_SOUTH;
		$this->revert[Vector3::SIDE_SOUTH] = Vector3::SIDE_NORTH;
		$this->revert[Vector3::SIDE_WEST] = Vector3::SIDE_EAST;
		$this->revert[Vector3::SIDE_EAST] = Vector3::SIDE_WEST;
		$this->revert[Vector3::SIDE_UP] = Vector3::SIDE_DOWN;
		$this->revert[Vector3::SIDE_DOWN] = Vector3::SIDE_UP;

	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "veinminer") return false;
		if (!$this->inGame($sender)) return true;
		if (count($args) != 0) return false;

		$state = $this->getState($sender,false);
		if ($state) {
			$sender->sendMessage(mc::_("VeinMiner de-actived"));
			$this->setState($sender,false);
		} else {
			$sender->sendMessage(mc::_("VeinMiner activated"));
			$this->setState($sender,true);
		}
		return true;
	}
	/////////////////////////////////////////////////////////////////////////
	//
	// Event handlers
	//
	/////////////////////////////////////////////////////////////////////////
	public function onBreak(BlockBreakEvent $ev) {
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if ($ev->isCancelled()) return;
		$pl = $ev->getPlayer();
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if (($m = $this->getState($pl,false)) === false) return;
		echo __METHOD__.",".__LINE__.":: m=$m\n";//##DEBUG
		if ($m === "insta-break") {
			$ev->setInstaBreak(true);
			return;
		}
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if ($ev->getBlock()->getId() == Block::AIR) return;
		if (!$pl->isCreative() || !$this->creative) {
			if ($this->items && !isset($this->items[$ev->getItem()->getId()])) {
				//echo "Not using an PickAxe\n"; //##DEBUG
				return;
			}
		}
		if (substr($ev->getBlock()->getName(),-4) !== " Ore") return;
		// This is an ore...
		$ev->setInstaBreak(true);
		$pl->sendMessage(mc::_("Using VeinMinger"));
		$this->setState($pl,"insta-break");//prevents infinite loops...
		$c = $this->veinsearch($ev->getBlock(),$this->blocklimit,$pl,$this->sides);
		$this->setState($pl,true);
		if ($c && $this->broadcast)
			$this->owner->getServer()->broadcastMessage(
				mc::n(mc::_("%1% used VeinMiner (one block affected)",$pl->getDisplayName()),
							mc::_("%1% used VeinMiner (%2% blocks affected)",$pl->getDisplayName(),$c),
							$c));
	}
	public function veinsearch($block,$maxblocks,$pl,$sides) {
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if ($maxblocks <= 0) return  0;//Trivial case

		$count = 0;

		foreach ($sides as $dir) {
			$nextblock = $block->getSide($dir);
			if ($nextblock->getId() != $block->getId()) continue;

			--$maxblocks;
			++$count;

			if ($this->items && $this->itemwear) {
				$hand = $pl->getInventory()->getItemInHand();
				$nextblock->getLevel()->useBreakOn($nextblock,$hand,$pl);
				$pl->getInventory()->setItemInHand($hand);
			} else {
				$nextblock->getLevel()->useBreakOn($nextblock,null,$pl);
			}
			$nextsides = $this->sides;
			unset($nextsides[$this->revert[$dir]]); // Make sure we don't backtrack
			$k = $this->veinsearch($nextblock,$maxblocks,$pl,$nextsides);
			$maxblocks -= $k;
			$count += $k;

			if ($maxblocks <= 0) break;
		}
		return $count;
	}
  /*
	public function onTouch(PlayerInteractEvent $ev){
		if ($ev->isCancelled()) return;
		$pl = $ev->getPlayer();
		if (!$this->getState($pl,false)) return;
		if ($ev->getBlock()->getId() == Block::AIR) return;
		if (!$pl->isCreative() || !$this->creative) {
			if ($this->items && !isset($this->items[$ev->getItem()->getId()])) {
				//echo "Not using an PickAxe\n"; //##DEBUG
				return;
			}
		}
		$bl = $ev->getBlock();
	}*/
}
