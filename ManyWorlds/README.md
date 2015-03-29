ManyWorlds
==========

* Summary: A full featured MultiWorld suite
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 - API 1.10.0
* OptionalPlugins: n/a
* Categories: World Editing and Management, Teleportation
* Plugin Access: Commands, Manages Worlds
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/ManyWorlds)

Overview
---------

A full featured MultiWorld suite

Features:

* Basic Multiworld Management:
  * teleport
  * load/unload
  * create
  * world info
* Per world MOTD
* World-protect Anti-griefing
* PvP worlds
* World borders
* Player Limits on worlds

Basic Usage:

* /motd
* /mw tp *level* [player]
* /mw create *level* [seed [flat|normal [preset]]]
* /mw load *level*
* /mw unload [-f] *level*
* /mw ls [level]
* /mw motd *level* [line] [text]
* /mw open [level]
* /mw lock [level]
* /mw protect [level]
* /mw pvp [level]
* /mw peace [level]
* /mw unprotect [level]
* /mw add [level] *player*
* /mw rm [level] *player*
* /mw border [level] *x1 z1 x2 z2*
* /mw no-border [level]
* /mw border-off [level]
* /mw border-on [level]
* /mw limits

Documentation
-------------

This plugin is a world manager that allows you to generate and load
worlds as well as teleport between worlds.

It also implements a simple per-world _message of the day_ that is
shown automatically when a player teleports to a new world.  This is
stored in the `worlds/level` subdirectory under `motd.txt`.  You can
edit this file directly using something like `notepad` or write the
text using the `motd` sub-command.

The teleport itself has a number of workarounds to deal with
Client-Server glitches.  Essentially, it works for me.

### Commands:

Informational:

* motd  
  Show a level specific _message of the day_ type message.

Teleporting:

* mw tp *level* [player]  
  Teleports `player` to `level`.  If no `player` is specified, it
  teleports the current user.
* mw ls [level]  
  If `level` is not specified, it will list all available worlds.  If
  `level` is specified, it will provide details on that `level`.

World management:

* mw create *level* [seed] [flat|normal] [preset]  
  Creates a world named `level`.  You can optionally specify a `seed`
  as number, the generator (`flat` or `normal`) and a `preset` string.
* mw load *level*  
  Loads `level` directly.  If you use `--all` for the `level` name, it
  will load all worlds.
* mw unload *level*
  Unloads `level`.
* mw motd *level* [line text]  
  If only *level* is specified, it will show the `motd` message for
  that *level*.  If `line` and `text` is specified (`text` can be
  empty, however), it will modify that line of the `motd` message.

World Protect:

* mw open [level]  
  Placing/destroying blocks are allowed.
* mw lock [level]  
  Place/destroy block is *not* allowed.  Not even for ops.
* mw protect [level]  
  Place/destroy block is allowed for players in the `auth`
  list.  If the `auth` list is empty, players with
  `mw.world.protect.basic` permission (by default ops).
* mw add [level] *player*  
  *player* is added to the `auth` list.
* mw rm [level] *player*  
  *player* is removed from the `auth` list.

Also changing world protect modes is only allowed for users in the `auth`
list or at the console.

Per-world PvP:

* mw pvp [level]  
  pvp is allowed.
* mw nopvp [level]  
  pvp is *not* allowed.


World Borders:

* mw border [level] *x1 z1 x2 z2*  
  Creates a border for [level] bounded by the x1,z1 to x2,z2 coordinates.
* mw no-border [level]  
  Removes any borders for [level].
* mw border-off [level]  
  Temporarily disables border control for you.
* /mw border-on [level]  
  Restores border controls for you.

Player Limits:

* mw limits [level] [value]  
  Sets the max number of players allow in [level].  Set to `0` or `-1`
  to remove limits.

### Examples:

Create a new normal world:

    /mw create overworld 711 normal

Create a new flat world:

    /mw create flatland 404 flat 2;7,59x1,3x3,2;1;

Teleport to this newly created world:

    /mw tp flatland

Teleport a player to another world:

    /mw tp flatland joshua

### Configuration

In the plugin's config.yml file you can have:

	settings:
	  broadcast-tp: true
	  player-limits: true
	  world-border: true
	  world-protect: true
	  pvp-worlds: true

* `broadcast-tp`: Controls broadcast message that somebody teleported.
* `player-limits`: Enables the player limits.  Max number of players
  in a world.
* `world-border`: Enables creating a border around a world.
* `world-protect`: Protects worlds from griefing.
* `pvp-worlds`: Controls if PvP is allowed in specific worlds.

### Permission Nodes:

* mw.cmd.tp - Allows users to travel to other worlds
* mw.cmd.tp.others - Allows users to make others travel to other worlds
* mw.cmd.world.create - Allows users to create worlds
* mw.cmd.world.load - Allows users to load worlds
* mw.cmd.world.motd - Allow editing motd text.
* mw.cmd.world.protect - Allow access to protect functionality
* mw.world.protect.basic - for worlds without auth lists controls who
  can place/destroy blocks.
* mw.cmd.world.pvp - access to per world pvp functionality
* mw.cmd.world.border - access to border functions
* mw.cmd.world.limit - allow access to limit functionality

FAQ
---

* Q: How do I create a `FLAT` world?
* A: You must be using PocketMine-MP v1.4.1.  Set the `generator` to
  `flat`.
* Q: How do I load multiple worlds on start-up?
  A: That functionality is provided by PocketMine-MP core by default.
  In the `pocketmine.yml` file there is a `worlds` section where you
  can define which worlds to load on start-up.

Issues
------

* World names can not contain spaces.
* Unloading a world may cause a core dump.
* Placing a sign on worlds that do not allow placing blocks will crash
  the MCPE client.

Changes
-------

* 1.2.0: Extended functionality
  * World Protect/PvP restrictions
  * World Border
  * World Player Limit
  * Added a setting to control if to broadcast when people teleport.
  * Code clean-up
  * Teleport functionality encapsulated in TeleportManager.
* 1.1.0:
  * Show better help messages.
  * Added world unload.  May cause core dumps.
  * `ls` sub-command improvements:
    * paginated output
    * show number of players, autoloading and default status.
  * Per-level `motd.txt`.  Worlds can contain a small `motd.txt` text
    file that will be displayed when the player enters or when they
    type the `/motd` command.
  * Workaround teleport glitches, with a minimal API to let other
    plugins use this.
  * Added `loadall` functionality.
  * BugFix: given an invalid player name to teleport would crash server.
* 1.0.0 : Initial release

Copyright
---------

    ManyWorlds
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
