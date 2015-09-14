<?php
namespace aliuly\common;
use aliuly\common\PluginAsyncTask;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use aliuly\common\GetMotd;


/**
 * A shortcut for getting motd data in the background.
 *
 * This is a class that allows you to get motd data from one host in the background.
 *
 * Usage:
 *
 *    new GetMotdAsyncTask($thisPlugin,"getResults",$host,$port)
 *
 * And the method will be called when motd is retrieved.
 */
class GetMotdAsyncTask extends PluginAsyncTask{
	protected $host;
	protected $port;
	/**
	 * @param Plugin	$owner
	 * @param str 		$callable		method from $owner to call
	 * @param str			$host				host to query
	 * @param int			$port				port of host to query
	 * @param array   $args				arguments to pass to callback method
	 */
	public function __construct(Plugin $owner, $callable, $host, $port= 19132, array $args = []){
		parent::__construct($owner,$callable,$args);
		$this->host = $host;
		$this->port = $port;
	}
	public function onRun() {
		$this->setResult([
				"host"=>$this->host,
				"port" => $this->port,
				"results" => null,
		]);
		$res = GetMotd::query($this->host,$this->port);
		$this->setResult([
			"host"=>$this->host,
			"port" => $this->port,
			"results" => $res,
		]);
	}
}
