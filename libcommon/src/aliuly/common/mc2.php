<?php
namespace aliuly\common;

use pocketmine\utils\TextFormat;
use aliuly\common\mc;

/**
 * Very PocketMine-MP specific extension to the mc package
 */
abstract class mc2 {
	/**
	 * Checks message files and nags the user to submit translations...
	 *
	 * @param Plugin $plugin - owning plugin
	 * @param str $path - output of $plugin->getFile()
	 * @return int|false - false on error or the number of messages loaded
	 */
	public static function plugin_init_alt($plugin,$path) {
		$lang = $plugin->getServer()->getProperty("settings.language");
		if (mc::plugin_init($plugin,$path) === false && $lang != "eng") {
			list($fp,$fill) = [$plugin->getResource("messages/eng.ini"),"English"];
			if ($fp === null) list($fp,$fill) = [ $plugin->getResource("messages/messages.ini"),"EMPTY"];
			if ($fp === null) return false;
			file_put_contents($plugin->getDataFolder()."messages.ini",stream_get_contents($fp)."\n\"<nagme>\"=\"yes\"\n");
			mc::plugin_init($plugin,$path);
			$plugin->getLogger()->error(TextFormat::RED."Your selected language \"".$lang."\" is not supported");
			$plugin->getLogger()->error(TextFormat::YELLOW."Creating a custom \"messages.ini\" with ".$fill." strings");
			$plugin->getLogger()->error(TextFormat::AQUA."Please consider translating and submitting a translation");
			$plugin->getLogger()->error(TextFormat::AQUA."to the developer");
			$plugin->getLogger()->error(TextFormat::YELLOW."If you later change your language in \"pocketmine.yml\"");
			$plugin->getLogger()->error(TextFormat::YELLOW."make sure you delete this \"messages.ini\"");
			$plugin->getLogger()->error(TextFormat::YELLOW."otherwise your changes will not be recognized");
			return;
		}
		if (mc::_("<nagme>") !== "yes") return;

		// Potentially the language may exists since this was created...
		$fp = $plugin->getResource("messages/".$lang.".ini");
		if($fp === null && $lang != "eng"){
			$plugin->getLogger()->error(TextFormat::RED."Your selected language \"".$lang."\" is not supported");
			$plugin->getLogger()->error(TextFormat::AQUA."Please consider translating \"messages.ini\"");
			$plugin->getLogger()->error(TextFormat::AQUA."and submitting a translation to the  developer");
			return;
		}
		if ($fp !== null) fclose($fp);
		// This language is actually supported...
		$plugin->getLogger()->error(TextFormat::RED."Using a supported language: \"".$lang."\"");
		$plugin->getLogger()->error(TextFormat::YELLOW."Saving/Fixing \"messages.ini\" as");
		$plugin->getLogger()->error(TextFormat::YELLOW."\"messages.bak\"...");
		$orig = file_get_contents($plugin->getDataFolder()."messages.ini");
		file_put_contents($plugin->getDataFolder()."messages.bak",strtr($orig,["<nagme>"=>"<don't nagme>"]));
		unlink($plugin->getDataFolder()."messages.ini");
	}
}
