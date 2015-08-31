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
class PMScript {
  protected $options;
  protected $vars;
  protected $server;
  protected $globs;
  /**
   *
   * Options:
   * - vars : ExpandVars object or null
   * - cmdSelectors : use command selectors
   * - perms : Allow +op:,+rcon:,+console: prefixes
   *
   * @param array $opts - options array
   * @param array $consts - initial constants
   */
  public function __construct(Server $server, $opts) {
    $this->server = $server;
    $this->options = [];
    $this->globs = [];
    foreach ("cmdSelectors"=>true, "perms"=>true] as $k=>$v) {
      $this->options[$k] = isset($opts[$k]) ? $opts[$k] : $v;
    }
    $this->vars = isset($options["vars"]) ? $options["vars"] : null;
  }
  ////////////////////////////////////////////////////////////////////////
  // Static functions API
  ////////////////////////////////////////////////////////////////////////

  /**
   * @param Server $srv - pocketmine\Server instance
   * @param CommandSender $ctx - running context
   * @param str $cmdline - command line to execute
   */
  static public function opexec(Server $srv, CommandSender $ctx, $cmdline) {
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
      if (trim($rcon->getMessage()) != "") $ctx->sendMessage($rcon->getMessage());
      return;
    }
    $srv->dispatchCommand($ctx,$cmdline);
  }
  /**
   * Run a command with a given context
   * @param Server $srv - pocketmine\Server instance
   * @param CommandSender $ctx - running context
   * @param str $cmdline - command line to execute
   * @param bool $selectors - allow command selectors
   * @param bool $perms - allow permission prefixes
   */
  static public function exec(Server $srv,CommandSender $ctx,$cmdline, $selectors = true, $perms = true) {
    if ($selectors) {
      $res = CmdSelector::expandSelectors($srv,$ctx,$cmdline);
      if ($res === false) $res = [ $cmdline ];

      if ($perms) {
        foreach ($res as $cmd) {
          self::opexec($srv,$ctx,$cmd);
        }
      } else {
        foreach ($res as $cmd) {
          $srv->distpatchCommand($ctx,$cmdline);
        }
      }
    } else {
      if ($erms) {
        self::opexec($srv,$ctx,$cmdline);
      } else {
        if ($ctx == null) $ctx = new ConsoleCommandSender;
        $srv->distpatchCommand($ctx,$cmdline);
      }
    }
  }
  ////////////////////////////////////////////////////////////////////////
  // Static support functions
  ////////////////////////////////////////////////////////////////////////
  /** Convert text into PHP code
   * @param $txt - input text
   * @return str
   */
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
        $php .= "  ".__CLASS__."::exec(\$server,\$context,'".$ln."',\$selectors,\$perms);\n";
      }
    }
    return $php;
  }
  /**
   * Check prefixes
   * @param str $txt - input text
   * @param str $tok - keyword to test
   * @return
   */
  static public function startsWith($txt,$tok) {
    $ln = strlen($tok);
    if (strtolower(substr($txt,0,$ln)) != $tok) return null;
    return trim(substr($txt,$ln));
  }

  ////////////////////////////////////////////////////////////////////////
  // OOP API
  ////////////////////////////////////////////////////////////////////////
  /**
   * Define additional constants on the fly...
   * @param str $name
   * @param str $value
   */
  public function define($str,$value) {
    $this->vars->define($str,$value);
  }
  /**
   * @param CommandSender $context
   * @param str $script
   * @param array|null $args
   * @param array|null $exts
   */
  public function run($context,$script, $args = null,$exts = null) {
    if (!($context instanceof CommandSender)) $context = new ConsoleCommandSender;
    // convert to php (caching?)
    $php = self::prepare($script);
    echo "======\n";//##DEBUG
    echo $php;//##DEBUG
    echo "======\n";//##DEBUG

    // Basic variable definitions...
    $server = $this->server;
    $selectors = $this->options["cmdSelectors"];
    $perms = $this->options["perms"];
    $interp = $this;

    // Additional variable definitions...
    if ($exts as $j=>$k) {
      eval("\$$j = \$k;");
    }

    // Expand vars
    if ($this->vars !== null) {
      $vars = $this->vars->getConsts();
      $this->vars->sysVars($vars);
      if ($context instanceof Player) $this->vars->playerVars($context,$vars);
      if ($args !== null) {
        $vars["{#}"] = count($args);
        for ($i = 0; $i < count($args) ; ++$i) {
          $vars["{".$i."}"] = $args[$i];
        }
      }
      $php = strtr($php,$vars);
      echo $php;//##DEBUG
      echo "======\n";//##DEBUG
    } elseif ($args !== null) {
      $vars = [ "{#}" => count($args) ];
      for ($i = 0; $i < count($args) ; ++$i) {
        $vars["{".$i."}"] = $args[$i];
      }
      $php = strtr($php,$vars);
    }
    // Run commands
    eval($php);
  }
  /**
   * @param str $label - global variable to get
   * @param mixed $default - default value to return is no global found
   * @return mixed
   */
  public function getGlob($label,$default) {
    if (!isset($this->globs[$label])) return $default;
    return $this->globs[$label];
  }
  /**
   * Set global variable
   *
   * @param str $label - state variable to set
   * @param mixed $val - value to set
   * @return mixed
   */
  public function setGlob($label,$val) {
    $this->globs[$label] = $val;
    return $val;
  }
  /**
   * Clears a global variable
   *
   * @param str $label - state variable to clear
   */
  public function unsetGlob($label) {
    if (!isset($this->globs[$label])) return;
    unset($this->globs[$label]);
  }

}
