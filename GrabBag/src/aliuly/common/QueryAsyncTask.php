<?php
namespace aliuly\common;
use aliuly\common\PluginAsyncTask;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use xPaw\MinecraftQuery;
use xPaw\MinecraftQueryException;


/**
 * A shortcut for doing a Query in the background.
 *
 * This is a class that allows you to query one host in the background.
 *
 * Usage:
 *
 *    new QueryAsyncTask($thisPlugin,"queryResults",$host,$port)
 *
 * And the method will be called when the query finishes.
 */
class QueryAsyncTask extends PluginAsyncTask{
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
			"info" => null,
			"players" => null,
		]);
		$Query = new MinecraftQuery( );
		try {
			//echo __METHOD__.",".__LINE__."\n";//##DEBUG
			//echo "host=$host port=$port\n";//##DEBUG
			$Query->Connect( $this->host, $this->port, 1 );
		} catch (MinecraftQueryException $e) {
			$this->setResult("Query ".$this->host." error: ".$e->getMessage());
			return;
		}
		$this->setResult([
			"host"=>$this->host,
			"port" => $this->port,
			"info" => $Query->GetInfo(),
			"players" => $Query->GetPlayers(),
		]);
	}
}
