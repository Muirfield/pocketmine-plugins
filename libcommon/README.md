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

This plugin contains my standard library that I personally use when
writing PocketMine-MP plugins.  Normally I embed the differnt modules
when creating my plugins in order to avoid dependency issues.  However
**libcommon** is usable as a stand-alone plugin.

When used as stand-alone, it provides useful functionality that
can be called directly by script plugins.  Also, if **\pocketmine\DEBUG** > 1,
it defines some useful debugging and example commands.

For the most up to date documentation visit
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/libcommon).

This plugin can be downloaded from its
[Downloads](https://github.com/alejandroliu/pocketmine-plugins/tree/master/libcommon/downloads.md)
<img src="https://raw.githubusercontent.com/alejandroliu/bad-plugins/master/Media/download-icon.png" alt="Downloads"/>
page.

Features:

- Paginated output
- Command/Sub-command registration
- Player state management
- Config shortcuts and multi-module|feature management
- Translations
- Multiple economy supports
- API version checking
- Plugin shortcuts, etc...

It also bundles useful third party libraries:

- xPaw MinecraftQuery

For the full API documentation go to:
[GitHub pages](http://alejandroliu.github.io/pocketmine-plugins/libcommon/apidocs/index.html)

## Commands

Also, for debugging purposes, the **libcommon** command is provided, which
has the following sub-commands:

<!-- template: gd2/subcmds.md -->
* dumpmsg: Dump a plugin's messages.ini<br/>
  usage: /libcommon **dumpmsg** _&lt;plugin&gt;_

  This command is available when **DEBUG** is enabled.
* echo: shows the given text (variable substitutions are performed)<br/>
   usage: /libcommon **echo** _[text]_

  This command is available when **DEBUG** is enabled.
* motd-add: Add a server for MOTD querying<br/>
  usage: /libcommon **motd-add** _&lt;server&gt;_ _[port]_

  This command is available when **DEBUG** is enabled.

* motd-stat: Return the servers MOTD values<br/>
  usage: /libcommon **motd-stat**

  This command is available when **DEBUG** is enabled.
* query-add: Add a server for Query gathering<br/>
  usage: /libcommon **query-add** _&lt;server&gt;_ _[port]_

  This command is available when **DEBUG** is enabled.

* query-list: Return the available Query data<br/>
  usage: /libcommon **query-list**

  This command is available when **DEBUG** is enabled.
* rc: Runs the given script<br/>
  usage: usage: /libcommon **rc** _&lt;script&gt;_ _[args]_

  This command will execute PMScripts present in the **libcommon**
  folder.  By convention, the ".pms" suffix must be used for the file
  name, but the ".pms" is ommitted when issuing this command.

  The special script **autostart.pms** is executed automatically
  when the **libcommon** plugin gets enabled.

* version: shows the libcomonn version<br/>
   usage: /libcommon **version**

<!-- end-include -->

For use in PMScripts, a **echo** command is defined.  Unlike the
**libcommon echo** command, **echo** does not do any variable
substitutions.

## Command Selectors
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

Note that available variables depend on installed plugins, pocketmine.yml
settings, execution context, etc.

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

<!-- end-include -->

## Changes

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
- 1.1.0: Update 1
  * Added ItemName class (with more item names)
  * Removed MPMU::itemName
- 1.0.0: First release

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
