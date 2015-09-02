<?php
namespace aliuly\common;

use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

use aliuly\common\CmdSelector;
use aliuly\common\ExpandVars;
use aliuly\common\Cmd;
/**
 * Class that implements a PocketMine-MP scripting engine
 */
class PMScript {
  protected $owner;
  protected $selector;
  protected $perms;
  protected $vars;
  protected $globs;
  /**
   * @param Plugin $owner - plugin that owns this interpreter
   * @param bool|ExpandVars $vars - allow for standard variable expansion
   * @param bool $perms - allow the use of Cmd::opexec
   * @param int $selector - if 0, command selctors are not used, otherwise max commands
   */
  public function __construct(Plugin $owner, $vars = true, $perms = true, $selector = 100) {
    $this->owner = $owner;
    $this->selector = $selector;
    $this->globs = [];
    if ($perms) {
      $this->perms = [ Cmd::class, "opexec" ];
    } else {
      $this->perms = [ $this->owner->getServer(), "dispatchCommand" ];
    }
    if ($vars) {
      if ($vars instanceof ExpandVars) {
        $this->vars = $vars;
      } else {
        $this->vars = new ExpandVars($owner);
      }
    } else {
      $this->vars = null;
    }
  }
  /**
   * Define additional constants on the fly...
   * @param str $name
   * @param mixed $value
   */
  public function define($str,$value) {
    if ($this->vars !== null) $this->vars->define($str,$value);
  }
  /** Return plugin owner */
  public function getOwner() {
    return $this->owner;
  }
  /** Return server */
  public function getServer() {
    return $this->owner->getServer();
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
  ////////////////////////////////////////////////////////////////////////
  // Main implementation
  ////////////////////////////////////////////////////////////////////////

  /**
   * Run a script file
   * @param CommandSender $ctx - Command context
   * @param callable $php - Loaded PMScript
   * @param array $args - Command args
   * @param array $opts - Some environemnt variables
   */
  public function runScriptFile(CommandSender $ctx, $path, array &$args, array &$opts) {
    $php = $this->loadScriptFile($path);
    if ($php === false) return false;
    $this->executeScript($ctx,$php,$args,$opts);
    return true;
  }

  /**
   * load a script from file (May implement a cache in the future...)
   * @param str $path - path to file to load
   * @param bool $cache - enable/disable caching
   */
  public function loadScriptFile($path,$cache = false) {
    return $this->loadScriptCode(file_get_contents($path));
  }
  /**
   * Execute a PMScript
   *
   * @param CommandSender $ctx - Command context
   * @param callable $php - Loaded PMScript
   * @param array $args - Command args
   * @param array $opts - Some environemnt variables
   */
  public function runScriptCode(CommandSender $ctx,$pmscode,array &$args,array &$opts) {
    $php = $this->loadScriptCode($pmscode);
    if ($php === false) return false;
    $this->executeScript($ctx,$php,$args,$opts);
    return true;
  }
  /**
   * Execute preloaded PHP code
   * @param CommandSender $ctx - Command context
   * @param callable $php - Loaded PMScript
   * @param array $args - Command args
   */
  public function executeScript(CommandSender $ctx, $php, array &$args, array &$opts) {
    if ($this->vars === null) {
      $vars = [];
    } else {
      $vars = $this->vars->getConsts();
      $this->vars->sysVars($vars);
      if ($ctx instanceof Player) $this->vars->playerVars($ctx,$vars);
    }
    $vars["{#}"] = count($args);
    $i = 0;
    foreach ($args as $j) {
      $vars["{".($i++)."}"] = $j;
    }
    foreach ($opts as $i=>&$j) {
      if (is_string($j)) $vars["{".$i."}"] = $j;
    }
    try {
      $php($this,$ctx,$vars,$args,$opts);
    } catch (\Exception $e) {
      $ctx->sendMessage(TextFormat::RED.mc::_("Exception: %1%",$e->getMessage()));
    }
  }
  /**
   * Prepare PMScript and convert into a PHP callable
   * @param str $pmscript - text script
   */
   public function loadScriptCode($pmscript) {
     $php = "";
     // Prefix code ...
     $php .= " return function (\$interp,\$context,&\$vars,&\$args,&\$env) {";
     $php .= "  foreach (\$vars as \$i=>\$j) {\n";
     $php .= "    if (preg_match(\"/^\\{([_a-zA-Z][_a-zA-Z0-9]*)\\}\\\$/\",\$i,\$mv)) {\n";
     $php .= "       eval(\"\\\$\" . \$mv[1] . \" = \\\$j;\\n\");\n";
     $php .= "    }\n";
     $php .= "  }\n";
     foreach (explode("\n",$pmscript) as $ln) {
       $ln = trim($ln);
       if ($ln == "" || $ln{0} == "#" || $ln{0} == ";") continue;
       if ($ln{0} == "@") {
         $c = substr($ln,-1);
         $q = ($c == ":" || $c == ";") ? "\n" : ";\n";
         $php .= substr($ln,1).$q;
       } else {
         $php .= "  \$interp->exec(\$context,'".$ln."',\$vars);\n";
       }
     }
     $php .= "};";
     echo $php;
     return eval($php);
  }
  /**
   * Execute a command
   * @param CommandSender $ctx - Command context
   * @param str $cmdline - Command to execute
   * @param array $vars - Variables table for variable expansion
   */
  public function exec(CommandSender $ctx, $cmdline, $vars) {
    $cmdline = strtr($cmdline,$vars);
    if ($this->selector) {
      $cmds = CmdSelector::expandSelectors($this->getServer(),$ctx, $cmdline, $this->selector);
      if ($cmds == false) {
        $cmds = [ $cmdline ];
      }
    } else {
      $cmds = [ $cmdline ];
    }
    $cmdex = $this->perms;
    foreach ($cmds as $ln) {
      $cmdex($ctx,$ln);
    }
  }

}
