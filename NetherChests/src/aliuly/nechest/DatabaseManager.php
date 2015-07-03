<?php
namespace aliuly\nechest;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\inventory\Inventory;


interface DatabaseManager {
	public function __construct(PluginBase $owner,$isGlobal);
  public function saveInventory(Player $player,Inventory $inv);
  public function loadInventory(Player $player,Inventory $inv);
	public function close();
}
