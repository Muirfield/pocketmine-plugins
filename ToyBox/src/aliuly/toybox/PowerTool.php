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

class PowerTool extends BaseCommand implements Listener {
	protected $items;
	protected $itemwear;
	protected $creative;

	public function __construct($owner,$cfg) {
		parent::__construct($owner);
		$this->enableCmd("powertool",
							  ["description" => mc::_("Enable/Disable PowerTool"),
								"usage" => mc::_("/powertool"),
								"aliases" => ["pt"],
								"permission" => "toybox.powertool"]);

		$this->owner->getServer()->getPluginManager()->registerEvents($this,$this->owner);
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
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "powertool") return false;
		if (!$this->inGame($sender)) return true;
		if (count($args) != 0) return false;

		$state = $this->getState($sender,false);
		if ($state) {
			$sender->sendMessage(mc::_("PowerTool de-actived"));
			$this->setState($sender,false);
		} else {
			$sender->sendMessage(mc::_("PowerTool activated"));
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
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
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
		$ev->setInstaBreak(true);
	}

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
		if ($this->items && $this->itemwear) {
			$hand = $pl->getInventory()->getItemInHand();
			$bl->getLevel()->useBreakOn($bl,$hand,$pl);
			$pl->getInventory()->setItemInHand($hand);
		} else {
			$bl->getLevel()->useBreakOn($bl,null,$pl);
		}
	}
}
