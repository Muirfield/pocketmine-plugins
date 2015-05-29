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

class Main extends PluginBase implements Listener,CommandExecutor {
	protected $auth;
	protected $pwds;
	protected $chpwd;
	protected $cfg;

	public function onEnable(){
		$this->auth = $this->getServer()->getPluginManager()->getPlugin("SimpleAuth");
		if (!$this->auth) {
			$this->getLogger()->error(TextFormat::RED."Unable to find SimpleAuth");
			throw new \RuntimeException("Missinge Dependancy");
			return;
		}
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		$defaults = [
			"version" => $this->getDescription()->getVersion(),
			"messages" => [
				"re-enter pwd" => "Please re-enter password to confirm:",
				"passwords dont match" => "Passwords do not match.\nPlease try again!\nEnter a new password:",
				"register ok" => "You have been registered!",
				"no spaces" => "Password should not contain spaces or tabs",
				"not name" => "Password should not be your name",
				"too many logins" => "You have attempted to login too many times.",
				"login timeout" => "Login timer expired!",
				"register first" => "You must first be registered",
				"chpwd msg" => "Enter your new password:",
				"chpwd error" => "Old password does not match",
				"chpwd ok" => "Password changed succesfully",
				"registration error" => "Registration error.  Try again later!",
				"auth error" => "Authentication error.  Try again later!",
				"chat protected" => "Do not send your password on the chat window",
				"snob login" => "Actually, you don't really need to type /login",
			],
			"nest-egg" => [
				"STONE_SWORD:0:1",
				"WOOD:0:16",
				"COOKED_BEEF:0:5",
				"GOLD_INGOT:0:10",
			],
			"max-attempts" => 5,
			"login-timeout" => 60,
			"auto-ban" => false,
			"lamer-mode" => false,
			"chat-protect" => false,
		];
		if (file_exists($this->getDataFolder()."config.yml")) {
			unset($defaults["nest-egg"]);
		}
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
		echo __METHOD__.",".__LINE__."\n"; //##DEBUG;
		$pl = $ev->getPlayer();
		$n = $pl->getName();
		if ($this->auth->isPlayerAuthenticated($pl) && !isset($this->chpwd[$n])) {
			if ($this->cfg["chat-protect"]) {
				if ($this->authenticate($pl,$ev->getMessage())) {
					$pl->sendMessage($this->cfg["messages"]["chat protected"]);
					$ev->setMessage("**CENSORED**");
					$ev->setCancelled();
				}
			}
			return;
		}

		if (!$this->auth->isPlayerRegistered($pl) || isset($this->chpwd[$n])) {
			if (!isset($this->pwds[$n])) {
				if (!$this->checkPwd($pl,$ev->getMessage())) {
					$ev->setCancelled();
					$ev->setMessage("~");
					return;
				}
				$this->pwds[$n] = $ev->getMessage();
				$pl->sendMessage($this->cfg["messages"]["re-enter pwd"]);
				$ev->setCancelled();
				$ev->setMessage("~");
				return;
			}
			if ($this->pwds[$n] != $ev->getMessage()) {
				unset($this->pwds[$n]);
				$ev->setCancelled();
				$ev->setMessage("~");
				$pl->sendMessage($this->cfg["messages"]["passwords dont match"]);
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
					$pl->sendMessage($this->cfg["messages"]["registration error"]);
					return;
				}
				if (!$this->auth->registerPlayer($pl,$pw)) {
					$pl->kick($this->cfg["messages"]["registration error"]);
					return;
				}
				$pl->sendMessage($this->cfg["messages"]["chpwd ok"]);
				return;
			}
			// New user registration...
			if (!$this->auth->registerPlayer($pl,$this->pwds[$n])) {
				$pl->kick($this->cfg["messages"]["registration error"]);
				return;
			}
			if (!$this->auth->authenticatePlayer($pl)) {
				$pl->kick($this->cfg["messages"]["auth error"]);
				return;
			}
			unset($this->pwds[$n]);
			$ev->setMessage("~");
			$ev->setCancelled();
			$pl->sendMessage($this->cfg["messages"]["register ok"]);
			if (isset($this->cfg["nest-egg"]) && !$pl->isCreative()) {
				// Award a nest egg to player...
				foreach ($this->cfg["nest-egg"] as $i) {
					$r = explode(":",$i);
					if (count($r) != 3) continue;
					$item = Item::fromString($r[0].":".$r[1]);
					$item->setCount(intval($r[2]));
					$pl->getInventory()->addItem($item);
				}
			}
			return;
		}
		if ($this->cfg["lamer-mode"]) {
			$msg = $ev->getMessage();
			if (preg_match('/^\s*\/login\s+/',$msg)) {
				$pl->sendMessage($this->cfg["messages"]["snob login"]);
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
		echo __METHOD__.",".__LINE__."($n)\n"; //##DEBUG;
		$pl = $this->getServer()->getPlayer($n);
		if ($pl && !$this->auth->isPlayerAuthenticated($pl)) {
			echo __METHOD__.",".__LINE__."($n)\n"; //##DEBUG;
			$pl->kick($this->cfg["messages"]["login timeout"]);
		}
	}
	public function checkLoginCount($n) {
		echo __METHOD__.",".__LINE__."($n)\n"; //##DEBUG;
		if (!isset($this->pwds[$n])) return;
		echo __METHOD__.",".__LINE__."($n)\n"; //##DEBUG;
		$pl = $this->getServer()->getPlayer($n);
		if ($pl && !$this->auth->isPlayerAuthenticated($pl)) {
			echo __METHOD__.",".__LINE__."($n)\n"; //##DEBUG;
			if ($this->pwds[$n] >= $this->cfg["max-attempts"]) {
				echo __METHOD__.",".__LINE__."($n)\n"; //##DEBUG;
				if ($this->cfg["auto-ban"]) {
					// OK banning use for trying to hack...
					$ip = $pl->getAddress();
					$this->getServer()->getIPBans()->addBan($ip,"Too many login attempts",null,"SimpleAuthHelper");
					$this->getServer()->blockAddress($ip,-1);
					$this->getServer()->broadcastMessage("[Helper] Banned IP Address $ip");
				}

				$pl->kick($this->cfg["messages"]["too many logins"]);
				unset($this->pwds[$n]);
			}
			return;
		}
		unset($this->pwds[$n]);
		return;
	}
	public function checkPwd($pl,$pwd) {
		if (preg_match('/\s/',$pwd)) {
			$pl->sendMessage($this->cfg["messages"]["no spaces"]);
			return false;
		}
		if (strlen($pwd) < $this->auth->getConfig()->get("minPasswordLength")){
			$pl->sendMessage($this->auth->getMessage("register.error.password"));
			return false;
		}
		if (strtolower($pl->getName()) == strtolower($pwd)) {
			$pl->sendMessage($this->cfg["messages"]["not name"]);
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
			$sender->sendMessage(TextFormat::RED."SimpleAuthHelper has been disabled");
			$sender->sendMessage(TextFormat::RED."SimpleAuth not found!");
			return true;
		}
		switch($cmd->getName()){
			case "chpwd":
				if (!($sender instanceof Player)) {
					$sender->sendMessage(TextFormat::RED.
												"This command only works in-game.");
					return true;
				}
				if (count($args) == 0) return false;
				if(!$this->auth->isPlayerRegistered($sender)) {
					$sender->sendMessage($this->cfg["messages"]["register first"]);
					return true;
				}
				if ($this->authenticate($sender,implode(" ", $args))) {
					$this->chpwd[$sender->getName()] = $sender->getName();
					$sender->sendMessage($this->cfg["messages"]["chpwd msg"]);
					return true;
				}
				$sender->sendMessage($this->cfg["messages"]["chpwd error"]);
				return false;
				break;
			case "resetpwd":
				foreach($args as $name){
					$player = $this->getServer()->getOfflinePlayer($name);
					if($this->auth->unregisterPlayer($player)){
						$sender->sendMessage(TextFormat::GREEN . "$name unregistered");
						if($player instanceof Player){
							$player->sendMessage(TextFormat::YELLOW."You are no longer registered!");
							$this->auth->deauthenticatePlayer($player);
						}
					}else{
						$sender->sendMessage(TextFormat::RED . "Unable to unregister $name");
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
