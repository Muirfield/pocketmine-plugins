<?php
//= cmd:alias,Server_Management
//: Create a new command alias
//> usage: **alias** **[-f]** _<alias>_ _<command>_ _[options]_
//:
//: Create an alias to a command.
//: Use the **-f** to override existing commands
//:

namespace aliuly\grabbag;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;
use aliuly\grabbag\common\Cmd;
use aliuly\grabbag\common\PermUtils;

class AliasCmd implements CommandExecutor {
  protected $cmd;
  public function __construct($owner,$alias,$cmd) {
    Cmd::addCommand($owner, $this, $alias, [
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
    PermUtils::add($this->owner, "gb.cmd.alias", "allow creating aliases", "op");
		$this->enableCmd("alias",
							  ["description" => mc::_("Create a command alias"),
								"usage" => mc::_("/alias [-f] [alias [command]]"),
								"permission" => "gb.cmd.alias"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		switch($cmd->getName()) {
			case "alias":
        return $this->cmdAlias($sender,$args);
		}
		return false;
	}
  public function addAlias($alias,$cmdline,$force) {
    if ($this->owner->getServer()->getCommandMap()->getCommand($alias) !== null) {
      if ($force) {
        MPMU::rmCommand($this->owner->getServer(),$alias);
      } else {
        return false;
      }
    }
    $this->aliases[$alias] = new AliasCmd($this->owner, $alias, $cmdline);
    return true;
  }
  private function cmdAlias(CommandSender $sender,array $args) {
    if (count($args) == 0 || count($args) == 1 && is_numeric($args[0])) return $this->lsAliases($sender,$args);
    if (count($args) == 1)  return $this->showAlias($sender, $args[0]);

    if ($args[0] == "-f") {
      $force = true;
      array_shift($args);
      if (count($args) <= 1) return false;
    } else
      $force = false;

    // Create an alias
    $alias = array_shift($args);
    $cmdline = implode(" ",$args);
    if ($this->cmdAlias($alias,$cmdline,$force)) {
      $sender->sendMessage(TextFormat::GREEN.mc::_("Created alias \"%1%\" as \"%2%\"",$alias,$cmdline));
    } else {
      $sender->sendMessage(TextFormat::RED.mc::_("%1% already exists use -f option", $alias));
    }
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
