# NotSoFlat

* * *

    NotSoFlat 0.1
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

# TODO

- Add code to modify terrain based on biome settings.
  biome determine dsq values and topsoil blocks.
- Add topsoil by lattitude and height
- redo decoration (based on biome?)

# Known Issues

- `decorations.treecount` doesn't seem to work


