<?php
class {ClassName} implements \pocketmine\event\Listener {
  protected $plugin;
  protected $callback;
  public function __construct(\pocketmine\plugin\PluginBase $owner,$callback) {
    $this->plugin = $owner;
    $this->callback = $callback;
  }
  public function on{EventClass}({EventClassPath} $ev) {
    $this->callback->dispatchEvent("{EventId}",$ev);
  }
}
