<?php
namespace aliuly\common;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use aliuly\common\mc;

/**
 * Sub Command dispatcher
 */
class SubCommandMap {
  public function __construct() {
    $this->executors = [];
    $this->help = [];
    $this->usage = [];
    $this->aliases = [];
    $this->permission = [];
  }
  /**
   * Returns the number of commands configured
   */
  public function getCommandCount() {
    return count($this->executors);
  }
  /**
   * Dispatch commands using sub command table
   */
  public function dispatchSCmd(CommandSender $sender,Command $cmd,array $args,$data=null) {
    if (count($args) == 0) {
      $sender->sendMessage(mc::_("No sub-command specified"));
      return false;
    }
    $scmd = strtolower(array_shift($args));
    if (isset($this->aliases[$scmd])) {
      $scmd = $this->aliases[$scmd];
    }
    if (!isset($this->executors[$scmd])) {
      $sender->sendMessage(mc::_("Unknown sub-command %2% (try /%1% help)",$cmd->getName(),$scmd));
      return false;
    }
    if (isset($this->permission[$scmd])) {
      if (!$sender->hasPermission($this->permission[$scmd])) {
        $sender->sendMessage(mc::_("You are not allowed to do this"));
        return true;
      }
    }
    $callback = $this->executors[$scmd];
    if ($callback($sender,$cmd,$scmd,$data,$args)) return true;
    if (isset($this->executors["help"])) {
      $callback = $this->executors["help"];
      return $callback($sender,$cmd,$scmd,$data,["usage"]);
    }
    return false;
  }
  /**
	 * Register a sub command
	 * @param str $cmd - sub command
	 * @param callable $callable - callable to execute
	 * @param array $opts - additional options
	 */
	public function registerSCmd($cmd,$callable,$opts) {
		$cmd = strtolower($cmd);
		$this->executors[$cmd] = $callable;

    if (isset($opts["help"])) {
      $this->help[$cmd] = $opts["help"];
      ksort($this->help);
    }
    if (isset($opts["usage"])) $this->usage[$cmd] = $opts["usage"];
    if (isset($opts["permission"])) $this->permission[$cmd] = $opts["permission"];
		if (isset($opts["aliases"])) {
			foreach ($opts["aliases"] as $alias) {
				$this->aliases[$alias] = $cmd;
			}
		}
	}

  public function getUsage($scmd) {
    return isset($this->usage[$scmd]) ? $this->usage[$scmd] : null;
  }
  public function getAlias($scmd) {
    return isset($this->aliases[$scmd]) ? $this->aliases[$scmd] : null;
  }
  public function getHelpMsg($scmd) {
    return isset($this->help[$scmd]) ? $this->help[$scmd] : null;
  }
  public function getHelp() {
    return $this->help;
  }
  public function getAliases() {
    return $this->aliases;
  }
}
