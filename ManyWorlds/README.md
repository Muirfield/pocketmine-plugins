<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/ManyWorlds-icon.png" style="width:64px;height:64px" width="64" height="64"/>

# ManyWorlds

* Summary: Manage Multiple Worlds
* Dependency Plugins: n/a
* PocketMine-MP version: 1.6+php7 (API:2.0.0)
* OptionalPlugins: n/a
* Categories: Admin Tools, Teleportation
* Plugin Access: Commands, Manages Worlds
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/ManyWorlds)

## Overview

**DO NOT POST QUESTION/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you have a
question/bug-report/request please use the
[Thread](http://forums.pocketmine.net/threads/manyworlds.7277/) for
that.  You are more likely to get a response and help that way.

Please go to
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/ManyWorlds)
for the most up-to-date documentation.

Full feature set of commands to manage multiple worlds.

Features:

* teleport
* load/unload
* create
* world info
* edit level.dat

### Basic Usage

* create : Creates a new world
* default : Sets the default world
* fixname : fixes name mismatches
* generators : List available world generators
* load : Loads a world
* ls : Provide world information
* lvdat : Show/Modify level.dat variables
* tp : Teleport to another world
* unload : Unloads world

## Documentation

This plugin is a world manager that allows you to generate and load
worlds as well as teleport between worlds.

### Command Reference

The following commands are available:

* /mw **create** _&lt;world&gt;_ _[seed]_ _[generator]_ _[preset]_  
  Creates a new world  

 Creates a world named _world_.  You can optionally specify a _seed_
 as number, the generator (_flat_ or _normal_) and a _preset_ string.

* /mw **default** _&lt;world&gt;_  
  Sets the default world  

  Teleports you to another world.  If _player_ is specified, that
  player will be teleported.
* /mw **fixname** _&lt;world&gt;_  
  fixes name mismatches  

  Fixes a world's **level.dat** file so that the name matches the
  folder name.
* /mw **generators**  
  List available world generators  

  List registered world generators.
* /mw **load** _&lt;world&gt;_  
  Loads a world  

  Loads _world_ directly.  Use _--all_ to load **all** worlds.

* /mw **ls** _[world]_  
  Provide world information  

  If _world_ is not specified, it will list available worlds.
  Otherwise, details for _world_ will be provided.
* /mw **lvdat** _&lt;world&gt;_ _[attr=value]_  
  Show/Modify level.dat variables  

  Change directly some **level.dat** values/attributes.  Supported
  attributes:
  - spawn=x,y,z : Sets spawn point
  - seed=randomseed : seed used for terrain generation
  - name=string : Level name
  - generator=flat|normal : Terrain generator
  - preset=string : Presets string.

* /mw **tp** _[player]_ _&lt;world&gt;_  
  Teleport to another world  

  Teleports you to another world.  If _player_ is specified, that
  player will be teleported.
* /mw **unload** _[-f]_  _&lt;world&gt;_  
  Unloads world  

  Unloads _world_.  Use _-f_ to force unloads.


### Permission Nodes

* mw.cmds : Allow all the ManyWorlds functionality
* mw.cmd.tp : Allows users to travel to other worlds
  (Defaults to Op)
* mw.cmd.tp.others : Allows users to make others travel to other worlds
  (Defaults to Op)
* mw.cmd.ls : Allows users to list worlds
  (Defaults to Op)
* mw.cmd.world.create : Allows users to create worlds
  (Defaults to Op)
* mw.cmd.world.load : Allows users to load worlds
  (Defaults to Op)
* mw.cmd.lvdat : Manipulate level.dat
  (Defaults to Op)
* mw.cmd.default : Changes default world
  (Defaults to Op)


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

This plugin will follow the server language configuration.  The
languages currently available are:

* English
* Spanish

You can provide your own message file by creating a file called
`messages.ini` in the pluginc config directory.  Check
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/ManyWorlds/resources/messages/)
for sample files.

## Issues

* New world names can not contain spaces.

## FAQ

* Q: How do I create a `FLAT` world?
* A: You must be using PocketMine-MP v1.4.1.  Set the `generator` to
  `flat`.
* Q: How do I load multiple worlds on start-up?
* A: That functionality is provided by PocketMine-MP core by default.
  In the `pocketmine.yml` file there is a `worlds` section where you
  can define which worlds to load on start-up.  Examples:

      [CODE]

      # pocketmine.yml
      worlds:
         world1: []
         world2: []
      [/CODE]

  This will automatically load worlds: "world1" and "world2" on startup.

# Changes

* 2.1.0: Updating to new API
* 2.0.3: Bugfix update
  * Fixes bug reported by @thebigsmileXD
* 2.0.2: Bug fix
  * Updated libcommon to 1.2.0dev1
    * This fixes a bug reported by @SoyPro. (#23)
    * Note this means that permissions defined in `plugin.yml` are applied
      properly which means all **ManyWorlds** sub commands are **OP only**.
* 2.0.1: Bug fix
  * Changed command to manyworlds and mw is an alias.  This is to
    prevent possible name collisions.
  * Completed Spanish translation.
  * Fixed crash (reported by @reyak)
* 2.0.0: Modularization
  * Re-written for modularity
  * teleport manager API deprecated
  * Added `default` command to change the default level.
  * New `genlist` for list of generators
  * tp command changed to more natural English.
  * Translation: Spanish
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

