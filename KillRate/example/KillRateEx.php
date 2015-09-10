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
 * #depend KillRate, PurePerms
 *
 * Changes:
 * - 1.0.1: Minor change
 *   - Changed 3 constant for strlen("lvl")
 * - 1.0.0: First public release
 */


namespace script{
	use pocketmine\plugin\PluginBase;
	use pocketmine\event\Listener;
	use pocketmine\utils\TextFormat;

	use aliuly\killrate\api\KillRateScoreEvent;
	use aliuly\killrate\api\KillRateResetEvent;

	class KillRateEx extends PluginBase implements Listener{
		const LvlPrefix = "lvl";
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
			 * Get what is the current player levels as a number.
			 * In this example levels are called "lvl" which is defined in the
			 * constant LvlPrefix.
			 */
			$clevel = intval(substr($this->pp->getUser($ev->getPlayer())->getGroup()->getName(),strlen(self::LvlPrefix)));
			/*
			 * The example only defines 5 levels.  Feel free to change this
			 */
			if ($clevel >= 5) return; // max level is 5!

			/*
			 * Gets the current score
			 */
			$cscore = $this->kr->api->getScore($ev->getPlayer());
			/*
			 * These are the different number of points needed for that level.
			 * Feel free to change this
			 */
			switch($clevel+1){
				case 0: $threshold = 0; break; // Obviously this is not needed...
				case 1: $threshold = 1000; break;
				case 2: $threshold = 4000; break;
				case 3: $threshold = 8000; break;
				case 4: $threshold = 16000; break;
				case 5: $threshold = 32000; break;
				default: $threshold = 64000; break;
			}
			// If you are like me and prefer to use a formula, the next line
			// is an example on how to do a similar progression:
			// $threshold = ($clevel + 1) * ($clevel + 1) * 1000;

			if ($cscore + $ev->getPoints() < $threshold) return; // Did not manage to raise level yet!

			/*
			 * This determines the next group that corresponds to the new level.
			 * Make sure you change this if you change the group names.
			 */
			$nlevel =self::LvlPrefix . intval( $clevel + 1 );
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
	}
}
