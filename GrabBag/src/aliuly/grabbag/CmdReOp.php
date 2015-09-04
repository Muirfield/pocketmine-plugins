<?php
//= cmd:reop,Player_Management
//: Let's op drop priviledges temporarily
//> usage: **reop** [_player_]
//:
//: Will drop **op** priviledges from player.  Player can get **op**
//: back at any time by enter **reop** again or by disconnecting.

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;

class CmdReOp extends BasicCli implements Listener,CommandExecutor {
	protected $reops;
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("reop",
							  ["description" => mc::_("Temporarily deops administrators"),
								"usage" => mc::_("/reop [player]"),
								"permission" => "gb.cmd.reop"]);
    $this->reops = [];
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function isReOp($target) {
		return isset($this->reops[strtolower($target->getName())]);
	}
	public function reopPlayer($target) {
		$n = strtolower($target->getName());
    if ($target->isOp()) {
			$this->reops[$n] = true;
      $target->setOp(false);
			$target->sendMessage(mc::_("You are no longer Op"));
			return true;
		}
		// Player wants to resume op
    if (isset($this->reops[$n])) {
      $target->setOp(true);
			$target->sendMessage(mc::_("You are now Op"));
      unset($this->reops[$n]);
      return true;
    }
		return false;
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
    if (count($args) > 1) return false;
		if (count($args) == 0) {
			if (!MPMU::inGame($sender)) return true;
			$target = $sender;
			$other = false;
		} else {
			if (!MPMU::access($sender,"gb.cmd.".$cmd->getName().".others")) return true;
			$target = $this->owner->getServer()->getPlayer($args[0]);
			if ($target === null) {
				$sender->sendMessage(mc::_("%1% can not be found.",$args[0]));
				return true;
			}
			$other = true;
		}
    $n = strtolower($target->getName());
    if ($target->isOp()) {
      // Player is dropping from op...
      $this->reops[$n] = true;
      $target->setOp(false);
      if ($other) $sender->sendMessage(mc::_("%1% is no longer Op",$target->getDisplayName()));
      $target->sendMessage(mc::_("You are no longer Op"));
      return true;
    }

    // Player wants to resume op
    if (isset($this->reops[$n])) {
      $target->setOp(true);
      unset($this->reops[$n]);
      if ($other) $sender->sendMessage(mc::_("%1% is now Op",$target->getDisplayName()));
      $target->sendMessage(mc::_("You are now Op"));
      return true;
    }
    // This player can not be re-opped
    if ($other) {
      $sender->sendMessage(mc::_("That is not possible"));
      return true;
    }
    $sender->sendMessage(mc::_("You are not allowed to do this"));
		return true;
	}
	public function onQuit(PlayerQuitEvent $ev) {
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
    $n = strtolower($ev->getPlayer()->getName());
    if (!isset($this->reops[$n])) return;
    unset($this->reops[$n]);
    if ($ev->getPlayer()->isOp()) return;
    $ev->getPlayer()->setOp(true);
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
  }
}
