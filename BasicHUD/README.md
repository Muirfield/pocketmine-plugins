<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/hud.jpg" style="width:64px;height:64px" width="64" height="64"/>

BasicHUD
========

* Summary: A configurable heads up display
* Dependency Plugins: n/a
* PocketMine-MP version: 1.5 (API:1.12.0)
* DependencyPlugins: -
* OptionalPlugins: -
* Categories: Informational
* Plugin Access: Other Plugins
* WebSite: https://github.com/alejandroliu/pocketmine-plugins/tree/master/BasicHUD

## Overview

<!-- php: $v_forum_thread = "http://forums.pocketmine.net/threads/basichud.1222/"; -->
<!-- template: prologue.md -->

**DO NOT POST QUESTION/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you
have a question/bug-report/request please use the
[Thread](http://forums.pocketmine.net/threads/basichud.1222/) for
that.  You are more likely to get a response and help that way.

**NOTE:**

This documentation was last updated for version **1.0.6**.

Please go to
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/BasicHUD)
for the most up-to-date documentation.

You can also download this plugin from this [page](https://github.com/alejandroliu/pocketmine-plugins/releases/tag/BasicHUD-1.0.6).

<!-- template-end -->

This plugin lets you configure a basic Heads-Up Display (HUD) for
players.

## Basic Usage

* **/hud** _[on|off|format]_
  * If none specified, will show the current mode info.
  * If _on_ is specified, HUD will be turned on.
  * If _off_ is specified, HUD will be turned off.
  * If _format_ is specified, that needs to be configured in
    `config.yml` and that format will be used.

### Configuration

Configuration is through the `config.yml` file.
The following sections are defined:

#### main

*  ticks: How often to refresh the popup
*  format: Display format


### Permission Nodes

* basichud.user : Allow players to have an HUD
* basichud.cmd : Allow players to access HUD command
* basichud.cmd.toggle : Allow players to toggle HUD on/off
* basichud.cmd.switch : Allow players to switch formats
  (Defaults to Op)


## Configured Formats

The displayed text can be:

* A fixed string.
* A string containing {variables}
* A string containing <?php and <?=.  This allows you to embed
  arbitrary PHP code in the format.  This is similar to how web pages
  are done.

The default variables are:

* {BasicHUD}
* {MOTD}
* {tps}
* {player}
* {world}
* {x}
* {y}
* {z}
* {yaw}
* {pitch}
* {bearing}
* {10SPACE}
* {20SPACE}
* {30SPACE}
* {40SPACE}
* {50SPACE}
* {NL}
* {BLACK}
* {DARK_BLUE}
* {DARK_GREEN}
* {DARK_AQUA}
* {DARK_RED}
* {DARK_PURPLE}
* {GOLD}
* {GRAY}
* {DARK_GRAY}
* {BLUE}
* {GREEN}
* {AQUA}
* {RED}
* {LIGHT_PURPLE}
* {YELLOW}
* {WHITE}
* {OBFUSCATED}
* {BOLD}
* {STRIKETHROUGH}
* {UNDERLINE}
* {ITALIC}
* {RESET}

You can add more variables by creating a `vars.php` in the plugin
directory.  For your convenience, there is `vars-example.php`
available that you can use as a starting point.  Copy this file to
`vars.php`.

The example `vars.php` will create a `{score}` and `{money}` variable
if you have the relevant plugins.

By default, if you have `SimpleAuth` installed, the HUD will be
inactive until you log-in.  If you are using something other than
`SimpleAuth` you can copy the `message-example.php` to `message.php`
and do whatever checks you need to do.

## Multi-Format options

BasicHUD supports multiple formats.  These can be configured through
the `config.yml`.  So instead of **format** only having **one** format
configured, you can configure multiple, like this example:

````YAML
[CODE]
format:
 lv3: '{GREEN}{BasicHUD} {YELLOW}Lv3 {WHITE}{world} ({x},{y},{z}) {bearing} {RED}EUR:{money} Pts:{score}'
 lv2: '{GREEN}{BasicHUD} {GREEN}Lv2 {WHITE}{world} ({x},{y},{z}) {bearing} {RED}EUR:{money} Pts:{score}'
 lv1: '{GREEN}{BasicHUD} {BLUE}Lv1 {WHITE}{world} ({x},{y},{z}) {bearing} {RED}EUR:{money} Pts:{score}'
 lv0: '{GREEN}{BasicHUD} {GRAY}Lv0 {WHITE}{world} ({x},{y},{z}) {bearing} {RED}EUR:{money} Pts:{score}'
[/CODE]
````

In this example, four formats are defined.  To select the format,
**BasicHUD** will test permissions in order until the player has the
permission:

* basichud.rank.selector

So if the player wants to use format _lv2_, permission
_basichud.rank.lv2_ is required.  For multiple matches, the first
match is used.  If none matches, the last format is used.

Switching formats is not saved.  So on join the player always gets the
default format.  If you want HUD format choices to be saved you need a
permissions plugin.

# API

Since **BasicHUD** takes over the built-in _sendPopup_ functionality,
it provides a replacement function for it.  To use it you need this
fragment of code:

````PHP
[PHP]
if (($hud = $this->getServer()->getPluginManager()->getPlugin("BasicHUD")) !== null) {
  $hud->sendPopup($player,$msg);
} else {
  $player->sendPopup($msg);
}
[/PHP]

````

# Changes

* 1.0.6:
  * Fixed vars-example (money issues thanks @vertx)
  * Display item selected onItemHeld (incomplete)
* 1.0.5: Performance tweaks
  * Cache permissions for selecting formats
  * An empty vars.php yields an empty function (saving a comparison)
  * Constant vars are calculated once.
  * Fixed bug around permissions (to change formats)
  * Added translation (but not for bearing to avoid the translation overhead)
  * New {vars}:
    * {tps}
    * {NL}
    * {10SPACE}, {20SPACE}, {30SPACE}, {40SPACE}, {50SPACE}
* 1.0.3: First public release
  * Added a "use" permission.
  * More correct use of permission
* 1.0.2: added features
  * command to turn on|off|change HUD
  * supports multiple HUD formats which can be selected based on
    permissions.
* 1.0.1: minor update
  * Added additional variables
  * Improved examples
  * changed defaults
  * Added API
* 1.0.0: First release

# Copyright

    BasicHUD
    Copyright (C) 2015 Alejandro Liu
    All Rights Reserved.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

