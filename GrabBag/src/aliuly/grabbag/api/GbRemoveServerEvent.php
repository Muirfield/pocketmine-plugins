<?php
namespace aliuly\grabbag\api;

use aliuly\grabbag\Main as GrabBagPlugin;
use aliuly\grabbag\api\GrabBagEvent;
use pocketmine\event\Cancellable;

/**
 * Triggered when a new server is being removed from the server list
 */
class GbRemoveServerEvent extends GrabBagEvent implements Cancellable {
  public static $handlerList = null;
  private $serverId;
  /**
   * @param GrabBagPlugin $plugin - plugin owner
   */
   public function __construct(GrabBagPlugin $plugin,$id) {
     parent::__construct($plugin);
     $this->serverId = $id;
   }
  /**
   * Returns the server id
   * @return str
   */
  public function getId() {
     return $this->serverId;
  }
  /**
   * Sets the server id
   * @param str $id
   */
  public function setId($id) {
     $this->serverId = $id;
  }
}
