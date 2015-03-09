ManyWorlds
==========

* Summary: Basic commands for MultiWorld functionality
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 - API 1.10.0
* OptionalPlugins: n/a
* Categories: World Editing and Management, Teleportation
* Plugin Access: Commands, Manages Worlds
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/ManyWorlds)


A very basic Plugin implementing Multiworld functionality.

Basic Usage:

* /mw tp *level* [player]
* /mw create *level* [seed [flat|normal [preset]]]
* /mw load *level*
* /mw ls [level]

Documentation
-------------

This plugin is a world manager that allows you to generate and load
worlds as well as teleport between worlds.

### Commands:

* mw tp *level* [player]  
  Teleports `player` to `level`.  If no `player` is specified, it
  teleports the current user.
* mw create *level* [seed] [flat|normal] [preset]  
  Creates a world named `level`.  You can optionally specify a `seed`
  as number, the generator (`flat` or `normal`) and a `preset` string.
* mw load *level*  
  Loads `level` directly.
* mw ls [level]  
  If `level` is not specified, it will list all available worlds.  If
  `level` is specified, it will provide details on that `level`.

### Permission Nodes:

* mw.cmd.tp - Allows users to travel to other worlds
* mw.cmd.tp.others - Allows users to make others travel to other worlds
* mw.cmd.world.create - Allows users to create worlds
* mw.cmd.world.load - Allows users to load worlds

FAQ
---

* Q: Creating a world using `generator` doesn't work.
* A: PocketMine-MP has a bug in the `Server->generateLevel` method
  where specifying a `generator` is called incorrectly.


Changes
-------

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

