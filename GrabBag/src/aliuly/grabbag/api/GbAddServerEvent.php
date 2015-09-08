<?php
namespace aliuly\grabbag\api;

use aliuly\grabbag\Main as GrabBagPlugin;
use aliuly\grabbag\api\GrabBagEvent;
use pocketmine\event\Cancellable;

/**
 * Triggered when a new server is being added to the server list
 */
class GbAddServerEvent extends GrabBagEvent implements Cancellable {
  public static $handlerList = null;
  private $serverId;
  private $serverAttrs;
  /**
   * @param GrabBagPlugin $plugin - plugin owner
   * @param str $id - server id
   * @param array $attrs - server attributes
   */
  public function __construct(GrabBagPlugin $plugin, $id, $attrs) {
     parent::__construct($plugin);
     $this->serverId = $id;
     $this->serverAttrs = $attrs;
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
  /**
   * Gets the server attributes
   * @return array
   */
  public function getAttrs() {
    return $this->serverAttrs;
  }
  /**
   * Sets the server attributes
   * @param array $attrs
   */
  public function setAttrs($attrs) {
    $this->serverAttrs = $attrs;
  }
}
