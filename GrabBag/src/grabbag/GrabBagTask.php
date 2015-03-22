<?php
namespace grabbag;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;

class GrabBagTask extends PluginTask {
  private $callback;
  public function __construct($plugin,$callback) {
    parent::__construct($plugin);
    $this->callback = serialize($callback);
  }
  public function onRun($ticks) {
    $callback = unserialize($this->callback);
    $method = array_shift($callback);
    call_user_func([$this->getOwner(),$method],...$callback);
  }
}
