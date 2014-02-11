# NotSoFlat

* * *

    NotSoFlat 0.2
    Copyright (C) 2013 Alejandro Liu  
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

This is a simple PocketMine Terrain Generator.  It is based on the
Superflat Generator, but mixes in the 
[Diamond-Square](http://en.wikipedia.org/wiki/Diamond-square_algorithm)
algorithm to generate decent looking terrain.

# Usage

Copy to your plugins directory to install.  Use `simpleworlds` to
generate new worlds.

# Configuration

This is configured using the same _presets_ string as in `Superflat`
block layer configuration.

Presets string:

       version ; blocks ; biome ; options

It recognises the following options from superflat:

- spawn
- decoration

In addition the following options are defined:

## dsq

- min : minimum height of terrain
- max : maximum height of terrain
- water : water level
- off : how quickly terrain will vary

# Changes

* 0.1 : Initial release
* 0.2 : Updates
  - Updated API level
  - Misc typos/bug-fixes
  - Fixed tree generation
  - tweaked defaults a bit

# TODO

- Add code to modify topsoil according to height.
  - In pickBlock if $y == $h then we call a special topsoil routine
- Add snow depending on temperature/height
  - Create a temperature map (using dsq). With seed corners at the north
    colder than seed corners at the south
  - In pickBlock if $y > $h && $y == $waterlevel && tempmap is cold we
    place ice
  - In pickBlock if $y == $h+1 and tempmap (+ height) is cold, we add
    snow-cover block
- redo decoration (based on biome?)
- If not presets provided, we should pick one based on the seed.

# Known Issues

- terrain can be somewhat cracked


