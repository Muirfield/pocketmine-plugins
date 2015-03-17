<?php
namespace ManyWorlds;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;

class MwTask extends PluginTask {
  private $attr;

  public function __construct($plugin,$player,$location) {
    parent::__construct($plugin);
    $this->attr = implode("\0",[$player->getName(),
				$location->getX(),$location->getY(),$location->getZ()]);
  }
  public function onRun($ticks) {
    $this->getOwner()->delayedTP($this->attr);
  }
}
