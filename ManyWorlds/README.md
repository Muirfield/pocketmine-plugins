ManyWorlds
==========

* Summary: Basic commands for MultiWorld functionality
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 - API 1.10.0
* OptionalPlugins: n/a
* Categories: World Editing and Management, Teleportation
* Plugin Access: Commands, Manages Worlds
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/ManyWorlds)

Overview
---------

A very basic plugin implementing MultiWorld functionality

Features:

* teleport
* load/unload
* create
* world info

Basic Usage:

* /mw tp *level* [player]
* /mw create *level* [seed [flat|normal [preset]]]
* /mw load *level*
* /mw unload [-f] *level*
* /mw ls [level]
* /mw lvdat [level] [attr=value]
* /mw fixname [level]

Documentation
-------------

This plugin is a world manager that allows you to generate and load
worlds as well as teleport between worlds.

The teleport itself has a number of workarounds to deal with
Client-Server glitches.  Essentially, it works for me.

### Commands:

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

Format hacking:

* mw fixname *level*  
  Fixes `level.dat` files so that the name matches the folder name.
* mw lvdat *attr=value* *attr=value*  
  Change directly some `level.dat` values/attributes.  Supported
  attributes:
  * spawn=x,y,z : Sets spawn point
  * seed=randomseed : seed used for terrain generation
  * name=string : Level name
  * generator=flat|normal : Terrain generator
  * preset=string : Presets string.

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

* `broadcast-tp`: Controls broadcast message that somebody teleported.

### API

To use the teleport provided by ManyWorlds, you can use this code:

	if (($mw = $this->getServer()->getPluginManager()->getPlugin("ManyWorlds")) != null) {
	    $mw->mwtp($player,$pos);
	} else {
	    $player->teleport($pos);
	}

You need to do this in order for WorldProtect limits to work.

### Permission Nodes:

* mw.cmd.tp - Allows users to travel to other worlds
* mw.cmd.tp.others - Allows users to make others travel to other worlds
* mw.cmd.world.create - Allows users to create worlds
* mw.cmd.world.load - Allows users to load worlds
* mw.cmd.lvdat - Manipulate level data

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

Changes
-------

* 1.3.4: Updates for PM1.5
  * Removed CallbackTask deprecation warnings
* 1.3.3: Updates for PM1.5
  * Minor cosmetic changes.
  * Changes default canUnload to true if running on PM1.5
  * Fixed use of WorldProtect limits + TeleportMgr
* 1.3.2: API update
  * Allow WorldProtect to call our commands.
  * Simplified API and updated its documentation
* 1.3.1:
  * Fixed a bug around not show who was teleported for 3rd party
    teleport
  * Fixed all bugs reported by [Crash Archive](http://crash.pocketmine.net/)
* 1.3.0: Level.dat hacking.
  * Added `lvdat` command to change `level.dat` settings.
  * Added `fixname` command to fix `levelName` vs. `foldername`
    mismatches.
  * Fixed critical error for teleport!
* 1.2.0: Clean-ups
  * Added a setting to control if to broadcast when people teleport.
  * Removed per-level `motd.txt`.
  * Code clean-up
  * Teleport functionality encapsulated in TeleportManager.
  * Added workaround to remove TileEntities that linger on when teleporting.
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
