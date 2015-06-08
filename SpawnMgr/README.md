<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/spawnicon.png" style="width:64px;height:64px" width="64" height="64"/>

SpawnMgr
=======

* Summary: Better control of how players spawn
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 - API 1.10.0
* DependencyPlugins: -
* OptionalPlugins: SimpleAuth
* Categories: Admin Tools
* Plugin Access: Other Plugins, Items
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/SpawnMgr)

Overview
--------

**PLEASE DELETE YOUR OLD CONFIGURATION FILE AS THE FORMAT HAS BEEN CHANGED**

**DO NOT POST QUESTION/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you have a
question/bug-report/request please use the
[Thread](http://forums.pocketmine.net/plugins/spawnmgr.1141/)
for that.  You are more likely to get a response and help that way.

Please go to
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/KillRate)
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

Documentation
-------------

Control spawn settings in your server.

### Configuration

Configuration is through the `config.yml` file:

~~~YAML
[CODE]
---
settings:
  # Explosions allowed in spawn area, set false to disallow
  tnt: true
  # PvP allowed in spawn area, set false to disallow
  pvp: true
  # If true, allows Ops to join full servers, set to a number to allow
  # only the specified number of extra slots (above max slots)
  reserved: false
  # spawn-mode: can be one of the following:
  # - *default* : when joining will start at the last location.
  # - *world* : when joining will always start at the last world
  #    spawn point.
  # - *always* : when joining will always start at the default world
  #     spawn point.
  # - *home* : when joining will start at your home location.
  spawn-mode: default
  # on-death-inv allows the following values:
  # - false : player drops all items on the spot (default)
  # - keep : player keeps inventory
  # - clear : player loses all inventory but nothing gets dropped
  # - perms : Will use spawnmgr.keepinv and spawnmgr.nodrops to
  #    determine results
  on-death-inv: false
  # If spawn-mode is *home*, this is the command to use to teleport
  # player to their home.  Requires an plugin that implements /home
  # functionality
  home-cmd: /home
# List of armor elements
armor:
- chain_chestplate
- leather_pants
- leather_boots
# List of initial inventory
items:
- STONE_SWORD,1
- WOOD,16
- COOKED_BEEF,5
# Nest Egg, granted to the player when they register through SimpleAuth
nest-egg:
- GOLD_INGOT,10
...
[/CODE]
~~~

**NOTE**: The *home* *spawn-mode* requires you to have a */home*
plugin that provides with a `/home` command.  This command is executed
when the player joins.

### Permission Nodes:

* spawnmgr.receive.armor: allows to receive armor when you spawn
* spawnmgr.receive.items: allows to receive items when you spawn
* spawnmgr.receive.nestegg: allows to receive items when you register
  to SimpleAuth
* spawnmgr.keepinv: allows player to keep inventory
* spawnmgr.nodrops: players with this permission do not drop items on death
* spawnmgr.spawnmode: player will follow spawn-control setting
* spawnmgr.reserved: Players is allowed to join full servers

Changes
-------
* 1.3.0: ????
  * added /back command
  * added translation: spanish
* 1.2.0:
  * Additional inventory on death options
  * Added Nest-Egg feature
  * Fixed spawn-mode in PM1.5
* 1.1.0:
  * rewrite to remove offending code.
* 1.0.0 : First public release

Copyright
---------

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
