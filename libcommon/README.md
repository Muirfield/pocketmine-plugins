<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/common.png" style="width:64px;height:64px" width="64" height="64"/>

<!-- meta:Categories = DevTools -->
<!-- meta:PluginAccess = N/A -->

<!-- template: gd2/header.md -->

# libcommon

- Summary: aliuly's common library
- PocketMine-MP version: 1.5 (API:1.12.0)
- DependencyPlugins: 
- OptionalPlugins: 
- Categories: DevTools 
- Plugin Access: N/A 
- WebSite: https://github.com/alejandroliu/pocketmine-plugins/tree/master/libcommon

<!-- end-include -->

## Overview

<!-- php: //$v_forum_thread = "http://forums.pocketmine.net/threads/simpleauthhelper.8074/"; -->
<!-- template: nn/prologue.md -->
<!-- MISSING TEMPLATE: nn/prologue.md ->

<!-- end-include -->

This plugin contains a lot of functionality that can be used in other
plugins, in particular, ScriptPlugins.

API Features:

<!-- snippet:api-features -->
- Armor constants
- Paginated output
- Command and sub command dispatchers
- Config shortcuts and multi-module|feature management
- Multiple money support
- Translations
- Teleport wrappers
- API version checking
- Misc shorcuts and pre-canned routines
- Player session and state management
- Skin tools
<!-- end-include -->

It also bundles useful third party libraries:

- xPaw MinecraftQuery

See [API documentation](http://alejandroliu.github.io/pocketmine-plugins/apidocs/index.html)
for full details.

The **libcommon** library is my standard library that I personally use when
writing PocketMine-MP plugins.  Normally I embed the different modules
when creating my plugins in order to avoid dependency issues.

For use on your own plugins, you can either use the stand-alone **libcommon**
phar, or use the one bundled in **GrabBag**.

This plugin can be downloaded from its
[Downloads](https://github.com/alejandroliu/pocketmine-plugins/tree/master/libcommon/downloads.md)
<img src="https://raw.githubusercontent.com/alejandroliu/bad-plugins/master/Media/download-icon.png" alt="Downloads"/>
page.

Example scripts can be found here:

* [GitHub Examples](https://github.com/alejandroliu/pocketmine-plugins/tree/master/libcommon/resources/examples/)

<!-- snippet: pmscript  -->
## PMScript

The PMScript module implements a simple [PHP](https://secure.php.net/)
based scripting engine.  It can be used to enter multiple PocketMine
commands while allowing you to add PHP code to control the flow of
the script.

While you can embed any arbitrary PHP code, for readability purposes
it is recommended that you use
[PHP's alternative syntax](http://php.net/manual/en/control-structures.alternative-syntax.php)

By convention, PMScript's have a file extension of ".pms" and they are
just simple text file containing PHP console commands (without the "/").

To control the execution you can use the following prefixes when
entering commands:

* **+op:** - will give Op access to the player (temporarily) before executing
  a command
* **+console:** - run the command as if it was run from the console.
* **+rcon:** - like **+console:** but the output is sent to the player.

Also, before executing a command variable expansion (e.g. {vars}) and
command selector expansion (e.g. @a, @r, etc) takes place.

Available variables depend on installed plugins, pocketmine.yml
settings, execution context, etc.

It is possible to use PHP functions and variables in command lines by
surrounding PHP expressions with:

     '.(php expression).'

For example:

     echo MaxPlayers: '.$interp->getServer()->getMaxPlayers().'

### Adding logic flow to PMScripts

Arbitrary PHP code can be added to your pmscripts.  Lines that start
with "@" are treated as PHP code.  For your convenience,
you can ommit ";" at the end of the line.

Any valid PHP code can be used, but for readability, the use of
alternative syntax is recommended.

The execution context for this PHP code has the following variables
available:

* **$interp** - reference to the running PMSCript object.
* **$context** - This is the CommandSender that is executing the script
* **$vars** - This is the variables array used for variable substitution
  when executing commands.
* **$args** - Command line arguments.
* **$env** - execution environment.  Empty by default but may be used
  by third party plugins.
* **$v_xxxxx** - When posible the variables use for command variable
  substitution are made available as **$v_xxxx**.  For example, the
  **{tps}** variable, will be available as **$v_tps**

Example:

    # Sample PMScript
    #
    ; You can use ";" or "#" as comments
    #
    # Just place your commands as you would enter them on the console
    # on your .pms file.
    echo You have the following plugins:
    plugins
    echo {GOLD}Variable {RED}Expansions {BLUE}are {GREEN}possible
    echo libcommon: {libcommon} MOTD: {MOTD}
    #
    # You can include in there PHP expressions...
    say '.$context->getName().' is AWESOME!
    # CommandSelectors are possible...
    echo Greeting everybody
    say Hello @a
    ;
    # Adding PHP control code is possible:
    @if ($v_tps > 10):
      echo Your TPS {tps} is greater than 10
    @else:
      echo Your TPS {tps} is less or equal to 10
    @endif
    ;
    ;
    echo The following variables are available in this context:
    echo '.print_r($vars,true).'
    echo You passed {#} arguments to this script.
<!-- end-include -->

### Command Selectors

Command selectors are available in PMScripts.

<!-- snippet: cmdselector  -->

This adds "@" prefixes for commands.
See
[Command Prefixes](http://minecraft.gamepedia.com/Commands#Target_selector_arguments)
for an explanation on prefixes.

This only implements the following prefixes:

- @a - all players
- @e - all entities (including players)
- @r - random player/entity

The following selectors are implemented:

- c: (only for @r),count
- m: game mode
- type: entity type, use Player for player.
- name: player's name
- w: world

<!-- end-include -->

<!-- php:$h=3; -->
<!-- template: gd2/permissions.md -->

### Permission Nodes

* libcommon.command: libcommon command

<!-- end-include -->

## Changes

- 1.91.0: De-bundle
  * New module: TPUtils
  * De-bundled, now it is just a library again.  All sub-commands were moved
    to GrabBag.
  * Bug-Fixes:
    - MPMU::callPlugin : Fixed
- 1.90.0: Major Update 2
  * MoneyAPI bug fix
  * Fixed BasicPlugin bug
  * Lots of new API features.
  * Added sub-commands
  * Bug Fixes:
    * MoneyAPI crash
    * BasicPlugin permission dispatcher
  * API Features
    * GetMotdAsyncTask
    * Session management
    * FileUtils
    * ArmorItems
    * Variables
    * PMScripts
    * ItemName can load user defined tables
    * SubCommandMap spinned-off from BasicPlugin
  * Sub commands
    * DumpMsgs
    * echo and basic echo
    * rc
    * motd utils
    * version
    * trace
- 1.1.0: Update 1
  * Added ItemName class (with more item names)
  * Removed MPMU::itemName
- 1.0.0: First release

<!-- php:$copyright="2015"; -->
<!-- template: gd2/gpl2.md -->
# Copyright

    libcommon
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

<!-- end-include -->

