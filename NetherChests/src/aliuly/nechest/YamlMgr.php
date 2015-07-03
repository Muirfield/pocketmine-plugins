<?php
namespace aliuly\nechest;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\inventory\Inventory;
use pocketmine\utils\Config;
use pocketmine\item\Item;

class YamlMgr implements DatabaseManager {
  protected $isGlobal;
  protected $owner;
	public function __construct(PluginBase $owner,$cf) {
    $this->owner = $owner;
    $this->isGlobal = $cf["settings"]["global"];
  }
  protected function getDataFolder() {
    return $this->owner->getDataFolder();
  }
  public function saveInventory(Player $player,Inventory $inv) {
    $n = trim(strtolower($player->getName()));
    if ($n === "") return false;
    $d = substr($n,0,1);
    if (!is_dir($this->getDataFolder().$d)) mkdir($this->getDataFolder().$d);

    $path =$this->getDataFolder().$d."/".$n.".yml";
    $cfg = new Config($path,Config::YAML);
    $yaml = $cfg->getAll();
    if ($this->isGlobal)
      $ln = "*";
    else
      $ln = trim(strtolower($player->getLevel()->getName()));

    $yaml[$ln] = [];

    foreach ($inv->getContents() as $slot=>&$item) {
      $yaml[$ln][$slot] = implode(":",[ $item->getId(),
                             $item->getDamage(),
                             $item->getCount() ]);
    }
    $inv->clearAll();
    $cfg->setAll($yaml);
    $cfg->save();
    return true;

  }
  public function loadInventory(Player $player,Inventory $inv) {
		$n = trim(strtolower($player->getName()));
		if ($n === "") return false;
		$d = substr($n,0,1);
		$path =$this->getDataFolder().$d."/".$n.".yml";
		if (!is_file($path)) return false;

		$cfg = new Config($path,Config::YAML);
		$yaml = $cfg->getAll();
    if ($this->isGlobal)
      $ln = "*";
    else
      $ln = trim(strtolower($player->getLevel()->getName()));

		if (!isset($yaml[$ln])) return false;

		$inv->clearAll();
		foreach($yaml[$ln] as $slot=>$t) {
			list($id,$dam,$cnt) = explode(":",$t);
			$item = Item::get($id,$dam,$cnt);
			$inv->setItem($slot,$item);
		}
		return true;
	}
	public function close() {}
}
