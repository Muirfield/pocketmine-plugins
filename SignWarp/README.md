SignWarp
========

* Summary: Warp between places using signs
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 - API 1.10.0
* OptionalPlugins: ManyWorlds
* Categories: Teleportation
* Plugin Access: Commands, Tile Entities, Items/Blocks
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/SignWarp)

Overview
--------

A very basic Plugin implementing simple _Sign_ based warps.

Basic Usage:

Place a Sign with the following text:

	[SWARP]
	x y z

Where `x`, `y` and `z` are numbers containing the target warp
coordinates.

Or for a warp between worlds:

	[WORLD]
	world_name
	x y z

Where `world_name` is the world to warp to, and *optionally* the
`x`, `y` and `z` warp location.

Documentation
-------------

This plugin implements _warps_ through the placement of _signs_.  You
need to create a sign with the text:

	[SWARP]
	x y z

`x`, `y` and `z` are integers containing the target coordinates for
this warp.

To activate a _warp_ the player must touch a sign.  That will teleport
the player to the new location described by the `x`, `y`, `z`
coordinates.

The third and four lines of the sign are ignored and can be used to
describe the _warp_.

To teleport between worlds, the sign text should look like:

	[WORLD]
	world_name
	x y z
	Players:

`world_name` is the target world to teleport to.  `x`, `y`, `z` is the
target location.  If not specified it defaults to the `spawn` world.

If dynamic updates are enabled, the fourth line can contain the text
`Players:`, which will get updated dynamically with the number of
players on that world.  Otherwise the ine is  ignored and can be
contain any descriptive text.

To help identify potential _warp_ targets, the command `xyz` is
provided.  Entering `/xyz` in-game will display the current
coordinates of the player.

### config.yml

	---
	# Example config.yml
	settings:
	  dynamic-updates: 1
	  broadcast-tp: 1
	text:
	  world:
	  - '[WORLD]'
	  - '[MUNDO]'
	  warp:
	  - '[WARP]'
	  - '[SWARP]'
	  - '[TELEPORT]'
	  players:
	  - 'Players:'
	  - 'Jugadores:'
	...

* dynamic-updates: 1 or 0  
  If enabled, signs will be updated with the number of players in a
  particular world.
* broadcast-tp: 1 or 0  
  If enabled, teleports will be broadcast to all players.
* world:  
  List of texts to use for `[WORLD]` teleport signs.
* warp:  
  List of texts to use for `[SWARP]` teleport signs.
* players:  
  List of texts to use for the `Players:` counters.

### Permission Nodes:

* signwarp.cmd.xyz - Allows the user to show current x,y,z coordinates
* signwarp.place.sign - Allow user to create warp signs
* signwarp.touch.sign - Allow user to use warp signs

Changes
-------

* 1.2.2: Bug fixes
  * Fixed errors reported by [Crash Archive](http://crash.pocketmine.net/)
* 1.2.1 : Minor updates
  * Added broadcast-tp setting.
  * Small changes on the way ManyWorlds API is used.
* 1.2.0 : Configurable texts
  * Sign texts can be configured.  Useful for localization.
* 1.1.1 : Bugfix release
  * Fixed /xyz command.
* 1.1.0 : Update release
  * Will not teleport if you are holding a sign.
  * Prevents blocks to be placed when teleporting.
  * Use ManyWorlds teleport functionality when available.
  * Added dynamic sign updates.
* 1.0.0 : First release

FAQ
---

* Q: How do I create additional worlds?
* You can use a plugin like `ManyWorlds` or modify the `worlds` secion
  in your `pocketmine.yml` file.


Copyright
=========

    SignWarp
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

128 70 128
X:-100 Y:69 Z:1072
