<?php
namespace aliuly\grabbag\api;

use aliuly\grabbag\Main as GrabBagPlugin;
use aliuly\grabbag\api\GrabBagEvent;
use pocketmine\event\Cancellable;

/**
 * Triggered when server query data being removed
 */
class GbRmQueryEvent extends GrabBagEvent implements Cancellable {
  public static $handlerList = null;
  private $serverId;
  private $tag;
  /**
   * @param GrabBagPlugin $plugin - plugin owner
   */
   public function __construct(GrabBagPlugin $plugin,$id, $tag) {
     parent::__construct($plugin);
     $this->serverId = $id;
     $this->tag = $tag;
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
  public function getTag() {
    return $this->tag;
  }
  public function setTag($tag) {
    $this->tag = $tag;
  }
}
