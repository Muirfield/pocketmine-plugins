<?php
namespace grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\Player;

class UnbreakMgr implements Listener {
	public $owner;
	protected $blocks;
	public function __construct(Plugin $plugin,&$blst) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$blocks = [];
		foreach ($blst as $bl) $this->blocks[$bl] = $bl;
	}
	public function onBlockBreak(BlockBreakEvent $ev){
		$bl = $ev->getBlock();
		if (!isset($this->blocks[$bl->getId()])) return;
		$pl = $ev->getPlayer();
		if ($pl->hasPermission("gb.ubab.override")) return;
		$pl->sendMessage("It can not be broken!");
		$ev->setCancelled();
	}

}
