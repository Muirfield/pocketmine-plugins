<?php
//= cmd:plenty,Inventory_Management
//: When in survival, make sure that a player never runs out of items
//> usage: **plenty**
//:
//: When **plenty** is on, the player will not run out of items.  Whenever
//: the current block being placed is about to run out, you are given
//: new blocks of the same type automatically.

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;

use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\block\BlockPlaceEvent;

use aliuly\common\BasicCli;
use aliuly\common\mc;
use aliuly\common\MPMU;
use aliuly\common\PermUtils;
use aliuly\common\InvUtils;

class CmdPlenty extends BasicCli implements Listener,CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);

		PermUtils::add($this->owner, "gb.cmd.plenty", "Give players plenty of stuff", "op");
		$this->enableCmd("plenty",
							  ["description" => mc::_("Make sure that player does not run out of stuff"),
								"usage" => mc::_("/plenty"),
								"permission" => "gb.cmd.plenty"]);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function hasPlenty($player) {
		return $this->getState($player,false);
	}
	public function setPlenty($player, $mode) {
		if ($mode) {
			$this->setState($player,true);
		} else {
			$this->unsetState($player);
		}
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (count($args) !== 0) return false;
		if ($cmd->getName() != "plenty") return false;
		if (!MPMU::inGame($sender)) return true;
		$state = $this->getState($sender,false);
		if ($state) {
			$sender->sendMessage(mc::_("Plenty is off"));
			$this->unsetState($sender);
		} else {
			$sender->sendMessage(mc::_("Plenty is ON"));
			$this->setState($sender,true);
		}
		return true;
	}
	public function onConsume(PlayerItemConsumeEvent $ev) {
		$this->checkPlenty($ev->getPlayer(),$ev->getItem());
	}
	public function onPlace(BlockPlaceEvent $ev) {
		$this->checkPlenty($ev->getPlayer(),$ev->getItem());
	}
	private function checkPlenty($player,$item) {
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if (!$this->getState($player,false)) return;
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$count =  InvUtils::countInvItem($player,$item);
		echo __METHOD__.",".__LINE__." count=$count\n";//##DEBUG
		if ($count > 3) return;
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$item->setCount(3);
		$player->getInventory()->addItem(clone $item);
		$player->getInventory()->sendContents($player);

	}

}
