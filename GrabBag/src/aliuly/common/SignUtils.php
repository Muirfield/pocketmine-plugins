<?php
namespace aliuly\common;
use pocketmine\block\Block;
use pocketmine\tile\Sign;
use pocketmine\plugin\Plugin;

use aliuly\common\PluginCallbackTask;

/**
 * Routines for manipulating signs
 */
abstract class SignUtils {
  /**
   * Destroy a sign
   * @param Sign $tile - sign tile
   */
  static public function breakSign(Sign $tile) {
		$l = $tile->getLevel();
		$l->setBlockIdAt($tile->getX(),$tile->getY(),$tile->getZ(),Block::AIR);
		$l->setBlockDataAt($tile->getX(),$tile->getY(),$tile->getZ(),0);
		$tile->close();
	}
  static public function breakSignLater(Plugin $plugin,Sign $tile, $ticks=5) {
    $plugin->getServer()->getScheduler()->scheduleDelayedTask(
      new PluginCallbackTask($this,[self::class,"breakSign"],[$tile]),$ticks
    );
  }
}
