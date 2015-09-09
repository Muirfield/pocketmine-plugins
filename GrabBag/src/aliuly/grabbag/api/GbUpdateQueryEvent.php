<?php
namespace aliuly\grabbag\api;

use aliuly\grabbag\Main as GrabBagPlugin;
use aliuly\grabbag\api\GrabBagEvent;
use pocketmine\event\Cancellable;

/**
 * Triggered when a new server is being added to the server list
 */

 //GbUpdateQueryEvent($this->owner, $id, $tag, $attrs)
class GbAddServerEvent extends GrabBagEvent implements Cancellable {
  public static $handlerList = null;
  private $serverId;
  private $tag;
  private $attrs;
  /**
   * @param GrabBagPlugin $plugin - plugin owner
   * @param str $id - server id
   * @param str $tag - data tag
   * @param array $attrs - server attributes
   */
  public function __construct(GrabBagPlugin $plugin, $id, $tag, $attrs) {
     parent::__construct($plugin);
     $this->serverId = $id;
     $this->tag = $tag;
     $this->attrs = $attrs;
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
    return $this->attrs;
  }
  /**
   * Sets the server attributes
   * @param array $attrs
   */
  public function setAttrs($attrs) {
    $this->attrs = $attrs;
  }
  /**
   * Gets the server attributes
   * @return array
   */
  public function getTag() {
    return $this->tag;
  }
  /**
   * Sets the server attributes
   * @param array $attrs
   */
  public function setAttrs($tag) {
    $this->tag = $tag;
  }
}
