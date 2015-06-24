<?php
namespace aliuly\liab;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\level\Level;
use pocketmine\utils\Config;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\item\Item;

use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\scheduler\CallbackTask;


class Main extends PluginBase implements Listener {
	public function onEnable(){
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	private function saveInventory(Player $player,Level $level) {
		$n = trim(strtolower($player->getName()));
		if ($n === "") return false;
		$d = substr($n,0,1);
		if (!is_dir($this->getDataFolder().$d)) mkdir($this->getDataFolder().$d);

		$path =$this->getDataFolder().$d."/".$n.".yml";
		$cfg = new Config($path,Config::YAML);
		$yaml = $cfg->getAll();
		$ln = trim(strtolower($level->getName()));

		$yaml[$ln] = [];

		foreach ($player->getInventory()->getContents() as $slot=>&$item) {
			$yaml[$ln][$slot] = implode(":",[ $item->getId(),
														 $item->getDamage(),
														 $item->getCount() ]);
		}
		$cfg->setAll($yaml);
		$cfg->save();
		return true;
	}
	private function loadInventory(Player $player,Level $level) {
		$n = trim(strtolower($player->getName()));
		if ($n === "") return false;
		$d = substr($n,0,1);
		$path =$this->getDataFolder().$d."/".$n.".yml";
		if (!is_file($path)) return false;

		$cfg = new Config($path,Config::YAML);
		$yaml = $cfg->getAll();
		$ln = trim(strtolower($level->getName()));

		if (!isset($yaml[$ln])) return false;

		foreach($yaml[$ln] as $slot=>$t) {
			list($id,$dam,$cnt) = explode(":",$t);
			$item = Item::get($id,$dam,$cnt);
			$player->getInventory()->setItem($slot,$item);
		}
		return true;
	}

	/**
	 * @priority HIGHEST
	 */
	public function levelChg(EntityLevelChangeEvent $ev) {
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if ($ev->isCancelled()) return;
		$player = $ev->getEntity();
		if (!($player instanceof Player)) return;
		if ($player->isCreative()) return;
		if ($player->hasPermission("liab.keep")) return;
		if (!$this->saveInventory($player,$ev->getOrigin())) return;
		$player->getInventory()->clearAll();
		if (!$this->loadInventory($player,$ev->getTarget())) return;
	}
}
