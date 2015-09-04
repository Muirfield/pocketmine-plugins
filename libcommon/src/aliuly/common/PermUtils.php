<?php
namespace aliuly\common;

use pocketmine\plugin\Plugin;
use pocketmine\permission\Permission;

/**
 * Simple class encapsulating some Permission related utilities
 */
abstract class PermUtils {
  /**
   * Register a permission on the fly...
   * @param Plugin $plugin - owning plugin
   * @param str $name - permission name
   * @param str $desc - permission description
   * @param str $default - one of true,false,op,notop
   */
  static public function add(Plugin $plugin, $name, $desc, $default) {
    $perm = new Permission($name,$desc,$default);
    $plugin->getServer()->getPluginManager()->addPermission($perm);
  }
}
