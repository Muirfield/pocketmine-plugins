<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/WorldProtect-icon.png" style="width:64px;height:64px" width="64" height="64"/>

# WorldProtect

* Summary: protect worlds from griefers, pvp, limits and borders
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 (API:1.10.0), 1.5 (API:1.12.0)
* OptionalPlugins: n/a
* Categories: World Editing and Management, Admin Tools
* Plugin Access: Commands, Data Saving, World Editing, Manages Worlds
* WebSite: https://github.com/alejandroliu/pocketmine-plugins/tree/master/WorldProtect

## Overview

<!-- php: $v_forum_thread = "http://forums.pocketmine.net/threads/worldprotect.7517/"; -->
<!-- template: prologue.md -->

**DO NOT POST QUESTION/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you
have a question/bug-report/request please use the
[Thread](http://forums.pocketmine.net/threads/worldprotect.7517/) for
that.  You are more likely to get a response and help that way.

_NOTE:_

This documentation was last updated for version **2.1.0**.

Please go to
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/WorldProtect)
for the most up-to-date documentation.

You can also download this plugin from this [page](https://github.com/alejandroliu/pocketmine-plugins/releases/tag/WorldProtect-2.1.0).

<!-- template-end -->

A full featured World protection plugin.

Features:

* Protect worlds from griefers
* Per world PvP
* Create limits in your limitless worlds
* Limit the number of players in a world
* Show a text file when players enter a world
* Stops explosions from happening in a world
* Unbreakable blocks
* Banned Items
* Per world gamemode with inventory saving

All commands require a `world` name to be given, otherwise a default
is selected.  If in-game, the default `world` is the world the player
is currently in.  On the console the default is the `default-world` as
specified in the `server.properties` file.

### Basic Usage

* /motd : Shows the world's *motd* text
* add : Add player to the authorized list
* banitem|unbanitem : Control items that can/cannot be used
* border : defines a border for a world
* gm : Configures a world's gamemode.
* lock : Locks world, not even Op can use.
* ls : List info on world protection.
* max : Limits the number of players per world.
* motd : Modifies the world's *motd* text.
* noexplode : Stops explosions in a world
* protect : Protects world, only certain players can build.
* pvp : Controls PvP in a world
* rm : Removes player from the authorized list
* unbreakable|breakable : Control blocks that can/cannot be broken
* unlock : Removes protection

### Modules

* gm-save-inv : Will save inventory contents when switching gamemodes.

## Documentation

**NOTE: v2.0.0 is a complete rewrite.  Please test your settings
carefully when upgrading**

This plugin let's you limit what happens in a world.

It is able to:

* Protect worlds from griefers.  Only players within an auth list
  and/or certain permissions are allowed to place or destroy blocks.
* Per world PvP.  Prevents PvP matches in specific worlds.
* Create limits in your limitless worlds.  So players attempting are
  not able to work beyond a preset border.  This is useful if you want
  to avoid overloading the server by generating new Terrain.
* Limit the number of players in a world.
* Show a text file when players enter a world.  To explain players
  what is allowed (or not allowed) in specific worlds.  For example
  you could warn players when they are entering a PvP world.
* Create unbreakable blocks.

### Command Reference

The following commands are available:

* /motd  _[world]_  
  Shows the world's *motd* text  
  Shows the *motd* text of a _world_.  This can be used to show
  rules around a world.
* /wp _[world]_ **add** _&lt;player&gt;_  
  Add player to the authorized list  
* /wp  _[world]_ **banitem|unbanitem** _[Item-ids]_  
  Control items that can/cannot be used  
  Manages which Items can or can not be used in a given world.
  You can get a list of items currently banned
  if you do not specify any _[item-ids]_.  Otherwise these are
  added or removed from the list.
* /wp  _[world]_ **border** _[range|none|x1 z1 x2 z2]_  
  defines a border for a world  
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
* /wp _[world]_ gm _[value]_  
  Configures a world's gamemode.  
  - /wp _[world]_ **gm**
    - Shows the current game mode
  - /wp _[world]_ **gm** _mode_
    - Sets world game mode
  - /wp _[world]_ **gm** **none**
    - Removes per world gamemode
* /wp _[world]_ **lock**  
  Locks world, not even Op can use.  
* /wp **ls** _[world]_  
  List info on world protection.  
  - /wp **ls**
    - shows an overview of protections applied to all loaded worlds
  - /wp **ls** _[world]_
    - shows details of an specific world
* /wp _[world]_ max _[value]_  
  Limits the number of players per world.  
  - /wp _[world]_ **max**
    - shows the current limit
  - /wp _[world]_ **max** _value_
    - Sets limit value to _value_.
  - /wp _[world]_ **max** **0**
    - Removes world limits
* /wp _[world]_ **motd** _&lt;text&gt;_  
  Modifies the world's *motd* text.  
  Let's you modify the world's *motd* text.  The command only
  supports a single line, however you can modify the *motd* text
  by editing the `wpcfg.yml` file that is stored in the `world`
  folder.  For example:

      [CODE]
      motd:
      - line 1
      - line 2
      - line 3
      - line 4... etc
      [/CODE]


* /wp  _[world]_ **noexplode** _[off|world|spawn]_  
  Stops explosions in a world  
  - /wp _[world]_ **noexplode** **off**
    - no-explode feature is `off`, so explosions are allowed.
  - /wp _[world]_ **noexplode** **world**
    - no explosions allowed in the whole _world_.
  - /wp _[world]_ **noexplode** **spawn**
    - no explosions allowed in the world's spawn area.
* /wp _[world]_ **protect**  
  Protects world, only certain players can build.  
  When in this mode, only players in the _authorized_ list can build.
  If there is no authorized list, it will use `wp.cmd.protect.auth`
  permission instead.

* /wp  _[world]_ **pvp** _[on|off|spawn-off]_  
  Controls PvP in a world  
  - /wp _[world]_ **pvp** **off**
    - no PvP is allowed.
  - /wp _[world]_ **pvp** **on**
    - PvP is allowed
  - /wp _[world]_ **pvp** **spawn-off**
    - PvP is allowed except if inside the spawn area.
* /wp _[world]_ **rm** _&lt;player&gt;_  
  Removes player from the authorized list  
* /wp  _[world]_ **breakable|unbreakable** _[block-ids]_  
  Control blocks that can/cannot be broken  
  Manages which blocks can or can not be broken in a given world.
  You can get a list of blocks currently set to `unbreakable`
  if you do not specify any _[block-ids]_.  Otherwise these are
  added or removed from the list.
* /wp _[world]_ **unlock**  
  Removes protection  


### Module reference

#### gm-save-inv

Will save inventory contents when switching gamemodes.

This is useful
for when you have per world game modes so that players going from a
survival world to a creative world and back do not loose their
inventory.


### Configuration

Configuration is through the `config.yml` file.
The following sections are defined:

#### features


This section you can enable/disable modules.
You do this in order to avoid conflicts between different
PocketMine-MP plugins.  It has one line per feature:

   feature: true|false

If `true` the feature is enabled.  if `false` the feature is disabled.

#### motd

*  ticks: line delay when showing multi-line motd texts.
*  auto-motd: Automatically shows motd when entering world


### Permission Nodes

* wp.motd : Display MOTD
* wp.cmd.all : Allow access to protect command
  (Defaults to Op)
* wp.cmd.protect : Change protect mode
  (Defaults to Op)
* wp.cmd.protect.auth : Permit place/destroy in protected worlds
  (Defaults to Op)
* wp.cmd.border : Allow contfol of border functionality
  (Defaults to Op)
* wp.cmd.pvp : Allow PvP controls
  (Defaults to Op)
* wp.cmd.noexplode : Allow NoExplode controls
  (Defaults to Op)
* wp.cmd.limit : Allow control to limit functionality
  (Defaults to Op)
* wp.cmd.wpmotd : Allow editing the motd
  (Defaults to Op)
* wp.cmd.addrm : Allow modifying the auth list
  (Defaults to Op)
* wp.cmd.unbreakable : Modify unbreakable block list
  (Defaults to Op)
* wp.cmd.banitem : Ban/unban items
  (Defaults to Op)
* wp.cmd.info : Show WP config info
* wp.cmd.gm : Allow setting a per-world gamemode
  (Defaults to Op)
* wp.cmd.gm.exempt : Users with this permissions will ignore per world gm
  _(Defaults to disabled)_


## Issues

* World names can not contain spaces.
* When going from survival to creative then back to survival inventory
  contents get lost.

## Translations

This plugin will honour the server language configuration.  The
languages currently available are:

* English
* Spanish

You can provide your own message file by creating a file called
`messages.ini` in the pluginc config directory.  Check
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/WorldProtect)
for sample files.

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

* 2.1.1: minor Update
  * updated to libcommon 1.1 ItemName module.
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

