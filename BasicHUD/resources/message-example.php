/*
 * This is the standard "getMessage" implementation.  If you want to
 * override it, copy "message-example.php" to "message.php".
 *
 * The following variables are available:
 *
 * $plugin - the HUD plugin
 * $player - current player
 */

if (($sa = $plugin->getServer()->getPluginManager()->getPlugin("SimpleAuth")) !== null) {
	// SimpleAuth also has a HUD when not logged in...
	if (!$sa->isPlayerAuthenticated($player)) return null;
}
return $plugin->defaultGetMessage($player);
