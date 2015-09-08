<?php
//= cmd:servers,Server_Management
//: Manage peer server connections
//> usage: **servers** **<add|rm|ls>** _[options]_
//:
//: This is used to manage the peer server definitions used by the
//: **RCON** and **QUERY** modules.
//:
//: Options:
//> - **servers add** _<id> <host> [port] [--rcon-port=port] [--rconpw=secret] [# comments]_
//:   - adds a new connection with **id**
//> - **servers rm** _<id>_
//:   - Removes peer **id**.
//> - **servers ls**
//:   - List configured peers.

//= cfg:ServerList
//: This section configures peer servers.  This can be used with
//: *rcon* and *query* commands.

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;
use aliuly\grabbag\common\PermUtils;

use aliuly\grabbag\api\GbAddServerEvent;
use aliuly\grabbag\api\GbRemoveServerEvent;


class ServerList extends BasicCli implements CommandExecutor {
  const CfgTag = "serverlist";
  protected $servers;

  public function __construct($owner,$cfg) {
    parent::__construct($owner);
    $this->servers = $cfg;

    PermUtils::add($this->owner, "gb.cmd.servers", "servers command", "op");
    PermUtils::add($this->owner, "gb.cmd.servers.read", "view server configuration", "op");
    PermUtils::add($this->owner, "gb.cmd.servers.read.viewip", "view server IP address", "op");
    PermUtils::add($this->owner, "gb.cmd.servers.read.viewrcon", "view rcon secrets", "op");
    PermUtils::add($this->owner, "gb.cmd.servers.write", "change server configuration", "op");

    $this->enableCmd("servers",
                ["description" => mc::_("Manage server lists"),
                "usage" => mc::_("/servers <add|rm|ls> [opts]"),
                "aliases" => ["srv"],
                "permission" => "gb.cmd.servers"]);
  }

  public function getIds() {
    return array_keys($this->servers);
  }
  public function addServer($key,$val) {
    $this->owner->getServer()->getPluginManager()->callEvent(
	     $ev = new GbAddServerEvent($this->owner, $key, $val)
    );
    if ($ev->isCancelled()) return false;
    $this->servers[$ev->getId()] = $ev->getAttrs();
    $this->owner->cfgSave(self::CfgTag,$this->servers);
    return true;
  }
  public function rmServer($id) {
    if (!isset($this->servers[$id])) return true;
    $this->owner->getServer()->getPluginManager()->callEvent(
	     $ev = new GbAddServerEvent($this->owner, $key, $val)
     );
    if ($ev->isCancelled()) return false;
    $id = $ev->getId();
    if (!isset($this->servers[$id])) return true;
    unset($this->servers[$id]);
    $this->owner->cfgSave(self::CfgTag,$this->servers);
    return true;
  }
  public function getServer($id) {
    if (isset($this->servers[$id])) return $this->servers[$id];
    return null;
  }

  public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
    if (count($args) == 0) return false;
    switch($cmd->getName()) {
      case "servers":
        switch (strtolower($args[0])) {
          case "add":
            if (!MPMU::access($sender,"gb.cmd.servers.write")) return true;
            array_shift($args);
            return $this->cmdAdd($sender,$args);
          case "rm":
            if (!MPMU::access($sender,"gb.cmd.servers.write")) return true;
            array_shift($args);
            return $this->cmdRm($sender,$args);
          case "ls":
            if (!MPMU::access($sender,"gb.cmd.servers.read")) return true;
            array_shift($args);
            return $this->cmdList($sender,$args);
        }
    }
    return false;
  }
  private function cmdAdd(CommandSender $c,$args) {
    if (count($args) < 2) {
      $c->sendMessage(mc::_("Usage: add <id> <host> [port] [--rcon-port=port] [--rconpw=secret] [# comments]"));
      return false;
    }
    $id = array_shift($args);
    if (substr($id,0,1) == "-") {
      $c->sendMessage(mc::_("Server id can not start with a dash (-)"));
      return false;
    }
    if (strpos($id,",") !== false) {
      $c->sendMessage(mc::_("Server id can not contain commas (,)"));
      return false;
    }
    if (isset($this->servers[$id])) {
      $c->sendMessage(mc::_("%1% is an id that is already in use.",$id));
      $c->sendMessage(mc::_("Use rm first"));
      return false;
    }
    $dat = [
      "host" => array_shift($args),
      "port" => 19132,
    ];
    while (count($args)) {
      if (is_numeric($args[0])) {
        $dat["port"] = array_shift($args);
      } elseif (($i = MPMU::startsWith($args[0],"--rcon-port=")) !== null) {
        $dat["rcon-port"] = $i;
        array_shift($args);
      } elseif (($i = MPMU::startsWith($args[0],"--rconpw=")) !== null) {
        $dat["rcon-pw"] = $i;
        array_shift($args);
      } elseif (substr($args[0],0,1) == "#") {
        $dat["#"] = substr(implode(" ",$args),1);
        break;
      } else {
        $c->sendMessage(mc::_("Unknown option %1%",$args[0]));
        return false;
      }
    }
    if ($this->addServer($id,$dat))
      $c->sendMessage(mc::_("Server id %1% configured",$id));
    else
      $c->sendMessage(mc::_("Failed to configure %1%",$id));
    return true;
  }
  private function cmdRm(CommandSender $c,$args) {
    if (count($args) != 1) {
      $c->sendMessage(mc::_("Usage: rm <id>"));
      return false;
    }
    $id = array_shift($args);
    if (!isset($this->servers[$id])) {
      $c->sendMessage(mc::_("%1% does not exist",$id));
      return false;
    }
    if ($this->rmServer($id))
      $c->sendMessage(mc::_("Server id %1% deleted",$id));
    else
      $c->sendMessage(mc::_("Unable to delete id %1%",$id));
    return true;
  }
  private function cmdList(CommandSender $c,$args) {
    $pageNumber = $this->getPageNumber($args);
    $txt = ["Server connections"];
    foreach ($this->servers as $id => $dat) {
      $ln = $id;
      $q = ": ";
      if (MPMU::access($c,"gb.cmd.servers.read.viewip",false)) {
        $ln .= $q.$dat["host"].":".$dat["port"];
        $q = ", ";
      }
      if (MPMU::access($c,"gb.cmd.servers.read.viewrcon",false)) {
        if (isset($dat["rcon-port"])) {
          $ln .= $q.mc::_("rcon-port:%1%",$dat["rcon-port"]);
          $q = ", ";
        }
        if (isset($dat["rcon-pw"])) {
          $ln .= $q.mc::_("rcon-pw:%1%",$dat["rcon-pw"]);
          $q = ", ";
        }
      }
      if (isset($dat["#"])) {
        $ln .= $q.mc::_(" #:%1%",$dat["#"]);
        $q = ", ";
      }
      $txt[] = $ln;
    }
    return $this->paginateText($c,$pageNumber,$txt);
  }
}
