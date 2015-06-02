<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/ManyWorlds-icon.png" style="width:64px;height:64px" width="64" height="64"/>

# ManyWorlds

* Summary: Full Suite for MultiWorld functionality
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 - API 1.10.0
* OptionalPlugins: n/a
* Categories: World Editing and Management, Teleportation
* Plugin Access: Commands, Manages Worlds
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/ManyWorlds)

## Overview

Full feature set of commands to manage multiple worlds.

Features:

* teleport
* load/unload
* create
* world info
* edit level.dat

### Basic Usage

* /mw tp *level* [player]
* /mw create *level* [seed [flat|normal [preset]]]
* /mw load *level*
* /mw unload [-f] *level*
* /mw ls [level]
* /mw lvdat [level] [attr=value]
* /mw fixname [level]

## Documentation

This plugin is a world manager that allows you to generate and load
worlds as well as teleport between worlds.

### Commands Reference

The following commands are available:

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

### Permission Nodes


## Examples

Create a new normal world:

    /mw create overworld 711 normal

Create a new flat world:

    /mw create flatland 404 flat 2;7,59x1,3x3,2;1;

Teleport to this newly created world:

    /mw tp flatland

Teleport a player to another world:

    /mw tp joshua flatland


## Translations

This plugin will honour the server language configuration.  The
languages currently available are:

* English
* Spanish

You can provide your own message file by creating a file called
`messages.ini` in the pluginc config directory.  Check
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/ManyWorlds/resources/messages/)
for sample files.

## FAQ

* Q: How do I create a `FLAT` world?
* A: You must be using PocketMine-MP v1.4.1.  Set the `generator` to
  `flat`.
* Q: How do I load multiple worlds on start-up?
  A: That functionality is provided by PocketMine-MP core by default.
  In the `pocketmine.yml` file there is a `worlds` section where you
  can define which worlds to load on start-up.

## Issues

* World names can not contain spaces.

# Changes

* 2.0.0: Modularization
  * Re-written for modularity
  * teleport manager API deprecated
  * Added `default` command to change the default level.
  * New `genlist` for list of generators
  * tp command changed to more natural English.
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
