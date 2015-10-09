<?php
namespace aliuly\common;
//= api-features
//: - FastTransfer work-around wrapper
use pocketmine\Player;
use pocketmine\network\RakLibInterface;
use aliuly\common\mc;
/**
 * Fast Transfer related functions
 */
abstract class FastTransfer {
  /**
   * This will transfer a player and also add the workaround for players
   * lingering connections...
   * @param Player $player
   * @param str $address
   * @param int $port
   * @param str $message
   * @return bool
   */
  static public function transferPlayer(Player $player, $address, $port, $message = null) {
    $ft = $player->getServer()->getPluginManager()->getPlugin("FastTransfer");
    if ($ft === null) return false;
    if ($message === null) $message = mc::_("You are being transferred");
    $res = $ft->transferPlayer($player,$address,$port,$message);
    // find out the RakLib interface, which is the network interface that MCPE players connect with
    foreach($player->getServer()->getNetwork()->getInterfaces() as $interface){
      if ($interface instanceof RakLibInterface) {
        $raklib = $interface;
        break;
      }
    }
    if(!isset($rakLib)) return $res;
    // calculate the identifier for the player used by RakLib
    $identifier = $player->getAddress() . ":" . $player->getPort();

    // this method call is the most important one -
    // it sends some signal to RakLib that makes it think that the client
    // has clicked the "Quit to Title" button (or timed out). Some RakLib
    // internal stuff will then tell PocketMine that the player has quitted.
    $rakLib->closeSession($identifier, "transfer");
  }
}
