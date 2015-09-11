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
<!-- MISSING TEMPLATE: no/prologue.md ->

<!-- end-include -->

This plugin contains a log of functionality that can be used in other
plugins, in particular, ScriptPlugins. It also provides for commands useful
for script debugging. (Note that some commands are only enabled if
**\pocketmine\DEBUG** > 1 is set in **pocketmine.yml**). The main features
it provides are:

* Functionality to use in other scripts (See
  [API documentation](http://alejandroliu.github.io/pocketmine-plugins/apidocs/index.html))
* Scripting functionality: mainly used for running test scripts, but could
  be used for creating custom commands by grouping a batch of PocketMine-MP
  commands together in a single file.
* Event tracing: you can select what events (or group of events) to trace
  on the fly. Events will be shown on console or on your chat area.
  De-duplication is done so as not to spam screens.
* Query and MOTD polling tasks

API Features:

<!-- snippet:api-features -->
- API version checking
- Misc shorcuts and pre-canned routines
- Paginated output
- Command and sub command dispatchers
- Config shortcuts and multi-module|feature management
- Armor constants
- Multiple money support
- Translations
- Player session and state management
- Teleport wrappers
<!-- end-include -->

It also bundles useful third party libraries:

- xPaw MinecraftQuery

## Documentation

This plugin contains my standard library that I personally use when
writing PocketMine-MP plugins.  Normally I embed the different modules
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

For the full API documentation go to:
[GitHub pages](http://alejandroliu.github.io/pocketmine-plugins/apidocs/index.html)

The following subcommands are available:
<!-- php:$h = 0; -->
<!-- template: gd2/cmdoverview.md -->

* echo: shows the given text (variable substitutions are performed)
* motd-add: Add a server for MOTD querying
* motd-stat: Return the servers MOTD values
* onevent: Run command on event
* query-add: Add a server for Query gathering
* query-list: Return the available Query data
* rc: Runs the given script
* trace: controls event tracing
* version: shows the libcommon version


<!-- end-include -->

### Commands

Also, for debugging purposes, the **libcommon** command is provided, which
has the following sub-commands:

<!-- template: gd2/subcmds.md -->
* echo: shows the given text (variable substitutions are performed)<br/>
   usage: /libcommon **echo** _[text]_

  This command is available when **DEBUG** is enabled.
* motd-add: Add a server for MOTD querying<br/>
  usage: /libcommon **motd-add** _&lt;server&gt;_ _[port]_

  This command is available when **DEBUG** is enabled.

* motd-stat: Return the servers MOTD values<br/>
  usage: /libcommon **motd-stat**

  This command is available when **DEBUG** is enabled.
* onevent: Run command on event<br/>
  usage: usage: /libcommon **onevent** _&lt;event&gt;_ _[cmd]_

  This command is available when **DEBUG** is enabled.

  This command will make it so a command will be executed whenever an
  event is fired.  Options:
  * /libcommon **onevent**
    - show registered events
  * /libcommon **onevent** _&lt;event&gt;_
    - Show what command will be executed.
  * /libcommon **onevent** _<event>_ _<command>_
    - Will schedule for _command_ to be executed.
  * /libcommon **onevent** _<event>_ **--none**
    - Will remove the given event handler

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

* trace: controls event tracing<br/>
   usage: /libcommon **trace** _[options]_

  This command is available when **DEBUG** is enabled.
  Trace will show to the user the different events that are being
  triggered on the server.  To reduce spam, events are de-duplicated.

  Sub commands:
  * /libcommon **trace**
    - Shows the current trace status
  * /libcommon **trace** **on**
    - Turns on tracing
  * /libcommon **trace** **off**
    - Turns off tracing
  * /libcommon **trace** **events** _[type|class]_
    - Show the list of the different event types and classes.  If a _type_
      or _class_ was specified, it will show the events defined for them.
  * /libcommon **trace** _&lt;event|type|class&gt;_ _[additional options]_
    - Will add the specified _event|type|class_ to the current user's
      trace session.
  * /libcommon **trace** _&lt;-event|type|class&gt;_ _[additional options]_
    - If you start the _event|type|class_ specification name with a
      **dash**, the _event|type|class_ will be removed from the current
      trace session.

* version: shows the libcommon version<br/>
   usage: /libcommon **version**

<!-- end-include -->

For use in PMScripts, an **echo** and **rem** commands are defined.

The **rem** command does nothing, so can be used as a comment.

The **echo** command, unlike the **libcommon echo** command, does not do
any variable substitutions.  It is expected that the PMScript interpreter
would handle these.

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

* libcommon.debug.command (op): Allow access to libcommon debug commands
* libcommon.echo.command: Basic echo command

<!-- end-include -->

<!-- template: gd2/mctxt.md -->

## Translations

This plugin will honour the server language configuration.  The
languages currently available are:

* English
* Spanish


You can provide your own message file by creating a file called
**messages.ini** in the plugin config directory.
Check [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/libcommon/resources/messages/)
for sample files.
Alternatively, if you have
[GrabBag](http://forums.pocketmine.net/plugins/grabbag.1060/) v2.3
installed, you can create an empty **messages.ini** using the command:

     pm dumpmsgs libcommon [lang]

<!-- end-include -->

## Changes

- 1.91.0: ?
  * New module: TPUtils
  * Added onevent sub command
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
