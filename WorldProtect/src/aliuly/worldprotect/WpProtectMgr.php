<?php
namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\block\Block;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\tile\Sign;
use pocketmine\scheduler\CallbackTask;
use pocketmine\math\Vector3;

//use pocketmine\event\player\PlayerInteractEvent; // Not used for now...
//use pocketmine\event\entity\EntityExplodeEvent; // Also not used...

class WpProtectMgr implements Listener {
	public $owner;
	protected $signs;

	static protected function blockAddr(Block $block) {
		$l = $block->getLevel()->getName();
		$x = $block->getX();
		$y = $block->getY();
		$z = $block->getZ();
		return implode(":",[$l,$x,$y,$z]);
	}
	public function __construct(Plugin $plugin) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$signs = [];
	}

	public function onBlockBreak(BlockBreakEvent $ev){
		if ($ev->isCancelled()) return;
		$pl = $ev->getPlayer();
		if ($this->owner->checkBlockPlaceBreak($pl->getName(),
															$pl->getLevel()->getName())) return;
		$this->owner->msg($pl,"You are not allowed to do that here");
		$ev->setCancelled();
	}
	public function onSignChanged(SignChangeEvent $ev){
		if ($ev->isCancelled()) return;
		$h = self::blockAddr($ev->getBlock());
		if (!isset($this->signs[$h])) return;
		list($id,$meta,$cnt,$time) = $this->signs[$h];

		// API check...
		$api = explode(".",$this->owner->getServer()->getApiVersion());
		if (intval($api[1]) < 12) {
			if ($cnt == 0) {
				$this->signs[$h][2] = 1;
				return;
			}
		}
		unset($this->signs[$h]);
		$block =$ev->getBlock();
		$l = $block->getLevel();
		$x = $block->getX();
		$y = $block->getY();
		$z = $block->getZ();
		$l->setBlockIdAt($x,$y,$z,$id);
		$l->setBlockDataAt($x,$y,$z,$meta);

		$tt = new CallbackTask([$this,"ripSign"],[$l->getName(),$x,$y,$z]);
		$this->owner->getServer()->getScheduler()->scheduleDelayedTask($tt,10);
	}

	public function ripSign($level,$x,$y,$z) {
		$l = $this->owner->getServer()->getLevelByName($level);
		if (!$l) return;
		$sign = $l->getTile(new Vector3($x,$y,$z));
		if ($sign instanceof Sign) $sign->close();
	}


	public function onBlockPlace(BlockPlaceEvent $ev){
		if ($ev->isCancelled()) return;
		$pl = $ev->getPlayer();
		if ($this->owner->checkBlockPlaceBreak($pl->getName(),
															$pl->getLevel()->getName())) return;
		$this->owner->msg($pl,"You are not allowed to do that here");
		$id = $ev->getBlock()->getId();

		if ($id == Block::SIGN_POST || $id == Block::WALL_SIGN) {
			// Oh no.. placing a SignPost!
			$h = self::blockAddr($ev->getBlock());
			$this->signs[$h] = [ $ev->getBlockReplaced()->getId(),
										$ev->getBlockReplaced()->getDamage(),
										0, time() ];
			return;
		}
		$ev->setCancelled();
	}
}
