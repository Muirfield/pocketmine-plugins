Scorched
=======

* Summary: The Mother of All Mini-Games
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 - API 1.10.0
* DependencyPlugins: -
* OptionalPlugins: -
* Categories: General
* Plugin Access: Commands
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/Scorched)

Overview
--------

Let's you play a simulation of the old PC game
[Scorched Earth](http://en.wikipedia.org/wiki/Scorched_Earth_%28video_game%29).

Commands:

* *rpg* _[fuse speed|short|long|fast]_
* *fire* _[fuse speed|short|long|fast]_

Documentation
-------------

*WARNING: This game is very destructive to world maps.*

Let's you play a game similar to
[Scorched Earth](http://en.wikipedia.org/wiki/Scorched_Earth_%28video_game%29).

You need to have a bow, at least one arrow and as many TNT's as you
can muster.

Equip the bow.  And then enter the command:

	/rpg fast

Activates the RPG with default settings.  Now start shooting your
bow.  Instead of arrows, you will find Primed TNTs.

With this you can play a
[Scorched Earth](http://en.wikipedia.org/wiki/Scorched_Earth_%28video_game%29).
like game.

Looking up or down controls the angle of elevation (thus the distance fired).

You can tweak the grenade by optionally specifying an initial _fuse_
and a _speed_ setting.

The game is played with two or more players in different locations in
the map, and they trade shots until only one remains.

The _fuse_ is the time (in ticks) that the grenade will explode.
Be careful of using very short fuses.  The _speed_ is the initial
speed of the grenade.  Faster means the TNT will travel farther.

### Presets:

* short: fuse=30, speed=0.5
* long: fuse=80, speed=1.0
* fast: fuse=20, speed=1.0

### Permission Nodes:

* scorched.cmd.fire - access to commands.

Changes
-------

* 1.1.0 : Playability improvements
* 1.0.0 : First release

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
