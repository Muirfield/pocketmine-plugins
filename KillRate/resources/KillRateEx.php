<?php

/**
 * KillRate Extensions
 *
 * This is just an example on additional features that can be implemented
 * using KillRate.
 *
 * This script plugin depends on PurePerms, libcommon and KillRate.
 *
 * Sample command:
 *
 * * krgive <player> <points> - award <player> some <points>
 *
 * Sample levels implementation
 *
 * For this example, you need to create in PurePerms the following groups:
 *
 * lvl0, lvl1, lvl2, lvl3, lvl4, lvl5.
 *
 * You can give the different groups different permissions (that may give
 * access to different features).  The default group should be "lvl0".
 *
 * As players start scoring points, they will go up levels.
 *
 * @name KillRateEx
 * @main script\KillRateEx
 * @version 1.0.0
 * @api 1.12.0
 * @author aliuly
 * @description Simple command implementations
 * @softdepend libcommon
 * @depend KillRate
 */


namespace script{
	use pocketmine\plugin\PluginBase;
	use pocketmine\command\ConsoleCommandSender;
	use pocketmine\command\CommandExecutor;
	use pocketmine\command\CommandSender;
	use pocketmine\command\Command;
	use pocketmine\event\Listener;
	use pocketmine\utils\TextFormat;

	use aliuly\killrate\api\KillRateScoreEvent;
	use aliuly\killrate\api\KillRateResetEvent;
	use aliuly\common\MPMU;


	class KillRateEx extends PluginBase implements CommandExecutor,Listener{
		public $kr;
		public $pp;
		public function onEnable(){
			$this->kr = $this->getServer()->getPluginManager()->getPlugin("KillRate");
			if (!$this->kr || intval($this->kr->getDescription()->getVersion()) != 2) {
				$this->getLogger()->error(TextFormat::RED."Unable to find KillRate");
				throw new \RuntimeException("Missinge Dependancy");
				return;
			}
			$this->pp = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
			if (!$this->pp) {
				$this->getLogger()->error(TextFormat::RED."Unable to find PurePerms");
				throw new \RuntimeException("Missinge Dependancy");
				return;
			}
			MPMU::addCommand($this,$this,"krgive",[
					"description" => "Add points to KillRate score",
					"usage" => "/krgive <player> <points>",
				]);
			$this->getServer()->getPluginManager()->registerEvents($this,$this);
		}
		public function onScoreReset(KillRateResetEvent $ev) {
			$ev->getPlayer()->sendMessage("You are being demoted to Level 0!");
			$this->pp->getUser($ev->getPlayer())->setGroup($this->pp->getDefaultGroup(), null);
		}
		public function onScoreAdd(KillRateScoreEvent $ev) {
			$clevel = intval(substr($this->pp->getUser($ev->getPlayer())->getGroup()->getName(),3));
			if ($clevel >= 5) return; // max level is 5!
			if (!$ev->getPoints() || $ev->getPoints() < 0)  return; // Actually deducting points!

			$cscore = $this->kr->getScore($ev->getPlayer());
			$threshold = ($clevel + 1) * ($clevel + 1) * 1000;

			if ($cscore + $ev->getPoints < $threshold) return; // Did not manage to raise level yet!

			$nlevel ="lvl" . intval( $clevel + 1 );
			$ev->getPlayer()->sendMessage("Congratulations!");
			$this->getServer()->broadcastMessage(TextFormat::YELLOW.
														$ev->getPlayer()->getDisplayName().
														" is now ".$nlevel);
			$this->pp->getUser($ev->getPlayer())->setGroup($this->pp->getGroup($nlevel),null);
		}
		public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
			switch($cmd->getName()) {
				case "krgive":
				  if (count($args) != 2) return false;
					list($player,$points) = $args;
					if (!is_numeric($points)) return !false;
					$player = $this->getServer()->getPlayer($player);
					if ($player == null) {
						$sender->sendMessage(TextFormat::RED.$args[0]." does not exist");
						return true;
					}
					$points = intval($points);
					$kr->api->updateScore($player,"points",$points);
					$sender->sendMessage(TextFormat::GREEN."Awarding ".$points." points to ".$player->getDisplayName());
					$player->sendMessage(TextFormat::YELLOW."You have been awarded ".$points." by ".$sender->getDisplayName());
					return true;
			}
			return false;
		}
	}
}
