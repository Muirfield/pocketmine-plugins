Voodoo
=======

* Summary: Animate corpses (zombies)
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 - API 1.10.0
* DependencyPlugins: -
* OptionalPlugins: -
* Categories: Fun
* Plugin Access: Entities
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/dev/Voodoo)

Overview
--------

Moves spawned zombies.  Spawned zombies will approache the closest
player and attack them.

Documentation
-------------

This plugin will look in all the levels where there are players
present.  In those levels will look for loaded zombies and move them
so that they attack players.  The logic is very simple and there is no
AI.  The zombies will approach the closest player within range (32
blocks away) and attack them.  It simply follows a straight line to
the player so it will *not* avoid obstacles.


### Issues

* The movement is very jerky.  (Jumping zombies).
* Since there are no physics implemented in PocketMine, the zombies
  will fly but to the MCPE client it looks like they are jumping
  really high!
* The yaw/pitch doesn't seem to change.

Changes
-------

* 0.1.0 : First release

Copyright
---------

    Scorched
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
