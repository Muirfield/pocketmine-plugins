<?php
namespace aliuly\localchat;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;

class Main extends PluginBase implements Listener {
	protected $near;
	protected $far;

	// Access and other permission related checks
	private function access(CommandSender $sender, $permission) {
		if($sender->hasPermission($permission)) return true;
		$sender->sendMessage("You do not have permission to do that.");
		return false;
	}
	private function inGame(CommandSender $sender,$msg = true) {
		if ($sender instanceof Player) return true;
		if ($msg) $sender->sendMessage("You can only use this command in-game");
		return false;
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Standard call-backs
	//
	//////////////////////////////////////////////////////////////////////
	public function onEnable(){
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$defaults = [
			"settings" => [
				"near" => 10,
				"far" => 20,
			],
		];

		$cfg = (new Config($this->getDataFolder()."config.yml",
								 Config::YAML,$defaults))->getAll();
		$this->near = (float)$cfg["settings"]["near"];
		$this->far = (float)$cfg["settings"]["far"];
		if ($this->near < 3) {
			$this->getLogger()->info(TextFormat::RED.
											 "Configured value for \"near\" is too small");
			$this->near = 3.0;
		}
		if ($this->far <= $this->near) {
			$this->getLogger()->info(TextFormat::RED.
											 "\"far\" value must be greater than \"near\" value");
			$this->far = $this->near * 2.0;
		}

	}
	//
	// Compute stuff...
	//
	public function checkNear(Player $from,Player $to,$msg) {
		if ($from == $to) return true;
		if ($to->hasPermission("localchat.spy")) return true;
		if ($from->getLevel() != $to->getLevel()) return false; // Different worlds
		$diff = (float)$from->distance($to);
		//echo "DIFF: $diff\n";
		if ($diff < $this->near) return true; // Close enough
		if ($diff > $this->far) return false; // too far...
		$snr = ($diff - $this->near)/($this->far - $this->near);
		if ($snr > 0.9) $snr = 0.9;
		//echo "SNR: $snr\n";
		$nmsg = [];
		foreach (preg_split('/\s+/',$msg) as $word) {
			if ((mt_rand() / mt_getrandmax()) < $snr) $word = str_shuffle($word);
			$nmsg[] = $word;
		}
		return implode(" ",$nmsg);
	}

	//////////////////////////////////////////////////////////////////////
	//
	// Event handlers
	//
	//////////////////////////////////////////////////////////////////////
	public function onChat(PlayerChatEvent $e){
		if ($e->isCancelled()) return;
		$pw = $e->getPlayer();
		// Non players are handled normally
		if (!($pw instanceof Player)) return;

		$msg = $e->getMessage();
		if (substr($msg,0,1) == ":") {
			// This messages goes to everybody on the server...
			// no need to do much...
			if (!$this->access($pw,"localchat.broadcast.server")) {
				$e->setCancelled();
				return;
			}
			$e->setMessage(substr($msg,1));
			return;
		}
		$near = [];
		if (substr($msg,0,1) == ".") {
			if (!$this->access($pw,"localchat.broadcast.level")) {
				$e->setCancelled();
				return;
			}
			// Send this message to everybody on this level
			$e->setMessage(substr($msg,1));
			foreach ($e->getRecipients() as $pr) {
				if ($pr instanceof Player) {
					if ($pr->getLevel() != $pw->getLevel()) continue;
				}
				$near[] = $pr;
			}
		} else {
			foreach ($e->getRecipients() as $pr) {
				if ($pr instanceof Player) {
					$out = $this->checkNear($pw,$pr,$msg);
					if ($out === false) continue;
					if ($out !== true) {
						// Garbled message...
						$pr->sendMessage("Overheard: $out");
						continue;
					}
				}
				$near[] = $pr;
			}
		}
		if (count($near) == 0) {
			$e->setCancelled();
			// No need to send anymore... nobody there to hear it
			return;
		}
		$e->setRecipients($near);
	}
}
