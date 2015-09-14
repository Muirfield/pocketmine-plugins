<?php
namespace aliuly\common;
use aliuly\common\PluginAsyncTask;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use aliuly\common\Rcon;
//
// We can not localize this as it is running on a different thread...
//

/**
 * Rcon implementation as an async task...
 */
class RconTask extends PluginAsyncTask {
	protected $server;
	protected $cmd;
	protected $sock;

	/**
	 * @param Plugin $owner
	 * @param str $callable - method from $owner to call
	 * @param array	$remote - Array containing $host,$port,$auth data
	 * @param str $cmd - remote command to execute
   * @param array $args	- extra arguments to pass to callback method
	 */
	public function __construct(Plugin $owner, $callable, array $remote, $cmd, array $args = []){
		parent::__construct($owner,$callable,$args);
		$this->server = $remote;
		$this->cmd = $cmd;
		$this->sock = false;
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
}
