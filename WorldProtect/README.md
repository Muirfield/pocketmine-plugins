<img src="https://raw.githubusercontent.com/Muirfield/pocketmine-plugins/master/Media/WorldProtect-icon.png" style="width:64px;height:64px" width="64" height="64"/>

<!-- meta:Categories = Anti-Griefing -->
<!-- meta:PluginAccess = Commands, Data Saving, World Editing -->
<!-- template: gd2/header.md -->

# WorldProtect

- Summary: protect worlds from griefers, pvp, limits and borders
- PocketMine-MP version: 1.4 (API:1.10.0), 1.5 (API:1.12.0), 1.6+php7 (API:2.0.0)
- DependencyPlugins: 
- OptionalPlugins: 
- Categories: Anti-Griefing 
- Plugin Access: Commands, Data Saving, World Editing 
- WebSite: https://github.com/Muirfield/pocketmine-plugins/tree/master/WorldProtect

<!-- end-include -->

## Overview

<!-- php: $v_forum_thread = "http://forums.pocketmine.net/threads/worldprotect.7517/"; -->
<!-- template: prologue.md -->

**DO NOT POST QUESTIONS/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you
have a question/bug-report/request please use the
[Thread](http://forums.pocketmine.net/threads/worldprotect.7517/) for
that.  You are more likely to get a response and help that way.

_NOTE:_

This documentation was last updated for version **2.3.0**.

Please go to
[github](https://github.com/Muirfield/pocketmine-plugins/tree/master/WorldProtect)
for the most up-to-date documentation.

You can also download this plugin from this [page](https://github.com/Muirfield/pocketmine-plugins/releases/tag/WorldProtect-2.3.0).

<!-- end-include -->

A full featured World protection plugin.

Features:

<!-- snippet:features -->
* Ban commands on a per world basis
* Per world game modes
* Limit the number of players in a world
* Control explosions per world
* Unbreakable blocks
* World borders
* Automatically displayed/per world MOTD
* Protect worlds from building/block breaking
* Per World PvP
* Ban specific items in a world
<!-- end-include -->

All commands require a _world_ name to be given, otherwise a default
is selected.  If in-game, the default _world_ is the world the player
is currently in.  On the console the default is the _default-world_ as
specified in the _server.properties_ file.

### Basic Usage

<!-- php:$h=4; -->
<!-- template: gd2/cmdoverview.md -->

#### Main Commands

* /motd: Shows the world's *motd* text
* /worldprotect: Main WorldProtect command

#### Sub Commands

* add: Add player to the authorized list
* bancmd|unbancmd: Prevents commands to be used in worlds
* banitem|unbanitem: Control itmes that can/cannot be used
* border: defines a border for a world
* gm: Configures per world game modes
* lock: Locks world, not even Op can use.
* ls: List info on world protection.
* max: Limits the number of players per world
* motd: Modifies the world's *motd* text.
* noexplode: Stops explosions in a world
* protect: Protects world, only certain players can build.
* pvp: Controls PvP in a world
* rm: Removes player from the authorized list
* unbreakable|breakable: Control blocks that can/cannot be broken
* unlock: Removes protection


<!-- end-include -->

### Modules

<!-- template: gd2/modovw.md -->
* gm-save-inv: Will save inventory contents when switching gamemodes.

<!-- end-include -->

## Documentation

This plugin let's you limit what happens in a world.

<!-- snippet: docs -->
It is possible to create limits in your limitless worlds.
So players are not able to go beyond a preset border.  This is
useful if you want to avoid overloading the server by
generating new Terrain.

Show a text file when players enter a world.  To explain players
what is allowed (or not allowed) in specific worlds.  For example
you could warn players when they are entering a PvP world.

This plugin protects worlds from griefers by restricing placing and breaking
blocks.  Worlds have three protection levels:

* unlock - anybody can place/break blocks
* protect - players in the _authorized_ list or, if the list is empty,
  players with **wp.cmd.protect.auth** permission can place/break
  blocks.
* lock - nobody (even *ops*) is allowed to place/break blocks.

Some items are able to modify a world by being consume (i.e. do not
need to be placed).  For example, _bonemeal_, _water or lava buckets_.
To prevent this type of griefing, you can use the **banitem**
feature.

<!-- end-include -->

### Command Reference

The following commands are available:

<!-- template: gd2/subcmds.md -->
* /motd: Shows the world's *motd* text<br/>
  usage: /motd  _[world]_
  
  Shows the *motd* text of a _world_.  This can be used to show
    rules around a world.
* /worldprotect: Main WorldProtect command<br/>
  usage: /worldprotect  _[world]_ _&lt;subcmd&gt;_ _[options]_
* add: Add player to the authorized list<br/>
  usage: /wp _[world]_ **add** _&lt;player&gt;_
* bancmd|unbancmd: Prevents commands to be used in worlds<br/>
  usage: /wp _[world]_ **bancmd|unbancmd** _[command]_
  
  If no commands are given it will show a list of banned
  commands.   Otherwise the _command_ will be added/removed
  from the ban list
  
* banitem|unbanitem: Control itmes that can/cannot be used<br/>
  usage: /wp  _[world]_ **banitem|unbanitem** _[Item-ids]_
  
  Manages which Items can or can not be used in a given world.
   You can get a list of items currently banned
   if you do not specify any _[item-ids]_.  Otherwise these are
   added or removed from the list.
  
* border: defines a border for a world<br/>
  usage: /wp  _[world]_ **border** _[range|none|x1 z1 x2 z2]_
  
  Defines a border for an otherwise infinite world.  Usage:
    - /wp _[world]_ **border**
      - will show the current borders for _[world]_.
    - /wp _[world]_ **border** _x1 z1 x2 z2_
      - define the border as the region defined by _x1,z1_ and _x2,z2_.
    - /wp _[world]_ **border** _range_
      - define the border as being _range_ blocks in `x` and `z` axis away
        from the spawn point.
    - /wp _[world]_ **border** **none**
      - Remove borders
  
* gm: Configures per world game modes<br/>
  usage: /wp _[world]_ gm _[value]_
  
  Options:
  - /wp _[world]_ **gm**
    - show current gamemode
  - /wp _[world]_ **gm** _&lt;mode&gt;_
    - Sets the world gamemode to _mode_
  - /wp _[world]_ **gm** **none**
    - Removes per world game mode
  
* lock: Locks world, not even Op can use.<br/>
  usage: /wp _[world]_ **lock**
* ls: List info on world protection.<br/>
  usage: /wp **ls** _[world]_
     - /wp **ls**
       - shows an overview of protections applied to all loaded worlds
     - /wp **ls** _[world]_
       - shows details of an specific world
* max: Limits the number of players per world<br/>
   usage : /wp _[world]_ max _[value]_
    - /wp _[world]_ **max**
      - shows the current limit
    - /wp _[world]_ **max** _value_
      - Sets limit value to _value_.
    - /wp _[world]_ **max** **0**
      - Removes world limits
  
* motd: Modifies the world's *motd* text.<br/>
  usage: /wp _[world]_ **motd** _&lt;text&gt;_
  
  Let's you modify the world's *motd* text.  The command only
  supports a single line, however you can modify the *motd* text
  by editing the **wpcfg.yml** file that is stored in the **world**
  folder.  For example:
  - [CODE]
    - motd:
      - line 1
      - line 2
      - line 3
      - line 4... etc
  - [/CODE]
* noexplode: Stops explosions in a world<br/>
  usage: /wp  _[world]_ **noexplode** _[off|world|spawn]_
    - /wp _[world]_ **noexplode** **off**
      - no-explode feature is `off`, so explosions are allowed.
    - /wp _[world]_ **noexplode** **world**
      - no explosions allowed in the whole _world_.
    - /wp _[world]_ **noexplode** **spawn**
      - no explosions allowed in the world's spawn area.
  
* protect: Protects world, only certain players can build.<br/>
  usage: /wp _[world]_ **protect**
  
  When in this mode, only players in the _authorized_ list can build.
  If there is no authorized list, it will use **wp.cmd.protect.auth**
  permission instead.
  
* pvp: Controls PvP in a world<br/>
  usage: /wp  _[world]_ **pvp** _[on|off|spawn-off]_
    - /wp _[world]_ **pvp** **off**
      - no PvP is allowed.
    - /wp _[world]_ **pvp** **on**
      - PvP is allowed
    - /wp _[world]_ **pvp** **spawn-off**
      - PvP is allowed except if inside the spawn area.
  
* rm: Removes player from the authorized list<br/>
  usage: /wp _[world]_ **rm** _&lt;player&gt;_
* unbreakable|breakable: Control blocks that can/cannot be broken<br/>
  usage: /wp  _[world]_ **breakable|unbreakable** _[block-ids]_
  
  Manages which blocks can or can not be broken in a given world.
  You can get a list of blocks currently set to **unbreakable**
  if you do not specify any _[block-ids]_.  Otherwise these are
  added or removed from the list.
  
* unlock: Removes protection<br/>
  usage: /wp _[world]_ **unlock**

<!-- end-include -->

### Module reference

<!-- php:$h=4; -->
<!-- template: gd2/mods.md -->
#### gm-save-inv

Will save inventory contents when switching gamemodes.

This is useful for when you have per world game modes so that
players going from a survival world to a creative world and back
do not loose their inventory.


<!-- end-include -->

### Configuration

Configuration is through the **config.yml** file.  The following sections
are defined.

<!-- php:$h=4; -->
<!-- template: gd2/cfg.md -->
#### features

This section you can enable/disable modules.
You do this in order to avoid conflicts between different
PocketMine-MP plugins.  It has one line per feature:

    feature: true|false

If **true** the feature is enabled.  if **false** the feature is disabled.


#### motd

*  ticks: line delay when showing multi-line motd texts.
*  auto-motd: Automatically shows motd when entering world


<!-- end-include -->
<!-- php:$h=3; -->
<!-- template: gd2/permissions.md -->

### Permission Nodes

* wp.motd: Display MOTD
* wp.cmd.all (op): Allow access to protect command
* wp.cmd.protect (op): Change protect mode
* wp.cmd.protect.auth (op): Permit place/destroy in protected worlds
* wp.cmd.border (op): Allow contfol of border functionality
* wp.cmd.pvp (op): Allow PvP controls
* wp.cmd.noexplode (op): Allow NoExplode controls
* wp.cmd.limit (op): Allow control to limit functionality
* wp.cmd.wpmotd (op): Allow editing the motd
* wp.cmd.addrm (op): Allow modifying the auth list
* wp.cmd.unbreakable (op): Modify unbreakable block list
* wp.cmd.bancmd (op): Ban/unban commands
* wp.cmd.banitem (op): Ban/unban items
* wp.banitem.exempt (disabled): it is able to use banned items
* wp.cmd.info: Show WP config info
* wp.cmd.gm (op): Allow setting a per-world gamemode
* wp.cmd.gm.exempt (disabled): Users with this permissions will ignore per world gm

<!-- end-include -->

## Issues

* World names can not contain spaces.
* When going from survival to creative then back to survival inventory
  contents get lost.

### Features Requested

* Tim // robske Büba: BanItem can ban items per {item-id}:{meta-data}

<!-- template: gd2/mctxt.md -->

## Translations

This plugin will honour the server language configuration.  The
languages currently available are:

* English
* Spanish


You can provide your own message file by creating a file called
**messages.ini** in the plugin config directory.
Check [github](https://github.com/Muirfield/pocketmine-plugins/tree/master/WorldProtect/resources/messages/)
for sample files.
Alternatively, if you have
[GrabBag](http://forums.pocketmine.net/plugins/grabbag.1060/) v2.3
installed, you can create an empty **messages.ini** using the command:

     pm dumpmsgs WorldProtect [lang]

<!-- end-include -->

# API

There is a minimal API to determine the max number of players per world:

```PHP
$this->getServer()->getPluginManager()->getPlugin("WorldProtect")->getMaxPlayers($world);
```

Where:

* $this - plugin pointer
* $world - either a world name or an instance of Level.

Returns an integer or null.

# FAQ

* Q: How do I keep my inventory so that it does not get clear when I
  switch gamemodes?
* A: Enable gm-save-inv module.

# Changes

* 2.3.0: Updated to API 2.0.0
  - Added banitem exempted permissions (@BobbyTowers)
  - Closes #47 (@SleepSpace9)
  - **THIS IS JUST A SIMPLE TAG UPDATE, NO TESTING HAS BEEN DONE**
* 2.2.0: minor Update
  - Implemented banned commands (@Tolo)
  - Documentation update
* 2.1.2: bug fix
  - Load/Unload events were not being registered (Bug reported by @GuddaJ)
* 2.1.1: minor Update
  * updated to libcommon 1.2.0dev2
     - Upgraded to ItemName module
     - permissions defined in `plugin.yml` are applied
       properly which means most sub commands are now **OP only**.
* 2.1.0: API
  * Added API to determine max players
* 2.0.3: Minor bug fix
  * Fixed bug: Configuration is not applied when reloading
* 2.0.2: Feature request
  * Feature Request(@Nifo2000): Option to control if MOTD is shown
    automatically
* 2.0.1: critical bug fix
  * Fixed a crash
  * Can now add to auth list when players are off-line
* 2.0.0: Complete re-write
  * Refactor so it is now more modular
  * Added per world gamemode and gamemode inventory save
  * Added banitem functionality
  * Added translation: Spanish
* 1.2.4: CallbackTask
  * Removed CallbackTask deprecation warnings
* 1.2.3: Suggested change
  * Simpler border setting using a single "range" number
* 1.2.2: protection overview (un-published)
  * Added an overview of protected worlds
* 1.2.1: BugFix
  * Positions are not configured correctly.
* 1.2.0: Update
  * Bugfix in sending motd text
  * Bugfixes WpProtect
  * wp ls/ld - will call ManyWorlds.  Needs ManyWorlds v1.3.2.
  * Fixed Signs Tiles being left all over...
  * Added stop PvP in spawn areas
  * Added Unbreakable blocks
  * Max players per world should now work *without* ManyWorlds.
* 1.1.1 : bugfix
  * Fixes bugs reported by [Crash Archive](http://crash.pocketmine.net/search)
* 1.1.0: no-explode
  * Added NoExplode functionality
  * Fixed stupid typo about /mw subcommands
* 1.0.0 : Initial release

<!-- php:$copyright="2015"; -->
<!-- template: gd2/gpl2.md -->
# Copyright

    WorldProtect
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

