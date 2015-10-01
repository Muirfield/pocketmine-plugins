<?php
//= cmd:tpask,Teleporting
//: Ask a player to teleport to them
//> usage: **tpask** _<player>_
//= cmd:tpahere,Teleporting
//: Ask a player to teleport to you
//> usage: **tpahere** _<player>_
//= cmd:tpaccept,Teleporting
//: Accept a Teleport request
//> usage: **tpaccept** _<player>_
//= cmd:tpdecline,Teleporting
//: Decline a teleport request
//> usage: **tpdecline** _<player>_

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;

use aliuly\common\BasicCli;
use aliuly\common\mc;
use aliuly\common\MPMU;
use aliuly\common\PermUtils;

class CmdTpRequest extends BasicCli implements CommandExecutor, Listener {
  protected $requests;

	public function __construct($owner) {
		parent::__construct($owner);
    $this->requests = [];

    $this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);

		PermUtils::add($this->owner, "gb.cmd.tpask", "Submit a tp request", "op");
    PermUtils::add($this->owner, "gb.cmd.tpahere", "Submit a tp-here request", "true");
    PermUtils::add($this->owner, "gb.cmd.tpaccept", "Accept tpask|tpahere", "true");
    PermUtils::add($this->owner, "gb.cmd.tpaccept.tpask", "Accept tpask", "true");
    PermUtils::add($this->owner, "gb.cmd.tpaccept.tpahere", "Accept tpask", "op");
    PermUtils::add($this->owner, "gb.cmd.tpdecline", "Decline tpask|tpahere", "true");

		$this->enableCmd("tpask",
							  ["description" => mc::_("Ask a player to teleport to them"),
								"usage" => mc::_("/tpask <player>"),
								"permission" => "gb.cmd.tpask",
                "aliases" => ["tpa"]]);
    $this->enableCmd("tpahere",
							  ["description" => mc::_("Ask a player to teleport to you"),
								"usage" => mc::_("/tpahere <player>"),
								"permission" => "gb.cmd.tpahere",
                "aliases" => ["tphere"]]);
    $this->enableCmd("tpaccept",
							  ["description" => mc::_("Accept a teleport request"),
								"usage" => mc::_("/tpaccept <player>"),
								"permission" => "gb.cmd.tpaccept",
                "aliases" => ["tpyes"]]);
    $this->enableCmd("tpdecline",
							  ["description" => mc::_("Decline teleport request"),
								"usage" => mc::_("/tpdecline <player>"),
								"permission" => "gb.cmd.tpdecline",
                "aliases" => ["tpno"]]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
    if (!MPMU::inGame($sender)) return true;
    if (count($args) != 1) return false;
    $b = $this->owner->getServer()->getPlayer($args[0]);
    if ($b === null) {
      $sender->sendMessage(mc::_("%1% not found", $args[0]));
      return true;
    }
		switch($cmd->getName()) {
			case "tpask":
				return $this->cmdTpAsk($sender,$b);
			case "tpahere":
				return $this->cmdTpHere($sender,$b);
      case "tpaccept":
        return $this->cmdAccept($sender,$b);
      case "tpdecline":
        return $this->cmdDecline($sender,$b);
		}
		return false;
	}
  public function onQuit(PlayerQuitEvent $ev) {
    $n = strtolower($ev->getPlayer()->getName());
    foreach (array_keys($this->requests) as $k) {
      $x = explode(":",$k);
      if ($x[0] == $n || $x[1] == $n) unset($this->requests[$k]);
    }
  }
  public function cmdTpAsk(Player $a, Player $b) {
    $a->sendMessage(mc::_("Sent a TPA teleport request to %1%", $b->getDisplayName()));
    $b->sendMessage(mc::_("%1% wants to teleport to your location", $a->getDisplayName()));
    $b->sendMessage(mc::_("Use /tpaccept or /tpdecline"));
    $this->requests[implode(":",[strtolower($a->getName()),strtolower($b->getName())])] = "tpa";
    return true;
  }
  public function cmdTpHere(Player $a, Player $b) {
    $a->sendMessage(mc::_("Sent TPH teleport request to %1%", $b->getDisplayName()));
    $b->sendMessage(mc::_("%1% wants you to teleport to their location"), $a->getDisplayName());
    $b->sendMessage(mc::_("Use /tpaccept or /tpdecline"));
    $this->requests[implode(":",[strtolower($a->getName()),strtolower($b->getName())])] = "tph";
    return true;
  }
  public function cmdDecline(Player $a, Player $b) {
    $k = implode(":",[strtolower($b->getName()),strtolower($a->getName())]);
    if (isset($this->requests[$k])) {
      $a->sendMessage(mc::_("Declining teleport request"));
      $b->sendMessage(mc::_("%1% has declined your teleport request",$a->getDisplayName()));
      unset($this->requests[$k]);
      return true;
    }
    $a->sendMessage(mc::_("No teleport request from %1%",$b->getDisplayName()));
    return true;
  }
  public function cmdAccept(Player $a, Player $b) {
    $k = implode(":",[strtolower($b->getName()),strtolower($a->getName())]);
    if (!isset($this->requests[$k])) {
      $a->sendMessage(mc::_("No teleport request from %1%",$b->getDisplayName()));
      return true;
    }
    $type = $this->requests[$k];
    unset($this->requests[$k]);
    switch ($type) {
      case "tpa":
        if (!MPMU::access($a,"gb.cmd.tpaccept.tpask")) return true;
        $a->sendMessage(mc::_("Accepted teleport request from %1%", $b->getDisplayName()));
        $b->sendMessage(mc::_("%1% accepted your TPASK request", $a->getDisplayName()));
        $b->teleport($a);
        return true;
      case "tph":
        if (!MPMU::access($a,"gb.cmd.tpaccept.tpahere")) return true;
        $a->sendMessage(mc::_("Accepted teleport request to %1%", $b->getDisplayName()));
        $b->sendMessage(mc::_("%1% accepted your TPAHERE request", $a->getDisplayName()));
        $a->teleport($b);
        return true;
    }
    $this->owner->getLogger()->error(mc::_("Invalid teleport request: %1%", $type));
    return false;
  }

}
