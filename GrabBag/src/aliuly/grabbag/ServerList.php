<?php
//= cmd:servers,Server_Management
//: Manage peer server connections
//> usage: **servers** **<add|rm|ls>** _[options]_
//:
//: This is used to manage the peer server definitions used by the
//: **RCON** and **QUERY** modules, among others.
//:
//: Sub-commands:
//> - **servers add** _<id>_ _<host>_ _[port]_ _[options]_ _[# comments]_
//:   - adds a new connection with **id**
//> - **servers rm** _<id>_
//:   - Removes peer **id**.
//> - **servers ls**
//:   - List configured peers.
//> - **servers info** _<id>_
//:   - Show server details

//= cfg:serverlist
//: This section configures peer servers.  This can be used with
//: *rcon* and *query* commands.

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use aliuly\common\BasicCli;
use aliuly\common\mc;
use aliuly\common\MPMU;
use aliuly\common\PermUtils;

use aliuly\grabbag\api\GbAddServerEvent;
use aliuly\grabbag\api\GbRemoveServerEvent;
use aliuly\grabbag\api\GbRmQueryEvent;
use aliuly\grabbag\api\GbUpdateQueryEvent;

class ServerList extends BasicCli implements CommandExecutor {
  const CfgTag = "serverlist";
  protected $servers;
  protected $query;
  //= cmd:servers,Server_Management
  //: Avalable options (when adding servers):
  public static $opts = [
    //: * rcon-port=port : Alternative port for RCON
    "rcon-port" => "port",
    //: * rcon-pw=secret : RCON password
    "rcon-pw" => "str",
    //: * rcon-host="str" : Alternative host for RCON
    "rcon-host" => "host",
    //: * no-motd-task : This server will not be polled by the MOTD Daemon
    "no-motd-task" => true,
    //: * no-query-task : This server will not be polled by the Query Daemon
    "no-query-task" => true,
    //: * query-use-ipv4 : Resolve host name when doing queries
    "query-use-ipv4" => true,
    //: * query-host=host : Alternative host to be used in queries
    "query-host" => "host",
    //: * ping-host=host : Alternative host to be used in MOTD pings
    "ping-host" => "host",
    //: * ping-use-ipv4 : Ping host by IP address
    "ping-use-ipv4" => true,
    //: * ft-host=host : Alternative host to be used in fast transfer
    "ft-host" => "host",
  ];

  public function __construct($owner,$cfg) {
    parent::__construct($owner);
    $this->servers = $cfg;
    $this->query = [];

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
    if (!$this->delQueryData($id)) return false;
    if (!isset($this->servers[$id])) return true;
    unset($this->servers[$id]);
    $this->owner->cfgSave(self::CfgTag,$this->servers);
    return true;
  }
  public function getServer($id) {
    if (isset($this->servers[$id])) return $this->servers[$id];
    return null;
  }
  public function getServerAttr($id,$attr,$default = null) {
    //echo __METHOD__.",".__LINE__."\n";//##DEBUG
    if (!isset($this->servers[$id])) return $default;
    $ret = $default;
    if (isset($this->servers[$id][$attr])) {
      $ret = $this->servers[$id][$attr];
    } elseif (isset(self::$opts[$attr]) && is_string(self::$opts[$attr]) && isset($this->servers[$id][self::$opts[$attr]])) {
      $ret = $this->servers[$id][self::$opts[$attr]];
    }
    switch ($attr) {
      case "query-host":
        if  (isset($this->servers[$id]["query-use-ipv4"]) && $this->servers[$id]["query-use-ipv4"]) {
          $ret = gethostbyname($ret);
        }
        break;
      case "ping-host":
        if  (isset($this->servers[$id]["ping-use-ipv4"]) && $this->servers[$id]["ping-use-ipv4"]) {
          $ret = gethostbyname($ret);
        }
        break;
    }
    return $ret;
  }
  public function addQueryData($id,$tag,$attrs) {
    if (!isset($this->servers[$id])) return false;
    $this->owner->getServer()->getPluginManager()->callEvent(
	     $ev = new GbUpdateQueryEvent($this->owner, $id, $tag, $attrs)
    );
    if ($ev->isCancelled()) return false;

    $id = $ev->getId();
    $tag = $ev->getTag();

    if (!isset($this->query[$id])) $this->query[$id] = [];
    if (is_array($attrs)) {
      $this->query[$id][$tag] = $ev->getAttrs();
      $this->query[$id][$tag]["age"] = microtime(true);
    } else {
      $this->query[$id][$tag] = [ "value" => $ev->getAttrs(), "age" => microtime(true) ];
    }
    return true;
  }
  public function getQueryData($id,$tag = null,$default = null) {
    if (!isset($this->query[$id])) return $default;
    if ($tag === null) return $this->query[$id];
    if (!isset($this->query[$id][$tag])) return $default;
    return $this->query[$id][$tag];
  }
  public function delQueryData($id,$tag = null) {
    if (!isset($this->query[$id])) return;
    if ($tag !== null && !isset($this->query[$id][$tag])) return;
    $this->owner->getServer()->getPluginManager()->callEvent(
	     $ev = new GbRmQueryEvent($this->owner, $id, $tag)
    );
    if ($ev->isCancelled()) return false;
    if ($tag !== null) {
      unset($this->query[$id][$tag]);
    } else {
      unset($this->query[$id]);
    }
    return true;
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
          case "info":
            if (!MPMU::access($sender,"gb.cmd.servers.read")) return true;
            array_shift($args);
            return $this->cmdShow($sender,$args);
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
      $c->sendMessage(mc::_("Usage: add <id> <host> [port] [options] [# comments]"));
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
      $attr = array_shift($args);
      if ($attr{0} == "#") {
        $dat["#"] = implode(" ",$args);
        break;
      }
      $kvp = explode("=",$attr,2);
      if (count($kvp) == 1) {
        if (isset(self::$opts[$attr])) {
          if (is_bool(self::$opts[$attr])) {
            $dat[$attr] = self::$opts[$attr];
          } else {
            $c->sendMessage(mc::_("No value specified %1%", $attr));
            return true;
          }
        } else {
          $c->sendMessage(mc::_("Unknown attribute %1%", $attr));
          return true;
        }
      }
      list($attr,$val) = $kvp;
      if (isset(self::$opts[$attr])) {
        if (is_string(self::$opts[$attr])) {
          $dat[$attr] = $val;
        } else {
          $c->sendMessage(mc::_("Attribute %1% is a boolean switch", $attr));
          return true;
        }
      } else {
        $c->sendMessage(mc::_("Unknown attribute %1%", $attr));
        return true;
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
  private function cmdShow(CommandSender $c,$args) {
    $pageNumber = $this->getPageNumber($args);
    if (count($args) != 1) {
      $c->sendMessage(mc::_("Usage: show <id>"));
      return false;
    }
    $id = array_shift($args);
    if (!isset($this->servers[$id])) {
      $c->sendMessage(mc::_("%1% does not exist",$id));
      return false;
    }
    $txt = [ mc::_("Details for %1%", $id) ];
    foreach ($this->servers[$id] as $k=>$v) {
      if ($k == "rcon-pw" && !MPMU::access($c,"gb.cmd.servers.read.viewrcon",false)) continue;
      if (isset(self::$opts[$k]) && self::$opts[$k] == "host" && !MPMU::access($c,"gb.cmd.servers.read.viewip",false)) continue;
      if (is_bool($v)) $v = $v ? mc::_("YES") : mc::_("NO");
      $txt[] = $k.": ".$v;
    }
    return $this->paginateText($c,$pageNumber,$txt);
  }
  private function cmdList(CommandSender $c,$args) {
    $pageNumber = $this->getPageNumber($args);
    $txt = [mc::_("Server connections")];
    foreach ($this->servers as $id => $dat) {
      $ln = $id;
      $q = ": ";
      if (MPMU::access($c,"gb.cmd.servers.read.viewip",false)) {
        $ln .= $q.$dat["host"].":".$dat["port"];
        $q = ", ";
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
