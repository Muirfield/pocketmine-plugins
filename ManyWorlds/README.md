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

A very basic Plugin implementing Multiworld functionality.

Basic Usage:

* /mw tp *level* [player]
* /mw create *level* [seed [flat|normal [preset]]]
* /mw load *level*
* /mw unload [-f] *level*
* /mw ls [level]
* /mw motd *level* [line] [text]
* /motd

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

* motd  
  Show a level specific _message of the day_ type message.
* mw tp *level* [player]  
  Teleports `player` to `level`.  If no `player` is specified, it
  teleports the current user.
* mw create *level* [seed] [flat|normal] [preset]  
  Creates a world named `level`.  You can optionally specify a `seed`
  as number, the generator (`flat` or `normal`) and a `preset` string.
* mw load *level*  
  Loads `level` directly.  If you use `--all` for the `level` name, it
  will load all worlds.
* mw unload *level*
  Unloads `level`.
* mw ls [level]  
  If `level` is not specified, it will list all available worlds.  If
  `level` is specified, it will provide details on that `level`.
* mw motd *level* [line text]  
  If only *level* is specified, it will show the `motd` message for
  that *level*.  If `line` and `text` is specified (`text` can be
  empty, however), it will modify that line of the `motd` message.


### Permission Nodes:

* mw.cmd.tp - Allows users to travel to other worlds
* mw.cmd.tp.others - Allows users to make others travel to other worlds
* mw.cmd.world.create - Allows users to create worlds
* mw.cmd.world.load - Allows users to load worlds
* mw.cmd.world.motd - Allow editing motd text.

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

Changes
-------

* 1.2.0: ??
  * nothing yet
* 1.1.0:
  * Show better help messages.
  * Added world unload.  May cause core dumps.
  * `ls` sub-command imporvements:
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
