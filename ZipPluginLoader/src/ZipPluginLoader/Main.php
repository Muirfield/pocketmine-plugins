<?php
namespace ZipPluginLoader;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginLoadOrder;

class Main extends PluginBase {
	const LOADER = "ZipPluginLoader\\ZipPluginLoader";
	public function onEnable(){
		if (!in_array("myzip",stream_get_wrappers())) {
			if (!stream_wrapper_register("myzip",__NAMESPACE__."\\MyZipStream")) {
				$this->getLogger()->error("Unable to register Zip wrapper");
				throw new \RuntimeException("Runtime checks failed");
				return;
			}
		}
		$this->getServer()->getPluginManager()->registerInterface(self::LOADER);
		$this->getServer()->getPluginManager()->loadPlugins($this->getServer()->getPluginPath(), ["ZipPluginLoader\\ZipPluginLoader"]);
		$this->getServer()->enablePlugins(PluginLoadOrder::STARTUP);
	}
	public function onDisable() {
		foreach ($this->getServer()->getPluginManager()->getPlugins() as $p) {
			if ($p->isDisabled()) continue;
			if (get_class($p->getPluginLoader()) == self::LOADER) {
				$this->getServer()->getPluginManager()->disablePlugin($p);
			}
		}
		if (in_array("myzip",stream_get_wrappers())) {
			stream_wrapper_unregister("myzip");
		}
	}
}
