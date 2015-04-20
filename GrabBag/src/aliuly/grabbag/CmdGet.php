<?php
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\utils\TextFormat;
use pocketmine\item\Item;

class CmdGet extends BaseCommand {
	// Override the MaxStacks counter...
	static $stacks = [ Item::MINECART => 1, Item::BOOK => 1, Item::COMPASS => 1,
							 Item::CLOCK => 1 ];

	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("get",
							  ["description" => "Shortcut to /give me",
								"usage" => "/get <item[:damage]> [amount]",
								"permission" => "gb.cmd.get"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (!isset($args[0])) return false;
		if ($cmd->getName() != "get") return false;
		if ($sender->isCreative()) {
			$sender->sendMessage("You are in creative mode");
			return true;
		}
		$item = Item::fromString($args[0]);
		if ($item->getId() == 0) {
			$sender->sendMessage(TextFormat::RED."There is no item called ".
										$args[0]);
			return true;
		}

		if (isset($args[1])) {
			$item->setCount((int)$args[1]);
		} else {
			if (isset(self::$stacks[$item->getId()])) {
				$item->setCount(self::$stacks[$item->getId()]);
			} else {
				$item->setCount($item->getMaxStackSize());
			}
		}
		$sender->getInventory()->addItem(clone $item);
		$this->owner->getServer()->broadcastMessage($sender->getName()." got ".
																  $item->getCount()." of ".
																  $this->itemName($item).
																  " (" . $item->getId() . ":" .
																  $item->getDamage() . ")");
		return true;
	}
}
