<?php
namespace aliuly\common;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;


/**
 * A shortcut for AsyncTask
 *
 */
abstract class PluginAsyncTask extends AsyncTask{
	/** @var string */
	public $owner;

	/** @var callable */
	protected $callable;

	/** @var array */
	protected $args;

	/**
	 * @param Plugin	$owner
	 * @param str 		$callable		method from $owner to call
	 * @param array   $args				arguments to pass to callback method
	 */
	public function __construct(Plugin $owner, $callable, array $args = []){
		$this->owner = $owner->getName();
		$this->callable = $callable;
		$this->args = $args;
	}
	public function onCompletion(Server $server) {
		$plugin = $server->getPluginManager()->getPlugin($this->owner);
		if ($plugin == null) {
			$server->getLogger()->error("Internal ERROR: ".__METHOD__.",".__LINE__);
			return;
		}
		if (!$plugin->isEnabled()) return;
		$callback = [$plugin, $this->callable];
		$callback($this->getResult(),...$this->args);
	}
}
