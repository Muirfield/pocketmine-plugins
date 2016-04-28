<img src="https://raw.githubusercontent.com/Muirfield/pocketmine-plugins/master/Media/emptyworld-icon.png" style="width:64px;height:64px" width="64" height="64"/>

# NAME

* Summary: An empty world generator
* Dependency Plugins: n/a
* PocketMine-MP version: 1.6+php7 (API:2.0.0)
* DependencyPlugins: -
* OptionalPlugins: -
* Categories: World Generator
* Plugin Access: World Editing
* WebSite: https://github.com/Muirfield/pocketmine-plugins/tree/master/EmptyWorld

## Overview

<!-- php: $v_forum_thread = "http://forums.pocketmine.net/plugins/emptyworld.1248/"; -->
<!-- template: prologue.md -->

**DO NOT POST QUESTIONS/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you
have a question/bug-report/request please use the
[Thread](http://forums.pocketmine.net/plugins/emptyworld.1248/) for
that.  You are more likely to get a response and help that way.

_NOTE:_

This documentation was last updated for version **1.1.0**.

Please go to
[github](https://github.com/Muirfield/pocketmine-plugins/tree/master/EmptyWorld)
for the most up-to-date documentation.

You can also download this plugin from this [page](https://github.com/Muirfield/pocketmine-plugins/releases/tag/EmptyWorld-1.1.0).

<!-- template-end -->

This is a simple WorldGenerator plugin that let's you create completely
empty worlds (except for a small spawn area).  This is useful for generating
lobbies or skywars maps.

Configuration is through the _presets_ line.  For example:

```
[CODE]
preset: radius=10,block=1
[/CODE]
```

The following preset strings are recognized:

* radius=nn
  * Defines the size of the spawn area.  Defaults to 10 block radius.
* block=nn
  * block id for the spawn area.  Defaults to 1 (stone).
* floorlevel=nnn
  * A number between 1 and 128.  The y position of the spawn area.
* biome=nn
  * The biome to use.  Defaults to 1 (plains)
* basefloor=nn
  * block id for the base floor.  This is a block generated at y=0, the
    bottom of the world.  It defaults to 0 (Air) which means that you
    fall to the void.

## FAQ

* Q: Where do you configure the preset line?
* A: This can be done either through [ManyWorlds](http://forums.pocketmine.net/plugins/manyworlds.1042/)
  or through **pocketmine.yml**
  * If using [ManyWorlds](http://forums.pocketmine.net/plugins/manyworlds.1042/)
    use the command:
    * /mw create <world> [seed] [generator] **[preset]**
  * If using **pocketmine.yml**, find the _worlds:_ section, use:
    * generator: emptyworld:[preset]

## Changes

* 1.1.0: Updated for API 2.0
  - Fixes #64 (Reported by @thebigsmileXD)
* 1.0.0: First release

## Copyright

    EmptyWorld
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

