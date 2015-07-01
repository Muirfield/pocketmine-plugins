<?php
namespace aliuly\common;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use aliuly\common\Rcon;
//
// We can not localize this as it is running on a different thread...
//

/**
 * Rcon implementation as an async task...
 */
class RconTask extends AsyncTask {
	protected $server;
	protected $cmd;
  protected $plugin;
	protected $callback;
	protected $data;

	/**
	 * @param array $remote - Array containing $host,$port,$auth data
	 * @param str $cmd - remote command to execute
	 * @param Plugin $plugin - plugin to receive results
	 * @param str $callback - method from plugin to callback with results
	 * @param mixed $data - data passed to callback function
	 */
	public function __construct($remote,$cmd,$plugin,$callback,$data) {
		$this->server = $remote;
		$this->cmd = $cmd;
		$this->sock = false;
		$this->plugin = $plugin->getName();
		$this->callback = $callback;
		$this->data = $data;
	}
	private function close($msg = "") {
		$this->setResult($msg);
		if ($this->sock) {
			fclose($this->sock);
			$this->sock = false;
		}
	}
	public function onRun() {
		list($host,$port,$auth) = $this->server;
		$ret = Rcon::connect($host,$port,$auth);
		if (!is_array($ret)) {
			$this->close($ret);
			return ;
		}
		list($this->sock,$id) = $ret;

		$ret = Rcon::cmd($this->cmd,$this->sock,$id);
		$this->close($ret);
	}
	public function onCompletion(Server $server) {
		$plugin = $server->getPluginManager()->getPlugin($this->plugin);
		if ($plugin === null) return;
		$cb = [$plugin,$this->callback];
		$cb($this->getResult(),$this->data);
	}
}
