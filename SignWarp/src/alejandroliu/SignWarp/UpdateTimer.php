<?php
namespace alejandroliu\SignWarp;

use pocketmine\scheduler\PluginTask;

class UpdateTimer extends PluginTask {
  public function onRun($currentTick) {
    $this->getOwner()->updateSigns();
  }
}
