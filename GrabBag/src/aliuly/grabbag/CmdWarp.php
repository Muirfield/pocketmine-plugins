<?php
//= cmd:warp,Teleporting
//: Teleport to warp.
//> usage: **warp**  _[player]_ _[warpname]_
//: Teleports to _warpname_.  If no _warpname_ is given, it will list the
//: warps available.
//:
//: Permissions are created with the form: **gb.warp.** _warpname_.
//= cmd:setwarp,Teleporting
//: Sets warp location
//> usage: **setwarp** _<warpname>_ _[x,y,z[:world]]_
//= cmd:delwarp,Teleporting
//: Removes warp
//> usage: **delhome** _<warpname>_

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

//use pocketmine\Player;
//use pocketmine\IPlayer;
//use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\utils\Config;
//use pocketmine\level\Level;

use aliuly\common\BasicCli;
use aliuly\common\mc;
use aliuly\common\MPMU;
use aliuly\common\PermUtils;
use aliuly\common\TPUtils;

class CmdWarp extends BasicCli implements CommandExecutor{
  protected $warps;

	public function __construct($owner) {
		parent::__construct($owner);

    $this->warps = (new Config($this->owner->getDataFolder()."warps.yml",
										 Config::YAML,[
                       "version" => $this->owner->getDescription()->getVersion()
                     ]))->getAll();
    foreach (array_keys($this->warps) as $warp) {
      if ($warp === "version") continue;
      PermUtils::add($this->owner, // Break in two lines so GD2 won't pick it up
                    "gb.warp.".$warp, "warp to ".$warp, "op");
    }

		PermUtils::add($this->owner, "gb.cmd.warp", "teleport to warp location", "true");
    PermUtils::add($this->owner, "gb.cmd.warp.other", "warp others", "false");
    PermUtils::add($this->owner, "gb.cmd.setwarp", "set home command", "op");
    PermUtils::add($this->owner, "gb.cmd.setwarp.new", "set a new warp", "op");
    PermUtils::add($this->owner, "gb.cmd.setwarp.move", "move existing warp", "true");
    PermUtils::add($this->owner, "gb.cmd.delwarp", "Remove warp", "op");

    $this->enableCmd("warp",
							  ["description" => mc::_("Teleport to warp location"),
								"usage" => mc::_("/warp [player] [warp]"),
								"permission" => "gb.cmd.warp"]);
    $this->enableCmd("setwarp",
							  ["description" => mc::_("Set warp location"),
								"usage" => mc::_("/setwarp <warp> [x,y,z[:world]]"),
								"permission" => "gb.cmd.setwarp"]);
		$this->enableCmd("delwarp",
							  ["description" => mc::_("Delete warp location"),
								"usage" => mc::_("/delwarp <warp>"),
								"permission" => "gb.cmd.delhome"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
    if (count($args) != 0) return false;
		switch($cmd->getName()) {
			case "warp":
				return $this->cmdWarp($sender,$args);
			case "setwarp":
				return $this->cmdSet($sender, $args);
      case "delwarp":
        return $this->cmdDel($sender,$args);
		}
		return false;
	}
  public function getWarps() {
    $warps = [];
    foreach ($this->warps as $a=>$b) {
      if ($a == "version") continue;
      $warps[] = $a;
    }
    return $warps;
  }
  public function getWarp($name) {
    $n = strtolower($name);
    if ($n == "version") return null;
    if (!isset($this->warps[$n])) return null;

    list($x,$y,$z,$world) = $this->warps[$n];
    $level = TPUtils::getLevelByName($this->ower->getServer(),$world);
    if ($level === null) return null;
    return new Position($x,$y,$z,$level);
  }
  public function setWarp($name, Position $pos) {
    $n = strtolower($name);
    if ($n == "version") return false;
    $this->warps[$n] = [ $pos->getX(), $pos->getY(), $pos->getZ(), $pos->getLevel()->getName() ];
    return $this->saveWarps();
  }
  public function delWarp($name) {
    $n = strtolower($name);
    if ($n == "version") return false;
    if (!isset($this->warps[$n])) return false;
    unset($this->warps[$n]);
    return $this->saveWarps();
  }
  protected function saveWarps() {
    $yaml = new Config($this->owner->getDataFolder()."warps",Config::YAML,[]);
    $yaml->setAll($this->warps);
    $yaml->save();
    return true;
  }

  private function cmdWarp(CommandSender $sender,$args) {
    switch (count($args)) {
      case 0:
        $warps = $this->getWarps();
        if (count($warps)) {
          $sender->sendMessage(mc::_("Warps(%1%): %2%"), count($warps), implode(", ", $warps));
        } else {
          $sender->sendMessage(mc::_("No warps defined"));
        }
        return true;
      case 1:
        if (!MPMU::inGame($sender)) return true;
        $n = strtolower($args[0]);
        $pos = $this->getWarp($n);
        if ($pos === null) {
          $sender->sendMessage(mc::_("Warp %1% does not exist", $args[0]));
          return true;
        }
        if (!MPMU::access($sender,"gb.warp.".$n)) return true;
        $sender->sendMessage(mc::_("Warping to %1%", $args[0]));
        $sender->teleport($pos);
        return true;
      case 2:
        if (!MPMU::access($sender,"gb.cmd.warp.other")) return true;
        $player = $this->owner->getServer()->getPlayer($args[0]);
        if ($player === null) {
          $sender->sendMessage(mc::_("Player %1% not found", $args[0]));
          return true;
        }
        $n = strtolower($args[1]);
        $pos = $this->getWarp($n);
        if ($pos === null) {
          $player->sendMessage(mc::_("Warp %1% does not exist", $args[1]));
          return true;
        }
        if (!MPMU::access($sender,"gb.warp.".$n)) return true;
        $player->sendMessage(mc::_("You are being warped to %1% by %2%", $args[1], $sender->getName()));
        $player->teleport($pos);
        $sender->sendMessage(mc::_("%1% was warped to %2%", $player->getDisplayName(), $args[1] ));
        return true;
    }
    return false;
  }
  private function cmdSet($sender,$args) {
    switch (count($args)) {
      case 0:
        return false;
      case 1:
        $n = strtolower($args[0]);
        $pos = $sender;
        break;
      default:
        $n = strtolower(array_shift($args));
        $pos = explode(":",implode(" ",$args),2);
        if (count($pos) == 2) {
          $world = $pos[1];
          $level = TPUtils::getLevelByName($this->owner->getServer(),$world);
          if ($level === null) {
            $sender->sendMessage(mc::_("World %1% does not exist", $world));
            return true;
          }
        } else {
          if (MPMU::inGame($sender,false)) {
            $level = $sender->getLevel();
          } else {
            $level = $this->owner->getServer()->getDefaultLevel();
          }
        }
        $pos = explode(",",$pos[0]);
        if (count($pos) != 3) {
          $sender->sendMessage(mc::_("Invalid position"));
          return true;
        }
        $pos = new Position(intval($pos[0]),intval($pos[1]),intval($pos[2]),$level);
    }
    $warp = $this->getWarp($n);
    if ($warp == null) {
      if (!MPMU::access($sender,"gb.cmd.setwarp.new")) return true;
      PermUtils::add($this->owner, // Split in two lines to hide it from gd2
                  "gb.warp.".$n, "warp to ".$n, "op");
    } else {
      if (!MPMU::access($sender,"gb.cmd.setwarp.move")) return true;
    }
    if ($this->setWarp($n,$pos)) {
      $sender->sendMessage(mc::_("Warp %1% created", $n));
    } else {
      $sender->sendMessage(mc::_("Unable to create Warp %1%", $n));
    }
    return true;
  }
  private function cmdDel($sender) {
    if (count($args) != 1) return false;
    $n = strtolower($args[0]);
    $pos = $this->getWarp($n);
    if ($pos === null) {
      $sender->sendMessage(mc::_("Warp %1% does not exist", $args[0]));
      return true;
    }
    if ($this->delWarp($n)) {
      $sender->sendMessage(mc::_("Warp %1% deleted", $n));
    } else {
      $sender->sendMessage(mc::_("Unable to delete Warp %1%", $n));
    }
    return true;
  }
}
