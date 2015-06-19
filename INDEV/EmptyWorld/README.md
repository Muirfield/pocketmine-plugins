<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/emptyworld-icon.png" style="width:64px;height:64px" width="64" height="64"/>

# NAME

* Summary: An empty world generator
* Dependency Plugins: n/a
* PocketMine-MP version: 1.5 (API:1.12.0)
* DependencyPlugins: -
* OptionalPlugins: -
* Categories: -
* Plugin Access: -
* WebSite: https://github.com/alejandroliu/pocketmine-plugins/tree/master/INDEV/EmptyWorld

## Overview

<!-- //php: $v_forum_thread = "http://forums.pocketmine.net/threads/xxxxxxxxxxxxxxxx"; -->
<!-- template: prologue.md -->
<!-- Add the line: -->
<!-- php: $v_forum_thread = "http://forums.pocketmine.net/threads/XXXX"; -->


**NOTE:**
This documentation was last updated for version **1.0.0**.
Please go to
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/INDEV/EmptyWorld)
for the most up-to-date documentation.

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

## Changes

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

