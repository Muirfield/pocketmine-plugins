Scorched
=======

* Summary: The Mother of All Mini-Games
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 - API 1.10.0
* DependencyPlugins: -
* OptionalPlugins: -
* Categories: Fun
* Plugin Access: Commands, Entities, Item/Blocks
* WebSite: [github](https://github.com/Muirfield/pocketmine-plugins/tree/master/Scorched)

Overview
--------

Let's you play a simulation of the old PC game
[Scorched Earth](http://en.wikipedia.org/wiki/Scorched_Earth_%28video_game%29).

Commands:

* *rpg*  [fuse speed|short|long|fast]
* *fire* [fuse speed|short|long|fast]
* *dumdum* [yield magic|off]
* *akira* [yield=##] [magic] [delay=ticks]

Documentation
-------------

*WARNING: This game is very destructive to world maps.*

Let's you play a game similar to
[Scorched Earth](http://en.wikipedia.org/wiki/Scorched_Earth_%28video_game%29).

### RPGs

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

### Exploding Arrows

You need a bow and arrows.  Equip the bow and enter the command:

	/dumdum

Activates exploding arrows.  Arrows will explode on impact.
You can change the explosion characteristics:

	/dumdum yield magic

The `yield` controls how large the explosion is going to be.  If you
use `magic` the explosion will kill entities/players but will not
cause physical damage to the world.

### Mines

To place a mine you need to stack a TNT block on top of a Redstone
Wire Block.  The RedStone Wire acts as a detonator/sensor.  You
can place an additional block on top of this contraption and it would
still work.  This is to hide the mine.

When a player passes on top of the mine, it will explode.

Entities may moving on top of the mine may also explode as long as the
`EntityMotionEvent` is fired.  This is not always the case, depending
on the `EntityManager` that you are using.

### Akira

Will generate an explosion out of nothing.  Do not use this lightly.

### Configuration

	presets:
	  short: [ 30, 0.5 ]
	  long: [ 80, 1.0 ]
	  fast: [ 20, 1.0 ]
	settings:
	  failure: 385
	  rate: 0.5
	  usage: 5
	  max-speed: 4.0
	  min-speed: 0.5
	  max-fuse: 120
	  min-fuse: 10
	  max-yield: 5
	  default-yield: 3
	  default-magic: false
	  forced-magic: false
	  no-magic: false
	  rpg-yield: 4
	  rpg-magic: false
	  rpg-noexplode: 0.10
	  mines: true
	mines:
	  block1: 46
	  block2: 247
	  yield: 5
	  magic: false

* `presets` contains config values for the rpg command.
* `failure` is the max damage level for the bow
* `rate` is the chance that the bow will fail
* `usage` is the bow wear and tear
* `max-speed` is the max speed configurable
* `min-speed` is the min speed configurable
* `max-fuse` is the max fuse configurable
* `min-fuse` is the min fuse configurable
* `default-yield` default yield configuration for exploding arrows
* `max-yield` max yield for exploding arrows.  Large yields cause lag!
* `default-magic`, arrows are magic by default
* `forced-magic`, arrows are always magical
* `no-magic`, arrows can not be magical
* `rpg-yield`, explosion yield for grenades
* `rpg-magic`, if true, rpgs will be magic.
* `rpg-noexplode`, probability that the RPG will fail to explode.
* `mines`, enables the mines functionality
  * `block1` : The mine block (TNT)
  * `block2` : The detonator block (RedStone Wire)
  * `yield` : Force of the explosion
  * `magic` : Magical mine

### Permission Nodes:

* scorched.cmd.fire - access to rpgs
* scorched.cmd.dumdums - access to exploding arrows
* scorched.cmd.akira - Create an explosion!

Todo
----
* Shotgun : shoots multiple arrows in one go
* Fire arrows ... incendiary arrows

Changes
-------

* 1.5.0 : Updated for API 2.0
* 1.4.1 : CallbackTask
  Removed CallbackTask deprecation message.
* 1.4.0 :
  * Added /akira.
  * Make it so Mine and Arrow explosions can be caught by Anti-Grief
    plugins.
* 1.3.0 : Dum Dums
  * Adds exploding arrows
  * Configure RPG yield and magic, also chance that RPG will not
    explode
  * Added mines
* 1.2.0 : Fun and games
  * Bows suffer wear and tear... the more damaged the bow, the higher
    the risk that it will misfired (exploding in your face!)
  * Configurable stuff
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
