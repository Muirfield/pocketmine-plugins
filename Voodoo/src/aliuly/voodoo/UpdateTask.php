<?php
namespace aliuly\voodoo;

use pocketmine\scheduler\PluginTask;

class UpdateTask extends PluginTask {
  public function onRun($currentTick) {
    $this->getOwner()->updateMobs();
  }
}
