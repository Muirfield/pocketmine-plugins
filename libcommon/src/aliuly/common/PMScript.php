<?php
namespace aliuly\common;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\RemoteConsoleCommandSender;
use pocketmine\Server;
use pocketmine\Player;

use aliuly\common\CmdSelector;
use aliuly\common\ExpandVars;

/**
 * Class that implements a PocketMine-MP scripting engine
 */
abstract class PMScript {
  static public $opt_expandVars = true;
  static public $opt_cmdSelectors = true;
  static public $opt_cmdPerms = true;
  static public $consts = [];
  static public $state = [];

  static public function prepare($txt) {
    $php = "";
    foreach (explode("\n",$txt) as $ln) {
      $ln = trim($ln);
      if ($ln == "" || $ln{0} == "#" || $ln{0} == ";") continue;
      $toks = preg_split("/\\s+/",$ln);
      if ($ln{0} == "@") {
        $c = substr($ln,-1);
        $q = ($c == ":" || $c == ";") ? "\n" : ";\n";
        $php .= substr($ln,1).$q;
      } else {
        // Special script commands...s


        $php .= "  ".__CLASS__."::exec(\$server,\$context,'".$ln."');\n";
      }
    }
    return $php;
  }
  static public function run(Server $server,$context,$script, $args = null) {
    // convert to php (caching?)
    $php = self::prepare($script);
    echo "======\n";//##DEBUG
    echo $php;//##DEBUG
    echo "======\n";//##DEBUG
    // Expand vars
    if (self::$opt_expandVars) {
      $vars = self::$consts;
      ExpandVars::sysVars($server,$vars);
      if ($context instanceof Player) ExpandVars::playerVars($context,$vars);
      if ($args !== null) {
        $vars["{script}"] = array_shift($args);
        $vars["{#}"] = count($args);
        for ($i = 0; $i < count($args) ; ++$i) {
          $vars["{".$i."}"] = $args[$i];
        }
      }
      $php = strtr($php,$vars);
      echo $php;//##DEBUG
      echo "======\n";//##DEBUG
    } elseif ($args !== null) {
      $vars = [ "{script}" => array_shift($args) ];
      $vars["{#}"] = count($args);
      for ($i = 0; $i < count($args) ; ++$i) {
        $vars["{".$i."}"] = $args[$i];
      }
      $php = strtr($php,$vars);
    }
    // Run commands
    eval($php);
  }
  static public function startsWith($txt,$tok) {
    $ln = strlen($tok);
    if (strtolower(substr($txt,0,$ln)) != $tok) return null;
    return trim(substr($txt,$ln));
  }

  static public function opexec(Server $srv, $ctx, $cmdline) {
    if (($cm = self::startsWith($cmdline,"+op:")) !== null) {
      if (!$ctx->isOp()) {
        $ctx->setOp(true);
        $srv->distpatchCommand($ctx,$cm);
        $ctx->setOp(false);
        return;
      }
      $srv->distpatchCommand($ctx,$cm);
      return;
    }
    if (($cm = self::startsWith($cmdline,"+console:")) !== null) {
      $srv->distpatchCommand(new ConsoleCommandSender,$cm);
      return;
    }
    if (($cm = self::startsWith($cmdline,"+rcon:")) !== null) {
      $rcon = new RemoteConsoleCommandSender;
      $srv->distpatchCommand(new ConsoleCommandSender,$cm);
      $ctx->sendMessage($rcon->getMessage());
      return;
    }
    if ($ctx == null) $ctx = new ConsoleCommandSender;
    $srv->dispatchCommand($ctx,$cmdline);
  }
  static public function exec(Server $srv,$ctx,$cmdline) {
    if (self::$opt_cmdSelectors) {
      $res = CmdSelector::expandSelectors($srv,$ctx,$cmdline);
      if ($res === false) $res = [ $cmdline ];

      if (self::$opt_cmdPerms) {
        foreach ($res as $cmd) {
          self::opexec($srv,$ctx,$cmd);
        }
      } else {
        if ($ctx == null) $ctx = new ConsoleCommandSender;
        foreach ($res as $cmd) {
          $srv->distpatchCommand($ctx,$cmdline);
        }
      }
    } else {
      if (self::$opt_cmdPerms) {
        self::opexec($srv,$ctx,$cmdline);
      } else {
        if ($ctx == null) $ctx = new ConsoleCommandSender;
        $srv->distpatchCommand($ctx,$cmdline);
      }
    }

  }
}
