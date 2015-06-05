<?php
namespace aliuly\helper;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\command\CommandExecutor;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\item\Item;
use pocketmine\utils\Config;
use pocketmine\Player;

use aliuly\helper\common\PluginCallbackTask;
use aliuly\helper\common\mc;

class Main extends PluginBase implements Listener,CommandExecutor {
	const RE_REGISTER = '/^\s*\/register\s+/';
	const RE_LOGIN = '/^\s*\/login\s+/';

	protected $auth;
	protected $pwds;
	protected $chpwd;
	protected $cfg;

	public function onEnable(){
		mc::plugin_init($this,$this->getFile());
		$this->auth = $this->getServer()->getPluginManager()->getPlugin("SimpleAuth");
		if (!$this->auth) {
			$this->getLogger()->error(TextFormat::RED.mc::_("Unable to find SimpleAuth"));
			throw new \RuntimeException("Missinge Dependancy");
			return;
		}

		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());


		$defaults = [
			"version" => $this->getDescription()->getVersion(),
			"max-attempts" => 5,
			"login-timeout" => 60,
			"leet-mode" => true,
			"chat-protect" => true,
		];
		$this->cfg=(new Config($this->getDataFolder()."config.yml",
										  Config::YAML,$defaults))->getAll();

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->pwds = [];
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Event handlers
	//
	//////////////////////////////////////////////////////////////////////
	public function onPlayerQuit(PlayerQuitEvent $ev) {
		$n = $ev->getPlayer()->getName();
		if (isset($this->pwds[$n])) unset($this->pwds[$n]);
		if (isset($this->chpwd[$n])) unset($this->chpwd[$n]);
	}
	public function onPlayerJoin(PlayerJoinEvent $ev) {
		if ($this->cfg["login-timeout"] == 0) return;
		$n = $ev->getPlayer()->getName();
		$this->getServer()->getScheduler()->scheduleDelayedTask(new PluginCallbackTask($this,[$this,"checkTimeout"],[$n]),$this->cfg["login-timeout"]*20);
	}
	/**
	 * @priority LOW
	 */
	public function onPlayerCmd(PlayerCommandPreprocessEvent $ev) {
		if ($ev->isCancelled()) return;
		//echo __METHOD__.",".__LINE__."\n"; //##DEBUG;
		$pl = $ev->getPlayer();
		$n = $pl->getName();
		if ($this->auth->isPlayerAuthenticated($pl) && !isset($this->chpwd[$n])) {
			if ($this->cfg["chat-protect"]) {
				if ($this->authenticate($pl,$ev->getMessage())) {
					$pl->sendMessage(TextFormat::RED.mc::_("chat protected"));
					$ev->setMessage(mc::_("**CENSORED**"));
					$ev->setCancelled();
				}
			}
			return;
		}

		if (!$this->auth->isPlayerRegistered($pl) || isset($this->chpwd[$n])) {
			if (!isset($this->pwds[$n])) {
				if ($this->cfg["leet-mode"] && preg_match(self::RE_REGISTER,$ev->getMessage())) {
					$pl->sendMessage(TextFormat::YELLOW.mc::_("snob register"));
					$ev->setMessage(preg_replace(self::RE_REGISTER,'',$ev->getMessage()));
				}
				if (!$this->checkPwd($pl,$ev->getMessage())) {
					$ev->setCancelled();
					$ev->setMessage("~");
					return;
				}
				$this->pwds[$n] = $ev->getMessage();
				$pl->sendMessage(TextFormat::AQUA.mc::_("re-enter pwd"));
				$ev->setCancelled();
				$ev->setMessage("~");
				return;
			}
			if ($this->pwds[$n] != $ev->getMessage()) {
				unset($this->pwds[$n]);
				$ev->setCancelled();
				$ev->setMessage("~");
				$pl->sendMessage(TextFormat::RED.mc::_("passwords dont match"));
				return;
			}
			if (isset($this->chpwd[$n])) {
				// User is changing password...
				unset($this->chpwd[$n]);
				$ev->setMessage("~");
				$ev->setCancelled();
				$pw = $this->pwds[$n];
				unset($this->pwds[$n]);

				if (!$this->auth->unregisterPlayer($pl)) {
					$pl->sendMessage(TextFormat::RED.mc::_("registration error"));
					return;
				}
				if (!$this->auth->registerPlayer($pl,$pw)) {
					$pl->kick(mc::_("registration error"));
					return;
				}
				$pl->sendMessage(TextFormat::GREEN.mc::_("chpwd ok"));
				return;
			}
			// New user registration...
			if (!$this->auth->registerPlayer($pl,$this->pwds[$n])) {
				$pl->kick(mc::_("registration error"));
				return;
			}
			if (!$this->auth->authenticatePlayer($pl)) {
				$pl->kick(mc::_("auth error"));
				return;
			}
			unset($this->pwds[$n]);
			$ev->setMessage("~");
			$ev->setCancelled();
			$pl->sendMessage(TextFormat::GREEN.mc::_("register ok"));
			return;
		}
		if ($this->cfg["leet-mode"]) {
			$msg = $ev->getMessage();
			if (preg_match(self::RE_LOGIN,$msg)) {
				$pl->sendMessage(TextFormat::YELLOW.mc::_("snob login"));
			} else {
				$ev->setMessage("/login $msg");
			}
		} else {
			$ev->setMessage("/login ".$ev->getMessage());
		}
		if ($this->cfg["max-attempts"] > 0) {
			if (isset($this->pwds[$n])) {
				++$this->pwds[$n];
			} else {
				$this->pwds[$n] = 1;
			}
			$this->getServer()->getScheduler()->scheduleDelayedTask(new PluginCallbackTask($this,[$this,"checkLoginCount"],[$n]),5);
		}
		return;
	}
	public function checkTimeout($n) {
		//echo __METHOD__.",".__LINE__."($n)\n"; //##DEBUG;
		$pl = $this->getServer()->getPlayer($n);
		if ($pl && !$this->auth->isPlayerAuthenticated($pl)) {
			//echo __METHOD__.",".__LINE__."($n)\n"; //##DEBUG;
			$pl->kick(mc::_("login timeout"));
		}
	}
	public function checkLoginCount($n) {
		if (!isset($this->pwds[$n])) return;
		$pl = $this->getServer()->getPlayer($n);
		if ($pl && !$this->auth->isPlayerAuthenticated($pl)) {
			if ($this->pwds[$n] >= $this->cfg["max-attempts"]) {
				$pl->kick(mc::_("too many logins"));
				unset($this->pwds[$n]);
			}
			return;
		}
		unset($this->pwds[$n]);
		return;
	}
	public function checkPwd($pl,$pwd) {
		if (preg_match('/\s/',$pwd)) {
			$pl->sendMessage(TextFormat::RED.mc::_("no spaces"));
			return false;
		}
		if (strlen($pwd) < $this->auth->getConfig()->get("minPasswordLength")){
			$pl->sendMessage(TextFormat::RED.mc::_("register.error.password %1%",
										  $this->auth->getConfig()->get("minPasswordLength")));
			return false;
		}
		if (strtolower($pl->getName()) == strtolower($pwd)) {
			$pl->sendMessage(TextFormat::RED.mc::_("not name"));
			return false;
		}
		return true;
	}
	protected function authenticate($pl,$password) {
		$provider = $this->auth->getDataProvider();
		if (($data = $provider->getPlayer($pl)) === null) {
			return false;
		}
		return hash_equals($data["hash"], $this->hash(strtolower($pl->getName()), $password));
	}

	//////////////////////////////////////////////////////////////////////
	//
	// Commands
	//
	//////////////////////////////////////////////////////////////////////
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		if (!$this->auth) {
			$sender->sendMessage(TextFormat::RED.mc::_("SimpleAuthHelper has been disabled"));
			$sender->sendMessage(TextFormat::RED.mc::_("SimpleAuth not found!"));
			return true;
		}
		switch($cmd->getName()){
			case "chpwd":
				if (!($sender instanceof Player)) {
					$sender->sendMessage(TextFormat::RED.
												mc::_("This command only works in-game."));
					return true;
				}
				if (count($args) == 0) return false;
				if(!$this->auth->isPlayerRegistered($sender)) {
					$sender->sendMessage(TextFormat::YELLOW.mc::_("register first"));
					return true;
				}
				if ($this->authenticate($sender,implode(" ", $args))) {
					$this->chpwd[$sender->getName()] = $sender->getName();
					$sender->sendMessage(TextFormat::AQUA.mc::_("chpwd msg"));
					return true;
				}
				$sender->sendMessage(TextFormat::RED.mc::_("chpwd error"));
				return false;
				break;
			case "resetpwd":
				foreach($args as $name){
					$player = $this->getServer()->getOfflinePlayer($name);
					if($this->auth->unregisterPlayer($player)){
						$sender->sendMessage(TextFormat::GREEN . mc::_("%1% unregistered",$name));
						if($player instanceof Player){
							$player->sendMessage(TextFormat::YELLOW.mc::_("You are no longer registered!"));
							$this->auth->deauthenticatePlayer($player);
						}
					}else{
						$sender->sendMessage(TextFormat::RED . mc::_("Unable to unregister %1%",$name));
					}
					return true;
				}
				break;
		}
		return false;
	}
	/**
	 * COPIED FROM SimpleAuth by PocketMine team...
	 *
	 * Uses SHA-512 [http://en.wikipedia.org/wiki/SHA-2] and Whirlpool [http://en.wikipedia.org/wiki/Whirlpool_(cryptography)]
	 *
	 * Both of them have an output of 512 bits. Even if one of them is broken in the future, you have to break both of them
	 * at the same time due to being hashed separately and then XORed to mix their results equally.
	 *
	 * @param string $salt
	 * @param string $password
	 *
	 * @return string[128] hex 512-bit hash
	 */
	private function hash($salt, $password){
		return bin2hex(hash("sha512", $password . $salt, true) ^ hash("whirlpool", $salt . $password, true));
	}
}
