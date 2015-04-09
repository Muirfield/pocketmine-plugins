NotSoFlat
=======

* Summary: An alternative world generator
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 - API 1.6.0
* DependencyPlugins: -
* OptionalPlugins: -
* Categories: Wolrd Generators
* Plugin Access: 
* WebSite: [github](https://github.com/alejandroliu/bad-plugins/tree/master/)

Overview
--------

This is a simple [PocketMine-MP][3] Terrain Generator.  It is based on the
Superflat Generator, but mixes in the [Simplex Noise][1] algorithm
to generate decent looking terrain.


Documentation
-------------

### Usage

Copy to your plugins directory to install.  Use `ManyWorlds` to
generate new worlds.

If using `ManyWorlds` use the command:

	/mw create testworld 4994 notsoflat

`4994` is the seed, and you can change it to any number you want.
`testworld` is the new world being created, replace as appropriate.

### Configuration

You can configure the generator using the presets string.
The presets strings can be configured in your `server.properties`
file under the key `generator-settings`.

You can either use a predefined config or use a preset string similar
to the one used by [Superflat][2]

The following are the available pre-defined config preset strints:

- overworld
- plains
- ocean
- hills
- mountains
- flatland
- hell
- desert
- mesa
- desert hills

It is possible to control de terrain generation with greater
detail using a preset configuration string:

	nsfv1 ; blocks ; biome ; options

Where:

- nsfv1 : it is a preset version string. It is ignored.
- blocks : it is an extended block preset configuration.  You can
  especify one or more strata structures by using a "`:`" as
  separator.  You can also use a predefined string.  Predefined
  block strings:
  - temperate : mostly grassy tile set
  - arid : a harsh, mostly desert tile set
  - hell : nettherrack all around
- biome : this is ignored
- options : additional settings to tweak the terrain generator.

Because some of the code is based on PocketMine-MP Superflat
generator, it recognizes the following options:

- decoration : Will decorate the terrain with the specified objects:
  - grasscount : tall grass or flowers
  - treecount : trees

The terrain generation itself can be configured using the `dsq` option.  
_(This is an extension to the Superflat generator)_

### dsq

- waterblock : What block to use instead of water.  Could be used
  configure a lava (Block 11) sea or an ice sea.
- strata : A list of `:` (colon) separated numbers used to select
  the different strata structures.
- hell : If set, it will create a roof of Netherrack and Bedrock at
  the top of the world.

### References

- [Diamond-Square Algorithm][1]
- [Minecraft Superflat generator][2]
- [PocketMine-MP][3]

    [1]: http://en.wikipedia.org/wiki/Simplex_noise "Wikipedia"
    [2]: http://minecraft.gamepedia.com/Superflat "Superflat Generator"
    [3]: http://www.pocketmine.net/ "PocketMine-MP"

Changes
-------

* 1.0.0 : Updated to PM1.4 API
  * Changed from DiamodSquares to Simplex Noise
  * Removed:
    - decoration:
      - spawn : This will create a circle around the spawn using the
	specified block.  The following sub-options are used:
	- radius : size of the spawn circle
	- block : block type.
    - dsq:
      - min : minimum height of terrain
      - max : maximum height of terrain
      - water : water level
      - off : how quickly terrain will vary
      - dotsz : averages the terrain so it is not as rough.
      - fn : Applies a function to the height map.  Available functions:
	- exp : Exponential.
	- linear : This doesn't do anything
      - fndat : Values to pass to the `fn` function.
* 0.3 : New features
  - multiple ground configs depending of height
  - cactus and weeds generation on sand
  - fn and dotsz settings to tweak the look of the environment
  - richer set of presets
* 0.2 : Updates
  - Updated API level
  - Misc typos/bug-fixes
  - Fixed tree generation
  - tweaked defaults a bit
* 0.1 : Initial release

Copyright
---------

    Mobsters
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
