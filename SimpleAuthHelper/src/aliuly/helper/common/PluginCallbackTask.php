<?php
namespace aliuly\helper\common;

use pocketmine\scheduler\PluginTask;
use pocketmine\plugin\Plugin;

/**
 * Simple plugin callbacks.
 *
 * Allows the creation of simple callbacks with extra data
 * The last parameter in the callback will be the "currentTicks"
 *
 * Simply put, just do:
 *
 *    new PluginCallbackTask($plugin,[$obj,"method"],[$args])
 *
 * Pass it to the scheduler and off you go...
 */
class PluginCallbackTask extends PluginTask{

	/** @var callable */
	protected $callable;

	/** @var array */
	protected $args;

	/**
	 * @param Plugin   $owner
	 * @param callable $callable
	 * @param array    $args
	 */
	public function __construct(Plugin $owner, callable $callable, array $args = []){
		parent::__construct($owner);
		$this->callable = $callable;
		$this->args = $args;
		$this->args[] = $this;
	}
	/**
	 * @return callable
	 */
	public function getCallable(){
		return $this->callable;
	}

	public function onRun($currentTicks){
		$c = $this->callable;
		$args = $this->args;
		$args[] = $currentTicks;
		$c(...$args);
	}

}
