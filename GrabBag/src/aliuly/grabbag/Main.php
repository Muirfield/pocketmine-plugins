<?php
/**
 **
 ** CONFIG:features
 **
 ** This section you can enable/disable commands and listener modules.
 ** You do this in order to avoid conflicts between different
 ** PocketMine-MP plugins.  It has one line per feature:
 **
 **    feature: true|false
 **
 ** If `true` the feature is enabled.  if `false` the feature is disabled.
 **
 **/
namespace aliuly\grabbag;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerQuitEvent;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;
use aliuly\grabbag\common\BasicPlugin;

class Main extends BasicPlugin {
	public function onEnable(){
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		mc::plugin_init($this,$this->getFile());
		$features = [
			"players" => [ "CmdPlayers", true ],
			"ops" => [ "CmdOps", true ],
			"gm?" => [ "CmdGmx", true ],
			"as" => [ "CmdAs", true ],
			"slay" => [ "CmdSlay", true ],
			"heal" => [ "CmdHeal", true ],
			"whois" => [ "CmdWhois", true ],
			"mute-unmute" => [ "CmdMuteMgr", true ],
			"freeze-thaw" => [ "CmdFreezeMgr", true ],
			"showtimings" => [ "CmdTimings", true ],
			"seeinv-seearmor" => [ "CmdShowInv", true ],
			"clearinv" => [ "CmdClearInv", true ],
			"get" => [ "CmdGet", true ],
			"shield" => [ "CmdShieldMgr", true ],
			"srvmode" => [ "CmdSrvModeMgr", true ],
			"opms-rpt" => [ "CmdOpMsg", true ],
			"entities" => [ "CmdEntities", true ],
			"after-at" => [ "CmdAfterAt", true ],
			"summon-dismiss" => [ "CmdSummon", true ],
			"pushtp-poptp" => [ "CmdTpStack", true ],
			"prefix" => [ "CmdPrefixMgr", true ],
			"spawn" => [ "CmdSpawn", true ],
			"burn" => [ "CmdBurn", true ],
			"blowup" => [ "CmdBlowUp", true ],
			"setarmor" => [ "CmdSetArmor", true ],
			"spectator"=> [ "CmdSpectator", false ],
			"followers"=> [ "CmdFollowMgr", true ],
			"rcon-client" => [ "CmdRcon", true ],
			"join-mgr" => [ "JoinMgr", true ],
			"repeater" => [ "RepeatMgr", true ],
			"broadcast-tp" => [ "BcTpMgr", false ],
			"crash" => ["CmdCrash", true],
			"pluginmgr" => ["CmdPluginMgr", true],
			"throw" => ["CmdThrow", true],
	];
		if (MPMU::apiVersion("1.12.0")) {
			$features["fly"] = [ "CmdFly", true ];
		}

		$cfg = $this->modConfig(__NAMESPACE__,$features, [
			"version" => $this->getDescription()->getVersion(),
			"rcon-client" => [],
			"join-mgr" => JoinMgr::defaults(),
			"broadcast-tp" => BcTpMgr::defaults(),
			"freeze-thaw" => CmdFreezeMgr::defaults(),
		]);
	}
}
