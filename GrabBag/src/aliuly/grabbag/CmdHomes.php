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
use pocketmine\math\Vector3;


use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;
use aliuly\grabbag\common\PermUtils;
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
    $n = strtolower($sender->getName());
    $l = "l-".strtolower($sender->getLevel()->getName());

		switch($cmd->getName()) {
			case "home":
				return $this->cmdHome($sender,$n,$l);
			case "sethome":
				return $this->cmdSet($sender,$n,$l);
      case "delhome":
        return $this->cmdDel($sender,$n,$l);
		}
		return false;
	}
  public function cmdHome($sender,$n,$l) {
    if (isset($this->homes[$l]) && isset($this->homes[$l][$n])) {
      $v3 = $this->homes[$l][$n];
      $sender->sendMessage(mc::_("There is no place like home..."));
      $sender->teleport(new Vector3($v3[0],$v3[1],$v3[2]));
      return true;
    }
    $sender->sendMessage(mc::_("Can't teleport.  You are homeless!"));
    return true;
  }
  public function cmdSet($sender,$n,$l) {
    $l = "l-".strtolower($sender->getLevel()->getName());
    if (isset($this->homes[$l]) && isset($this->homes[$l][$n])) {
      if (!MPMU::access("gb.cmd.sethome.move")) return true;
    } else {
      if (!MPMU::access("gb.cmd.sethome.new")) return true;
      if (!isset($this->homes[$l])) $this->homes[$l] = [];
    }
    $this->homes[$l][$n] = [ $sender->getX(), $sender->getY(), $sender->getZ() ];
    $this->saveHomes();
    $sender->sendMessage(mc::_("Home is where the heart is!"));
    return true;
  }
  public function cmdDel($sender,$n,$l) {
    $l = "l-".strtolower($sender->getLevel()->getName());
    if (!isset($this->homes[$l]) || !isset($this->homes[$l][$n])) {
      $sender->sendMessage(mc::_("You are already homeless!"));
      return true;
    }
    unset($this->homes[$l][$n]);
    if (count($this->homes[$l]) == 0) unset($this->homes[$l]);
    $this->saveHomes();
    $sender->sendMessage(mc::_("You were born to roam free!"));
    return true;
  }
  protected function saveHomes() {
    $yaml = new Config($this->owner->getDataFolder()."home",Config::YAML,[]);
    $yaml->setAll($this->homes);
    $yaml->save();
  }
}
