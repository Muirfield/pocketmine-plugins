<?php
namespace aliuly\toybox;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;

use pocketmine\event\player\PlayerQuitEvent;

class Main extends PluginBase implements Listener {
	protected $state;
	protected $modules;

	public function onEnable(){
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		$defaults = [
			"version" => $this->getDescription()->getVersion(),
			"modules" => [
				"treecapitator" => true,
				"compasstp" => true,
				"trampoline" => true,
				"powertool" => true,
				"cloakclock" => true,
				"floating-torch" => true,
				"magic-carpet" => true,
			],
			"floating-torch" => [
				"item" => "TORCH",
				"block" => "TORCH",
			],
			"compasstp" => [
				"item" => "COMPASS",
			],
			"cloakclock" => [
				"item" => "CLOCK"
			],
			"powertool" => [
				"ItemIDs" => [
					"IRON_PICKAXE", "WOODEN_PICKAXE", "STONE_PICKAXE",
					"DIAMOND_PICKAXE", "GOLD_PICKAXE"
				],
				"need-item" => true,
				"item-wear" => 1,
				"creative" => true,
			],
			"treecapitator" => [
				"ItemIDs" => [
					"IRON_AXE","WOODEN_AXE", "STONE_AXE",
					"DIAMOND_AXE","GOLD_AXE"
				],
				"need-item" => true,
				"break-leaves" => true,
				"item-wear" => 1,
				"broadcast-use" => true,
				"creative" => true,
			],
			"trampoline" => [
				"blocks" => [ "SPONGE" ],
			],
			"magic-carpet" => [
				"block" => "GLASS"
			],
		];
		$cnt = 0;
		$cfg=(new Config($this->getDataFolder()."config.yml",
									  Config::YAML,$defaults))->getAll();
		if ($cfg["modules"]["treecapitator"])
			$this->modules[]= new TreeCapitator($this,$cfg["treecapitator"]);
		if ($cfg["modules"]["powertool"])
			$this->modules[]= new PowerTool($this,$cfg["powertool"]);
		if ($cfg["modules"]["trampoline"])
			$this->modules[] = new Trampoline($this,$cfg["trampoline"]);
		if ($cfg["modules"]["compasstp"])
			$this->modules[] = new CompassTp($this,$cfg["compasstp"]["item"]);
		if ($cfg["modules"]["cloakclock"])
			$this->modules[] = new CloakClock($this,$cfg["cloakclock"]["item"]);
		if ($cfg["modules"]["floating-torch"])
			$this->modules[] = new TorchMgr($this,$cfg["floating-torch"]);
		if ($cfg["modules"]["magic-carpet"])
			$this->modules[] = new MagicCarpet($this,$cfg["magic-carpet"]["block"]);
		if (count($this->modules)) {
			$this->state = [];
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
		}
		$this->getLogger()->info("enabled ".count($this->modules)." modules");
	}

	public function onPlayerQuit(PlayerQuitEvent $ev) {
		$n = strtolower($ev->getPlayer()->getName());
		if (isset($this->state[$n])) unset($this->state[$n]);
	}
	public function getState($label,$player,$default) {
		if ($player instanceof CommandSender) $player = $player->getName();
		$player = strtolower($player);
		if (!isset($this->state[$player])) return $default;
		if (!isset($this->state[$player][$label])) return $default;
		return $this->state[$player][$label];
	}
	public function setState($label,$player,$val) {
		if ($player instanceof CommandSender) $player = $player->getName();
		$player = strtolower($player);
		if (!isset($this->state[$player])) $this->state[$player] = [];
		$this->state[$player][$label] = $val;
	}
	public function unsetState($label,$player) {
		if ($player instanceof CommandSender) $player = $player->getName();
		$player = strtolower($player);
		if (!isset($this->state[$player])) return;
		if (!isset($this->state[$player][$label])) return;
		unset($this->state[$player][$label]);
	}
	public function getItem($txt,$default=0,$msg="") {
		$r = explode(":",$txt);
		if (count($r)) {
			if (!isset($r[1])) $r[1] = 0;
			$item = Item::fromString($r[0].":".$r[1]);
			if (isset($r[2])) $item->setCount(intval($r[2]));
			if ($item->getId() != Item::AIR) {
				return $item;
			}
		}
		if ($default) {
			if ($msg != "")
				$this->getLogger()->info("$msg: Invalid item $txt, using default");
			$item = Item::fromString($default.":0");
			$item->setCount(1);
			return $item;
		}
		if ($msg != "")
			$this->getLogger()->info("$msg: Invalid item $txt, ignoring");
		return null;
	}
}
