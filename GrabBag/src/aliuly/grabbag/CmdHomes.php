<?php
//= cmd:home,Teleporting
//: Teleport to home
//> usage: **home**
//= cmd:sethome,Teleporting
//: Sets your home location
//> usage: **sethome**
//= cmd:home,Teleporting
//: Removes your home
//> usage: **delhome**

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\IPlayer;
use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\level\Level;

use aliuly\common\BasicCli;
use aliuly\common\mc;
use aliuly\common\MPMU;
use aliuly\common\PermUtils;
use pocketmine\utils\Config;

class CmdHomes extends BasicCli implements CommandExecutor{
  protected $homes;

	public function __construct($owner) {
		parent::__construct($owner);

    $this->homes = (new Config($this->owner->getDataFolder()."homes.yml",
										 Config::YAML,[
                       "version" => $this->owner->getDescription()->getVersion()
                     ]))->getAll();

		PermUtils::add($this->owner, "gb.cmd.home", "teleport to home location", "true");
    PermUtils::add($this->owner, "gb.cmd.sethome", "set home command", "true");
    PermUtils::add($this->owner, "gb.cmd.sethome.new", "set a new home", "op");
    PermUtils::add($this->owner, "gb.cmd.sethome.move", "move existing home", "true");
    PermUtils::add($this->owner, "gb.cmd.delhome", "Remove home", "true");

    $this->enableCmd("home",
							  ["description" => mc::_("Teleport to home location"),
								"usage" => mc::_("/home"),
								"permission" => "gb.cmd.home"]);
    $this->enableCmd("sethome",
							  ["description" => mc::_("Set your home location"),
								"usage" => mc::_("/sethome"),
								"permission" => "gb.cmd.sethome"]);
		$this->enableCmd("delhome",
							  ["description" => mc::_("Delete home location"),
								"usage" => mc::_("/delhome"),
								"permission" => "gb.cmd.delhome"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
    if (!MPMU::inGame($sender)) return true;
    if (count($args) != 0) return false;
		switch($cmd->getName()) {
			case "home":
				return $this->cmdHome($sender,$sender->getLevel());
			case "sethome":
				return $this->cmdSet($sender);
      case "delhome":
        return $this->cmdDel($sender);
		}
		return false;
	}
  public function getHome(IPlayer $player, Level $level) {
    $n = strtolower($player->getName());
    $l = "l-".strtolower($level->getName());
    if (!isset($this->homes[$l]) || !isset($this->homes[$l][$n])) return null;
    $v3 = $this->homes[$l][$n];
    return new Vector3($v3[0],$v3[1],$v3[2]);
  }
  public function setHome(IPlayer $player, Position $pos) {
    $n = strtolower($player->getName());
    $l = "l-".strtolower($pos->getLevel()->getName());
    $this->homes[$l][$n] = [ $pos->getX(), $pos->getY(), $pos->getZ() ];
    $this->saveHomes();
  }
  public function delHome(IPlayer $player, Level $level) {
    $n = strtolower($player->getName());
    $l = "l-".strtolower($level->getName());
    if (!isset($this->homes[$l]) || !isset($this->homes[$l][$n])) return;
    unset($this->homes[$l][$n]);
    if (count($this->homes[$l]) == 0) unset($this->homes[$l]);
    $this->saveHomes();
  }
  protected function saveHomes() {
    $yaml = new Config($this->owner->getDataFolder()."home",Config::YAML,[]);
    $yaml->setAll($this->homes);
    $yaml->save();
  }
  private function cmdHome(Player $player) {
    $home = $this->getHome($player,$player->getLevel());
    if ($home === null) {
      $player->sendMessage(mc::_("Can't teleport.  You are homeless!"));
      return true;
    }
    $player->sendMessage(mc::_("There is no place like home..."));
    $player->teleport($home);
  }
  private function cmdSet($sender) {
    $home = $this->getHome($sender,$sender->getLevel());
    if ($home === null) {
      if (!MPMU::access("gb.cmd.sethome.new")) return true;
    } else {
      if (!MPMU::access("gb.cmd.sethome.move")) return true;
    }
    $this->setHome($sender,$sender);
    $sender->sendMessage(mc::_("Home is where the heart is!"));
    return true;
  }
  private function cmdDel($sender) {
    $home = $this->getHome($sender,$sender->getLevel());
    if ($home === null) {
      $sender->sendMessage(mc::_("You are already homeless!"));
      return true;
    }
    $this->delHome($sender,$sender->getLevel());
    $sender->sendMessage(mc::_("You were born to roam free!"));
    return true;
  }
}
