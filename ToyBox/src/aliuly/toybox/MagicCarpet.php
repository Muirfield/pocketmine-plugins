<?php
namespace aliuly\toybox;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;

use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\network\Network;
use aliuly\toybox\common\mc;

class MagicCarpet extends BaseCommand implements Listener {
	protected $block;

	public function __construct($owner,$bl) {
		parent::__construct($owner);
		$this->enableCmd("magiccarpet",
							  ["description" => mc::_("Fly with a magic carpet"),
								"usage" => mc::_("/magiccarpet"),
								"aliases" => ["mc"],
								"permission" => "toybox.magiccarpet"]);
		$this->owner->getServer()->getPluginManager()->registerEvents($this,$this->owner);
		$this->block = $this->owner->getItem($bl,Item::GLASS,"magic-carpet")->getId();
		//echo __METHOD__.",".__LINE__." $this->block\n";//##DEBUG
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "magiccarpet") return false;
		if (!$this->inGame($sender)) return true;
		if (isset($args[0])) {
			$size = (int)array_shift($args);
		} else {
			$size = 5;
		}
		if (count($args) != 0) return false;
		switch($size){
			case 3:
			case 5:
			case 7:
				break;
			default:
				$size =5;
		}
		$state = $this->getState($sender,null);
		if ($state) {
			$this->deSpawn($sender,$state[1]);
			$this->unsetState($sender);
			$sender->sendMessage(mc::_("The magic carpet disappears"));
		} else {
			$state = $this->setState($sender,[ $size, []]);
			$this->carpet($sender);
			$sender->sendMessage(mc::_("A magic carpet of size %1%\nappears below your feet.",$size));
		}
		return true;
	}
	private function deSpawn(Player $pl,array &$blocks) {
		$l = $pl->getLevel();
		if (version_compare($this->owner->getServer()->getApiVersion(),"1.12.0") >= 0) {
			$sndblks = [];
			foreach($blocks as $i=>$block){
				list($x,$y,$z)=array_map("intval", explode(":", $i));
				$sndblks[] = Block::get($block->getId(),$block->getDamage(),
												new Position($x,$y,$z,$l));
			}
			$l->sendBlocks($l->getChunkPlayers($pl->getX()>>4,$pl->getZ()>>4),
								$sndblks, UpdateBlockPacket::FLAG_ALL_PRIORITY);
		} else {
			foreach($blocks as $i=>$block){
				list($x,$y,$z)=array_map("intval", explode(":", $i));
				$pk = new UpdateBlockPacket();
				$pk->x = $x;
				$pk->y = $y;
				$pk->z = $z;
				$pk->block = $block->getId();
				$pk->meta = $block->getDamage();
				Server::broadcastPacket($l->getUsingChunk($pk->x >> 4,$pk->z >> 4),
												$pk);
			}
		}
	}
	private function carpet(Player $pl) {
		$state = $this->getState($pl,null);
		if (!$state) return;
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		list($size,$blocks) = $state;
		$startX = intval($pl->getX()) - ($size-1)/2;
		$endX = $startX + $size;
		$startZ = intval($pl->getZ()) - ($size-1)/2;
		$endZ = $startZ + $size;
		$y = intval($pl->getY())-1;
		if ($pl->getPitch() > 75) --$y;
		$newBlocks = [];
		$l = $pl->getLevel();
		for($x=$startX; $x<$endX;++$x) {
			for($z=$startZ;$z<$endZ;++$z) {
				$i = "$x:$y:$z";
				if(isset($blocks[$i])){
					$newBlocks[$i] = $blocks[$i];
					unset($blocks[$i]);
				} else {
					$newBlocks[$i]=$l->getBlock(new Vector3($x,$y,$z));
					if ($newBlocks[$i]->getId() === Block::AIR) {
						$blocks[$i]=Block::get($this->block,0,
													  new Position($x,$y,$z,$l));
					}
				}
			}
		}
		$this->deSpawn($pl,$blocks);
		$this->setState($pl,[$size,$newBlocks]);
	}

	/////////////////////////////////////////////////////////////////////////
	//
	// Event handlers
	//
	/////////////////////////////////////////////////////////////////////////
	public function onMove(PlayerMoveEvent $e) {
		$this->carpet($e->getPlayer());
	}
	public function onKick(PlayerKickEvent	$e) {
		if ($e->isCancelled()) return;
		if (preg_match('/flying/i',$e->getReason())) {
			$state = $this->getState($e->getPlayer(),null);
			if ($state) {
				$e->setCancelled();
			}
		}
	}
	/**
	 * @priority LOWEST
	 */
	public function onPlayerQuit(PlayerQuitEvent $ev) {
		$state = $this->getState($ev->getPlayer(),null);
		if (!$state) return;
		$this->deSpawn($ev->getPlayer(),$state[1]);
	}
	/**
	 * @priority LOWEST
	 */
	public function onPlayerTeleport(EntityTeleportEvent $e) {
		$pl = $e->getEntity();
		if (!($pl instanceof Player)) return;
		$state = $this->getState($pl,null);
		if ($state) {
			$this->deSpawn($pl,$state[1]);
			$this->unsetState($pl);
			$pl->sendMessage(mc::_("Magic carpet lost due to teleport!"));
		}

	}


}
