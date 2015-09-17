<?php
//= cmd:shield,Teleporting
//: player is teleported to the place of last death
//> usage: **back**

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\level\Position;

use aliuly\common\BasicCli;
use aliuly\common\mc;
use aliuly\common\MPMU;
use aliuly\common\PermUtils;
use aliuly\common\TPUtils;

class CmdTpBack extends BasicCli implements Listener,CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);

		PermUtils::add($this->owner, "gb.cmd.back", "Allow players to return to place of death", "true");
		$this->enableCmd("back",
							  ["description" => mc::_("returns to the place of demise"),
								"usage" => mc::_("/back"),
								"permission" => "gb.cmd.back"]);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (count($args) !== 0) return false;
		if ($cmd->getName() != "back") return false;
		if (!MPMU::inGame($sender)) return true;

		$pos = $this->getState($sender,null);
    if ($pos == null) {
      $sender->sendMessage(mc::_("No recorded death position to return to"));
      return true;
    }
    list($x,$y,$z,$world) = $pos;
		$level = TPUtils::getLevelByName($this->owner->getServer(), $world);
    if ($level === null) {
      $sender->sendMessage(mc::_("Can not return to your death location"));
      $this->unsetState($sender);
      return true;
    }

    $sender->sendMessage(mc::_("Teleporting to the place of your demise"));
    TPUtils::tpNearBy($sender, new Position($x,$y,$z,$level));
    $this->unsetState($sender);

		return true;
	}
  public function onDeath(PlayerDeathEvent $ev) {
    $p = $ev->getPlayer();
    $this->setState($p,[$p->getX(),$p->getY(),$p->getZ(),$p->getLevel()->getName()]);
  }
}
