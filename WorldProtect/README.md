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

Basic Usage:

* /motd [level]
* /wp unprotect|unlock [level]
* /wp lock [level]
* /wp protect [level]
* /wp pvp [level] [on|off]
* /wp border [level] [x1 z1 x2 z2|none]
* /wp max [level] [count]
* /wp add [level] *player*
* /wp rm [level] *player*
* /wp motd [level] [text]

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

* wp pvp [level] [on|off]  
  If nothing is specified it will show the PvP status of the current
  level.  Otherwise PvP will either be activated|de-activated.

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

### Configuration

In the plugin's config.yml file you can have:

	settings:
	  player-limits: true
	  world-borders: true
	  world-protect: true
	  per-world-pvp: true
	  motd: true

* player-limits: Enables the per world player limits
* world-borders: Enables the world border module
* world-protect: Enables the anti-griefing module
* per-world-pvp: Enables per world PvP functionality
* motd : Enable per world MOTD text

### Permission Nodes:

* wp.motd - Display MOTD
* wp.cmd.all - Allow access to protect command
* wp.cmd.protect - Change protect mode
* wp.cmd.protect.auth - Permit place/destroy in protected worlds
* wp.cmd.border - Allow contfol of border functionality
* wp.cmd.pvp - Allow PvP controls
* wp.cmd.limit - Allow control to limit functionality
* wp.cmd.wpmotd - Allow editing the motd

### ManyWorlds

The World Limit functionality requires the use of ManyWorlds teleport.
Also, if ManyWorlds is installed, the

    /mw ls [level]

Will show additional information.

Issues
------

* World names can not contain spaces.
* Placing a sign on worlds that do not allow placing blocks will crash
  the MCPE client.

Changes
-------

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
