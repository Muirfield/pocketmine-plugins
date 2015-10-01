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

use SimpleAuth\event\PlayerAuthenticateEvent;

use aliuly\helper\common\PluginCallbackTask;
use aliuly\helper\common\mc;
use aliuly\helper\common\MPMU;

use aliuly\helper\EventListener;
use aliuly\helper\PermsHacker;
use aliuly\helper\DbMonitorTask;

class Main extends PluginBase implements Listener,CommandExecutor {
	const RE_REGISTER = '/^\s*\/register\s+/';
	const RE_LOGIN = '/^\s*\/login\s+/';

	public $auth;
	protected $pwds;
	protected $chpwd;
	protected $cfg;
	protected $listener;
	protected $permshacker;
	protected $monitor;

	public function onEnable(){
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());

		if (mc::plugin_init($this,$this->getFile()) === false) {
			file_put_contents($this->getDataFolder()."messages.ini",MPMU::getResourceContents($this,"messages/eng.ini")."\n\"<nagme>\"=\"yes\"\n");
			mc::plugin_init($this,$this->getFile());
			$this->getLogger()->error(TextFormat::RED."Your selected language \"".$this->getServer()->getProperty("settings.language")."\" is not supported");
			$this->getLogger()->error(TextFormat::YELLOW."Creating a custom \"messages.ini\" with English strings");
			$this->getLogger()->error(TextFormat::AQUA."Please consider translating and submitting a translation");
			$this->getLogger()->error(TextFormat::AQUA."to the developer");
			$this->getLogger()->error(TextFormat::YELLOW."If you later change your language in \"pocketmine.yml\"");
			$this->getLogger()->error(TextFormat::YELLOW."make sure you delete this \"messages.ini\"");
			$this->getLogger()->error(TextFormat::YELLOW."otherwise your changes will not be recognized");
		} else {
			if (mc::_("<nagme>") === "yes") {
				// Potentially the language may exists since this was created...
				$ln = $this->getServer()->getProperty("settings.language");
				$fp = $this->getResource("messages/".$ln.".ini");
				if($fp === null){
					$this->getLogger()->error(TextFormat::RED."Your selected language \"".$ln."\" is not supported");
					$this->getLogger()->error(TextFormat::AQUA."Please consider translating \"messages.ini\"");
					$this->getLogger()->error(TextFormat::AQUA."and submitting a translation to the  developer");
				} else {
					fclose($fp);
					// This language is actually supported...
					$this->getLogger()->error(TextFormat::RED."Using a supported language: \"".$ln."\"");
					$this->getLogger()->error(TextFormat::YELLOW."Saving/Fixing \"messages.ini\" as");
					$this->getLogger()->error(TextFormat::YELLOW."\"messages.bak\"...");
					$orig = file_get_contents($this->getDataFolder()."messages.ini");
					file_put_contents($this->getDataFolder()."messages.bak",strtr($orig,["<nagme>"=>"<don't nagme>"]));
					unlink($this->getDataFolder()."messages.ini");
				}
			}
		}

		$this->auth = $this->getServer()->getPluginManager()->getPlugin("SimpleAuth");
		if (!$this->auth) {
			$this->getLogger()->error(TextFormat::RED.mc::_("Unable to find SimpleAuth"));
			throw new \RuntimeException("Missing Dependancy");
			return;
		}

		$defaults = [
			//= cfg:main
			"version" => $this->getDescription()->getVersion(),
			"# max-attemps" => "kick player after this many login attempts. ",// NOTE: This conflicts with SimpleAuth's blockAfterFail setting
			"max-attempts" => 5,
			"# login-timeout" => "must authenticate within this number of seconds",
			"login-timeout" => 60,
			"# leet-mode" => "lets players use also /login and /register",
			"leet-mode" => true,
			"# chat-protect" => "prevent player to display their password in chat",
			"chat-protect" => false,
			"# hide-unauth" => "EXPERIMENTAL, hide unauthenticated players",
			"hide-unauth" => false,
			"# event-fixer" => "EXPERIMENTAL, cancels additional events",// for unauthenticated players
			"event-fixer" => false,
			"# hack-login-perms" => "EXPERIMENTAL, overrides login permisions",//to make sure players can login
			"hack-login-perms" => false,
			"# hack-register-perms" => "EXPERIMENTAL, overrides register permisions",//to make sure players can register
			"hack-register-perms" => false,
			"# db-monitor" => "EXPERIMENTAL, enable database server monitoring",
			"db-monitor" => false,
			"# monitor-settings" => "Configure database monitor settings",
			"monitor-settings" => DbMonitorTask::defaults(),
		];
		$this->cfg=(new Config($this->getDataFolder()."config.yml",
										  Config::YAML,$defaults))->getAll();

		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		if ($this->cfg["event-fixer"]) {
			$this->listener =new EventListener($this);
		}
		if ($this->cfg["hack-login-perms"] || $this->cfg["hack-register-perms"]) {
			$this->permshacker = new PermsHacker($this,$this->cfg["hack-login-perms"],$this->cfg["hack-register-perms"]);
		}
		if ($this->cfg["db-monitor"]) {
			$this->monitor = new DbMonitorTask($this,$this->cfg["monitor-settings"]);
		}
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
		if ($this->cfg["login-timeout"] !== 0) {
			$n = $ev->getPlayer()->getName();
			$this->getServer()->getScheduler()->scheduleDelayedTask(new PluginCallbackTask($this,[$this,"checkTimeout"],[$n]),$this->cfg["login-timeout"]*20);
		}
		if ($this->cfg["hide-unauth"]) {
			$p = $ev->getPlayer();
			foreach($this->getServer()->getOnlinePlayers() as $online){
				$online->hidePlayer($p);
				$p->hidePlayer($online);
			}
			$ev->setJoinMessage("");
			//
		}
	}
	public function onAuthenticate(PlayerAuthenticateEvent $ev) {
		if (!$this->cfg["hide-unauth"]) return;
		$pl = $ev->getPlayer();
		$this->getServer()->broadcastMessage(TextFormat::YELLOW.mc::_("%1% has just joined", $pl->getDisplayName()));
		foreach($this->getServer()->getOnlinePlayers() as $online){
			$online->showPlayer($pl);
			if ($this->auth->isPlayerAuthenticated($online)) {
				$pl->showPlayer($online);
			}
		}
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
	public function checkPwd($pl,$pwd, $name = null) {
		if (preg_match('/\s/',$pwd)) {
			$pl->sendMessage(TextFormat::RED.mc::_("no spaces"));
			return false;
		}
		if (strlen($pwd) < $this->auth->getConfig()->get("minPasswordLength")){
			$pl->sendMessage(TextFormat::RED.mc::_("register.error.password %1%",
										  $this->auth->getConfig()->get("minPasswordLength")));
			return false;
		}
		if (strtolower($name === null ? $pl->getName() : $name) == strtolower($pwd)) {
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
	private function chpwd(CommandSender $sender, $oldpwd) {
		if (!($sender instanceof Player)) {
			$sender->sendMessage(TextFormat::RED.
										mc::_("This command only works in-game."));
			return true;
		}
		if(!$this->auth->isPlayerRegistered($sender)) {
			$sender->sendMessage(TextFormat::YELLOW.mc::_("register first"));
			return true;
		}
		if ($this->authenticate($sender,$oldpwd)) {
			$this->chpwd[$sender->getName()] = $sender->getName();
			$sender->sendMessage(TextFormat::AQUA.mc::_("chpwd msg"));
			return true;
		}
		$sender->sendMessage(TextFormat::RED.mc::_("chpwd error"));
		return false;
	}
	private function resetpwd($sender, $name) {
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
	private function logout($sender) {
		if (!($sender instanceof Player)) {
			$sender->sendMessage(TextFormat::RED.
										mc::_("This command only works in-game."));
			return true;
		}
		if(!$this->auth->isPlayerAuthenticated($sender)) {
			$sender->sendMessage(TextFormat::YELLOW.mc::_("login first"));
			return true;
		}
		$sender->sendMessage(TextFormat::GREEN.mc::_("logout completed"));
		$this->auth->deauthenticatePlayer($sender);
		return true;
	}
	private function prereg($sender,$name,$newpwd) {
		$player = $this->getServer()->getOfflinePlayer($name);
		if ($this->auth->isPlayerRegistered($player)) {
			$sender->sendMessage(TextFormat::RED.mc::_("%1% already registered", $name));
			return true;
		}
		if (!$this->checkPwd($sender,$newpwd,$name)) return true;
		if ($this->auth->registerPlayer($player,$newpwd)) {
			$sender->sendMessage(TextFormat::GREEN.mc::_("registered %1%", $name));
			$sender->sendMessage("OK");
		} else {
			$sender->sendMessage(TextFormat::RED.mc::_("error registering %1%", $name));
		}
		return true;
	}
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		if (!$this->auth) {
			$sender->sendMessage(TextFormat::RED.mc::_("SimpleAuthHelper has been disabled"));
			$sender->sendMessage(TextFormat::RED.mc::_("SimpleAuth not found!"));
			return true;
		}
		switch($cmd->getName()){
			case "chpwd":
				if (count($args) == 0) return false;
				return $this->chpwd($sender, implode(" ", $args));
			case "resetpwd":
				if (count($args) != 1) return false;
				return $this->resetpwd($sender, $args[0]);
			case "logout":
				if (count($args) != 0) return false;
				return $this->logout($sender);
			case "preregister":
				if (count($args) != 2) return false;
				return $this->prereg($sender,$args[0],$args[1]);
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
