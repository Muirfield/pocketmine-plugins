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
 * @version 1.0.1
 * @api 1.12.0
 * @author aliuly
 * @description Simple command implementations
 * @depend KillRate, PurePerms
 *
 * Changes:
 * - 1.0.1: Minor change
 *   - Changed 3 constant for strlen("lvl")
 * - 1.0.0: First public release
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
	use aliuly\killrate\common\MPMU;


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
			/*
			 * The /krgive command is used to give points to players.
			 * technically is not part of the level mechanism
			 */
			MPMU::addCommand($this,$this,"krgive",[
					"description" => "Add points to KillRate score",
					"usage" => "/krgive <player> <points>",
				]);
			$this->getServer()->getPluginManager()->registerEvents($this,$this);
		}
		public function onScoreReset(KillRateResetEvent $ev) {
			$ev->getPlayer()->sendMessage("You are being demoted to Level 0!");
			/*
			 * Make sure that the default group in the PurePerms configuration
			 * is the Level 0 group!
			 *
			 * This only happens if you enable the score reset when you die function
			 * in KillRate.
			 */
			$this->pp->getUser($ev->getPlayer())->setGroup($this->pp->getDefaultGroup(), null);
		}
		public function onScoreAdd(KillRateScoreEvent $ev) {
			/*
			 * If we deduct points we do nothing....  After a player gains a level
			 * they get to keep it (unless they die)
			 */
			if (!$ev->getPoints() || $ev->getPoints() < 0)  return; // Actually deducting points!
			/*
			 * Get what is the current player levels as a number.  The "3" is because
			 * in this example levels are called "lvl" followed by the number.
			 * If you change the group names make sure you change this line so that
			 * $clevel is always a number (integer)
			 *
			 */
			$clevel = intval(substr($this->pp->getUser($ev->getPlayer())->getGroup()->getName(),strlen("lvl")));
			/*
			 * The example only defines 5 levels.  Feel free to change this
			 */
			if ($clevel >= 5) return; // max level is 5!

			/*
			 * Gets the current score
			 */
			$cscore = $this->kr->api->getScore($ev->getPlayer());
			/*
			 * This formula figures out the number of points needed to level UP!
			 *
			 * The default uses a non-linear formula:
			 *
			 *   Lvl0 - 0 points
			 *   Lvl1 - 1000 points
			 *   Lvl2 - 4000 points
			 *   Lvl3 - 8000 points
			 *   Lvl4 - 16000 points
			 *   Lvl5 - 32000 points
			 *
			 * Feel free to change this
			 */
			$threshold = ($clevel + 1) * ($clevel + 1) * 1000;

			if ($cscore + $ev->getPoints() < $threshold) return; // Did not manage to raise level yet!

			/*
			 * This determines the next group that corresponds to the new level.
			 * Make sure you change this if you change the group names.
			 */
			$nlevel ="lvl" . intval( $clevel + 1 );
			/*
			 * Tell everybody the good news (so they can get jellous)
			 * and then change the player's PurePerms group
			 */
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
					$this->kr->api->updateScore($player,"points",$points);
					$sender->sendMessage(TextFormat::GREEN."Awarding ".$points." points to ".$player->getDisplayName());
					$player->sendMessage(TextFormat::YELLOW."You have been awarded ".$points." by ".$sender->getName());
					return true;
			}
			return false;
		}
	}
}
