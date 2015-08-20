<?php
/**
 ** OVERVIEW:Server Management
 **
 ** COMMANDS
 **
 ** * alias : Create a new command alias
 **   usage: **alias** _[--rm|--list|--rmcmd]_ _<alias>_ _<command>_ _[options]_
 **
 **   Create an alias to a command.
 **   The following sub commands are possible:
 **
 **   * --rm : Remove an alias
 **   * --rmcmd : Remove an existing command
 **   * --list : List defined aliases
 **
 **/

namespace aliuly\grabbag;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\common\MPMU;
use aliuly\common\Cmd;
use pocketmine\utils\TextFormat;

class AliasCmd implements CommandExecutor {
  protected $cmd;
  public function __construct($owner,$alias,$cmd) {
    MPMU::addCommand($owner, $this, $alias, [
      "description" => mc::_("Alias for %1%", $cmd),
      "usage" => mc::_("/%1% [options]", $alias),
    ]);
    $this->cmd = $cmd;
  }
  public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
    $cmdline = $this->cmd;
    if (count($args)) $cmdline .= " ".implode(" ",$args);
    Cmd::exec($sender,[$cmdline],false);
		return true;
	}
  public function getCmd() {
    return $this->cmd;
  }
}

class CmdAlias extends BasicCli implements CommandExecutor {
  protected $aliases;
	public function __construct($owner) {
		parent::__construct($owner);
		$this->aliases = [];
		$this->enableCmd("alias",
							  ["description" => mc::_("Create a command alias"),
								"usage" => mc::_("/alias [--rm|--list|--rmcmd] <alias> [command]"),
								"permission" => "gb.cmd.alias"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		switch($cmd->getName()) {
			case "alias":
        return $this->cmdAlias($sender,$args);
		}
		return false;
	}
  private function cmdAlias(CommandSender $sender,array $args) {
    if (count($args) == 0) $args = ["--ls" ];
    switch(strtolower($args[0])) {
      case "--ls":
        array_shift($args);
        return $this->lsAliases($sender, $args);
      case "--rm":
        array_shift($args);
        return $this->rmAlias($sender, $args);
      case "--rmcmd":
        array_shift($args);
        if (count($args) != 1) return false;
        if (MPMU::rmCommand($this->owner->getServer(),$args[0])) {
          $sender->sendMessage(TextFormat::GREEN."Command %1% has been removed", $args[0]);
        } else {
          $sender->sendMessage(TextFormat::RED."Unable to remove command %1%", $args[0]);
        }
        return true;
    }
    if (count($args) == 1)  return $this->showAlias($sender, $args[0]);
    // Create an alias
    $alias = array_shift($args);
    $cmdline = implode(" ",$args);
    if (isset($this->aliases[$alias])) {
      $sender->sendMessage(TextFormat::RED.mc::_("%1% already exists as an alias", $alias));
      return true;
    }
    if ($this->owner->getServer()->getCommandMap()->getCommand($alias) !== null) {
      $sender->sendMessage(TextFormat::RED.mc::_("%1% already exists as a command", $alias));
      return true;
    }
    $this->aliases[$alias] = new AliasCmd($this->owner, $alias, $cmdline);
    $sender->sendMessage(TextFormat::GREEN.mc::_("Created alias \"%1%\" as \"%2%\"",$alias,$cmdline));
    return true;
  }
  private function rmAlias(CommandSender $sender, array $args) {
    if (count($args) != 1) return false;
    $alias = array_shift($args);
    if (!isset($this->aliases[$alias])) {
      $sender->sendMessage(TextFormat::RED.mc::_("%1% is NOT an alias", $alias));
      return true;
    }
    if (!MPMU::rmCommand($this->owner->getServer(),$alias)) {
      $sender->sendMessage(TextFormat::RED.mc::_("Unable to un-map alias %1%", $alias));
      return true;
    }
    unset($this->aliases[$alias]);
    $sender->sendMessage(TextFormat::GREEN.mc::_("Removed alias %1%",$alias);
    return true;
  }
  private function showAlias(CommandSender $sender, $alias) {
    if (!isset($this->aliases[$alias])) {
      $sender->sendMessage(TextFormat::RED.mc::_("%1% is NOT an alias", $alias));
      return true;
    }
    $sender->sendMessage(TextFormat::GREEN.mc::_("ALIAS:%1%=%2%",$alias, $this->aliases[$alias]->getCmd()));
    return true;
  }
  private function lsAliases(CommandSender $sender, array $args) {
    $pageNumber = $this->getPageNumber($args);
    $txt = [];
    $txt[] = mc::_("Aliases: %1%", count($this->aliases));
    foreach ($this->aliases as $alias=>&$exec) {
      $txt[] = mc::_(TextFormat::GREEN.$alias.": ".TextFormat::WHITE.$exec->getCmd());
    }
    return $this->paginateText($sender,$pageNumber,$txt);
  }
}
