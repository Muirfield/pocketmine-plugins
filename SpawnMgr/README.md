<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/spawnicon.png" style="width:64px;height:64px" width="64" height="64"/>

# SpawnMgr

* Summary: More spawn related settings
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 (API:1.10.0)
* DependencyPlugins: -
* OptionalPlugins: SimpleAuth
* Categories: Admin Tools
* Plugin Access: Other Plugins, Items
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/SpawnMgr)

## Overview

**DO NOT POST QUESTION/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you have a
question/bug-report/request please use the
[Thread](http://forums.pocketmine.net/plugins/spawnmgr.1141/)
for that.  You are more likely to get a response and help that way.

Please go to
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/SpawnMgr)
for the most up-to-date documentation.

Let's you control how your players spawn on your server.

Supports:

* no explosions in spawn
* no pvp in spawn
* always spawn, spawn in world, spawn at home
* allow ops to join full servers
* allow to keep inventory when dieing
* spawn with armor
* spawn with items
* SimpleAuth nest-egg's

## Documentation

Control spawn settings in your server.

## Config notes

* **spawn-mode**: Spawn mode settings can be one of the following:
  - *default* : when joining will start at the last location.
  - *world* : when joining will always start at the last world
    spawn point.
  - *always* : when joining will always start at the default world
    spawn point.
  - *home* : when joining will start at your home location.
* **on-death-inv** allows the following values:
 - *false* : player drops all items on the spot (default)
 - *keep* : player keeps inventory
 - *clear* : player loses all inventory but nothing gets dropped
 - *perms* : Will use spawnmgr.keepinv and spawnmgr.nodrops to
   determine results

**NOTE**: The *home* *spawn-mode* requires you to have a */home*
plugin that provides with a `/home` command.  This command is executed
when the player joins.

### Configuration

Configuration is throug the `config.yml` file.
The following sections are defined:

#### main

*  settings: Tunable parameters
	*  tnt: true, allows explosion in spawn, false disallows it
	*  pvp: true, allows pvp in spawn, false disallows it
	*  reserved: number of reserved vip slots, false to disable
	*  spawn-mode: default|world|always|home
	*  on-death-inv: false|keep|clear|perms
	*  home-cmd: command to use when spawn-mode is home
	*  death-pos: Save death location
*  armor: List of armor elements
*  items: List of initial inventory
*  nest-egg: List for nest-egg


### Permission Nodes

* spawnmgr.back : allows to return to death location
* spawnmgr.receive.armor : allows to receive armor when you spawn
* spawnmgr.receive.items : allows to receive items when you spawn
* spawnmgr.receive.nestegg : allows to receive items when you register to SimpleAuth
* spawnmgr.keepinv : allow player to keep inventory
  _(Defaults to disabled)_
* spawnmgr.nodrops : player will not drop inventory on death
  _(Defaults to disabled)_
* spawnmgr.spawnmode : player will follow spawn-control setting
* spawnmgr.reserved : Players is allowed to join full servers
  (Defaults to Op)


# Changes

* 1.3.0: maintenance update
  * added /back command
  * added translation: spanish
  * fixed the reserved slots (similar to VIPslots, but different code...)
* 1.2.0:
  * Additional inventory on death options
  * Added Nest-Egg feature
  * Fixed spawn-mode in PM1.5
* 1.1.0:
  * rewrite to remove offending code.
* 1.0.0 : First public release

# Copyright

    SpawnMgr
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

* * *

