<?php
namespace aliuly\scorched;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandExecutor;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

use pocketmine\item\Item;
use pocketmine\entity\Entity;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;

class Main extends PluginBase implements CommandExecutor {
  // Access and other permission related checks
  private function access(CommandSender $sender, $permission) {
    if($sender->hasPermission($permission)) return true;
    $sender->sendMessage("You do not have permission to do that.");
    return false;
  }
  private function inGame(CommandSender $sender,$msg = true) {
    if ($sender instanceof Player) return true;
    if ($msg) $sender->sendMessage("You can only use this command in-game");
    return false;
  }

  // Paginate output
  private function getPageNumber(array &$args) {
    $pageNumber = 1;
    if (count($args) && is_numeric($args[count($args)-1])) {
      $pageNumber = (int)array_pop($args);
      if($pageNumber <= 0) $pageNumber = 1;
    }
    return $pageNumber;
  }
  private function paginateText(CommandSender $sender,$pageNumber,array $txt) {
    $hdr = array_shift($txt);
    if($sender instanceof ConsoleCommandSender){
      $sender->sendMessage( TextFormat::GREEN.$hdr.TextFormat::RESET);
      foreach ($txt as $ln) $sender->sendMessage($ln);
      return true;
    }
    $pageHeight = 5;
    $hdr = TextFormat::GREEN.$hdr. TextFormat::RESET;
    if (($pageNumber-1) * $pageHeight >= count($txt)) {
      $sender->sendMessage($hdr);
      $sender->sendMessage("Only ".intval(count($txt)/$pageHeight+1)." pages available");
      return true;
    }
    $hdr .= TextFormat::RED." ($pageNumber of ".intval(count($txt)/$pageHeight+1).")".TextFormat::RESET;
    $sender->sendMessage($hdr);
    for ($ln = ($pageNumber-1)*$pageHeight;$ln < count($txt) && $pageHeight--;++$ln) {
      $sender->sendMessage($txt[$ln]);
    }
    return true;
  }
  private function paginateTable(CommandSender $sender,$pageNumber,array $tab) {
    $cols = [];
    for($i=0;$i < count($tab[0]);$i++) $cols[$i] = strlen($tab[0][$i]);
    foreach ($tab as $row) {
      for($i=0;$i < count($row);$i++) {
	if (($l=strlen($row[$i])) > $cols[$i]) $cols[$i] = $l;
      }
    }
    $txt = [];
    foreach ($tab as $row) {
      $txt[] = sprintf("%-$cols[0]s %-$cols[1]s %-$cols[2]s %-$cols[3]s",
		       $row[0],$row[1],$row[2],$row[3]);
    }
    return $this->paginateText($sender,$pageNumber,$txt);
  }
  // Standard call-backs
  public function onDisable() {
    $this->getLogger()->info("- Scorched Unloaded!");
  }
  public function onEnable(){
    $this->getLogger()->info("* Scorched Enabled!");
  }
  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    switch($cmd->getName()) {
    case "fire":
      if (!$this->access($sender,"scorched.cmd.fire")) return true;
      return $this->cmdMain($sender,$args);
    }
    return false;
  }
  // Command implementations

  private function cmdMain(CommandSender $c,$args) {
    if (!$this->inGame($c)) return false;
    if (!$c->isCreative()) {
      // Not in creative, we need to check inventories...
      $found = false;
      foreach ($c->getInventory()->getContents() as $slot=>$item) {
	if ($item->getID() != Item::TNT || $item->getCount() == 0) continue;

	$found = true;
	$count = $item->getCount();
	if ($count == 1) {
	  // The last one...
	  $c->getInventory()->clear($slot);
	} else {
	  $item->setCount($count-1);
	  $c->getInventory()->setItem($slot,$item);
	}
	break;
      }
      if (!$found) {
	$c->sendMessage(TextFormat::RED."You ran out of rockets".TextFormat::RESET);
	return true;
      }
    }
    $pos = $c->getPosition();
    $pos->y += $c->getEyeHeight();
    $speed = 2.5;
    if (isset($args[0]) && is_numeric($args[0])) {
      $speed = (float)$args[0];
      if ($speed > 4.0) $speed = 4.0;
      if ($speed < 0.5) $speed = 0.5;
    }
    $fuse = 80;
    if (isset($args[1]) && is_numeric($args[1])) {
      $fuse = (int)$args[1];
      if ($fuse > 120) $fuse = 120;
      if ($fuse < 10) $fuse = 10;
    }
    if (count($args) > 2) return false;

    $dir = $c->getDirectionVector();
    $dir->x = $dir->x * $speed;
    $dir->y = $dir->y * $speed;
    $dir->z = $dir->z * $speed;

    $nbt =
      new Compound("", ["Pos" => new Enum("Pos", [new Double("", $pos->x),
						  new Double("", $pos->y),
						  new Double("", $pos->z)]),
			"Motion" => new Enum("Motion",[new Double("",$dir->x),
						       new Double("",$dir->y),
						       new Double("",$dir->z)]),
			"Rotation" => new Enum("Rotation", [new Float("", 0),
							    new Float("", 0)]),
			"Fuse" => new Byte("Fuse", $fuse)]);

    $entity = Entity::createEntity("PrimedTNT",
				   $pos->getLevel()->getChunk($pos->x >> 4, $pos->z >> 4),
				   $nbt);
				       $entity->namedtag->setName("EssNuke");
    $entity->spawnToAll();

    return true;
  }
}
