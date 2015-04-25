WorldProtect
============

* Summary: protect worlds from griefers, pvp, limits and borders
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 - API 1.10.0
* OptionalPlugins: ManyWorlds
* Categories: World Editing and Management, Admin Tools, Anti-Griefing Tools
* Plugin Access: Commands, Manages Permission, Data Saving, World Editing
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/WorldProtect)

Overview
---------

A full featured World protection plugin.

Features:

* Protect worlds from griefers
* Per world PvP
* Create limits in your limitless worlds
* Limit the number of players in a world
* Show a text file when players enter a world
* Stops explosions from happening in a world

Basic Usage:

* /motd [level]
* /wp unprotect|unlock [level]
* /wp lock [level]
* /wp protect [level]
* /wp pvp [level] [on|off|spawn]
* /wp noexplode [level] [off|world|spawn]
* /wp border [level] [x1 z1 x2 z2|none]
* /wp max [level] [count]
* /wp add [level] *player*
* /wp rm [level] *player*
* /wp motd [level] [text]
* /wp unbreakable [level] [id id id]

Documentation
-------------

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


### Commands:

Informational:

* motd [level]  
  Show a level specific _message of the day_ type text.
* wp motd [level] _text_  
  Modify the motd text for the specified level (or the current level).
  While you can only enter a single line through this command, you can
  have a multi-line MOTD by modifying the per-world configuration file.

Protect:

* wp lock [level]  
  Place/destroy block is *not* allowed.  Not even for ops.  This is
  useful for adventure style worlds.
* wp protect [level]  
  Place/destroy block is allowed for players that have
  `wp.cmd.protect.auth` permission (by default *ops*) or players in
  the `auth` list. (See add/rm sub-commands).
* wp unlock|unprotect [level]
  Placing/destroying blocks are allowed.
* wp status [level]
  Will show the lock/protect status of a world.


Per-world PvP:

* wp pvp [level] [on|off|spawn]  
  If nothing is specified it will show the PvP status of the current
  level.  Otherwise PvP will either be activated|de-activated.  If set
  to `spawn`, PvP is turned off in the spawn area of this world.

No Explode:

* /wp noexplode [level] [off|world|spawn]
  If nothing is specified it will show the NoExplode status of the current
  level. If off, allows explosions (default).  If world, explosions are
  prevented in the whole world.  If spawn, explosions are prevented in
  the spawn area.

World borders:

* wp border [level] [x1 z1 x2 z2|none]  
  If nothing is specified it will show the current borders.  If `none`
  the current borders will be removed.  Otherwise a border will be set
  by the (x1,z1) - (x2,z2) coordinates.

Player Limits:

* wp max [level] [count]  
  If nothing is specified will show the current player limits.
  Otherwise the world limit will be set by `count`.  Set to `0` for
  limitless worlds.

Auth list:

* wp add [level] *player*
* wp rm [level] *player*

These commands add or remove players from the world's `auth` list.
Access to these commands is controlled by standard PocketMine
permission system.  When a world's `auth` list is defined, access to
these commands are restricted to the people in the `auth` list.

Unbreakable blocks:

* wp unbreakable [level] [id ...]  
  Will add the *id* to the list of unbreakable blocks for this world.
* wp breakable [level] [id ...]  
  Will remove the *id* from the list of unbreakable blocks for this world.

### Configuration

In the plugin's config.yml file you can have:

	settings:
	  player-limits: true
	  world-borders: true
	  world-protect: true
	  per-world-pvp: true
	  motd: true
	  no-explode: true
	  unbreakable: true

Control what modules are active.

### Permission Nodes:

* wp.motd - Display MOTD
* wp.cmd.all - Allow access to protect command
* wp.cmd.protect - Change protect mode
* wp.cmd.protect.auth - Permit place/destroy in protected worlds
* wp.cmd.border - Allow contfol of border functionality
* wp.cmd.pvp - Allow PvP controls
* wp.cmd.limit - Allow control to limit functionality
* wp.cmd.wpmotd - Allow editing the motd
* wp.cmd.noexplode - no explode command access
* wp.cmd.addrm - Allow modifying the auth list
* wp.cmd.unbreakable - Allow modifying the unbreakable block list

### ManyWorlds

If ManyWorlds is installed, the

    /mw ls [level]

Will show additional information.  Also, you can enter:

    /wp ls [level]

To get world details.

### Issues

* World names can not contain spaces.

Changes
-------

* ?1.2.2: protection overview
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

Copyright
---------

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
