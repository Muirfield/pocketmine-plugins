# NotSoFlat

* * *

    NotSoFlat 0.3
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

This is a simple [PocketMine-MP][3] Terrain Generator.  It is based on the
Superflat Generator, but mixes in the [Diamond-Square][1] algorithm
to generate decent looking terrain.

# Usage

Copy to your plugins directory to install.  Use `simpleworlds` to
generate new worlds.

If using `simpleworlds` use the command:

       swg 314 NotSoFlat myworld

`314` is the seed, and you can change it to any number you want.
`myworld` is the new world being created, replace as appropriate.

# Configuration

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

- spawn : This will create a circle around the spawn using the
  specified block.  The following sub-options are used:
  - radius : size of the spawn circle
  - block : block type.
- decoration : Will decorate the terrain with the specified objects:
  - grasscount : tall grass or flowers
  - treecount : trees
  - desertplant : _This is an extension to the Superflat generator_  
    Place cacti or weeds on top of sand blocks.

The terrain generation itself can be configured using the `dsq` option.  
_(This is an extension to the Superflat generator)_

## dsq

- min : minimum height of terrain
- max : maximum height of terrain
- water : water level
- waterblock : What block to use instead of water.  Could be used
  configure a lava (Block 11) sea or an ice sea.
- off : how quickly terrain will vary
- strata : A list of `:` (colon) separated numbers used to select
  the different strata structures.
- dotsz : averages the terrain so it is not as rough.
- fn : Applies a function to the height map.  Available functions:
  - exp : Exponential.
  - linear : This doesn't do anything
- fndat : Values to pass to the `fn` function.
- hell : If set, it will create a roof of Netherrack and Bedrock at
  the top of the world.

# Off-line World Generator

__For Advanced users only__

For convenience (mostly mine) I hacked together a basic world
generator script that can be run from outside [PocketMine-MP][3].
It can be found [here][4].

To install you must copy it to your PocketMine-MP directory (at the same
place you have your `start.sh` script).

To use, you must enter at the command-line in the PocketMin_MP directory:

       bin/php5/bin/php -I plugins/NotSoFlat.php --preset=plains 314 NotSoFlat myworld

Where:

- `plugins/NotSoFlat.php` : is the plugin file to load (the `-I` options
  multiple times if you need to load mulitple plugins)
- `--preset=plains` : `generator-string` to use.  You can use it to specify
  different presets without having to modify your `server.properties` file.
- `314` : Is the seed.  You can use any number you want.
- `NotSoFlat` : The name of this generator.
- `myworld` : The name of the new world to generate.

# Example Presets

- `plains`  
   Generate a _plain_ looking terrain.
- `nsfv1;temperate;0;spawn(radius=10 block=24),dsq(min=24 max=90 water=60 strata=30:54:60:74:80 dotsz=0.8),decoration(treecount=80 grasscount=45 desertplant=0)`  
   Generate a _plain_ looking terrain.
- `nsfv1;temperate;1;spawn(radius=10 block=48),dsq(min=24 max=90 water=30 strata=25:27:29:32:55 dotsz=1.0 fn=exp fdat=2),decoration(treecount=80 grasscount=45 desertplant=0)`  
   Mountain terrain
- `nsfv1;7,59x1,3x3,12:7,59x1,3x3,2;1;spawn(radius=10 block=48),dsq(min=53 max=57 strata=55 water=55 dotsz=0.7),decoration(treecount=100 grasscount=100 desertplant=0)`  
   A flatland.
- `nsfv1;hell;8;spawn(radius=10 block=89),dsq(min=40 max=80 water=55 waterblock=11 fn=exp fndat=1.7 hell=1)`  
   A possible Netherworld.
- `nsfv1;arid;2;spawn(radius=10 block=24),dsq(min=50 max=70 strata=1:2:52:65:68 water=51 dotsz=0.8),decoration(desertplant=80)`  
   Desert world
- `nsfv1;temperate;1;spawn(radius=10 block=48),dsq(min=24 max=90 water=30 strata=25:27:29:32:60 dotsz=0.9 fn=exp fdat=0.5),decoration(treecount=80 grasscount=45 desertplant=0)`  
   A mesa with sheer cliffs.

# References

- [Diamond-Square Algorithm][1]
- [Minecraft Superflat generator][2]
- [PocketMine-MP][3]
- [server.properties settings][7]
- [World Generation Script][4]
- [NotSoFlat github page][5]
- [NotSoFlat plugin page][6]

[1]: http://en.wikipedia.org/wiki/Diamond-square_algorithm "Wikipedia"
[2]: http://minecraft.gamepedia.com/Superflat "Superflat Generator"
[3]: http://www.pocketmine.net/ "PocketMine-MP"
[4]: https://raw.github.com/alejandroliu/pocketmine-plugins/master/scripts/GenWorld.php "GenWorld script"
[5]: https://github.com/alejandroliu/pocketmine-plugins/tree/master/NotSoFlat "GitHub page"
[6]: http://forums.pocketmine.net/plugins/notsoflat.385/ "PocketMine-MP Plugins page"
[7]: https://github.com/PocketMine/PocketMine-MP/wiki/server.properties "Server Properties"

# Changes

* 0.1 : Initial release
* 0.2 : Updates
  - Updated API level
  - Misc typos/bug-fixes
  - Fixed tree generation
  - tweaked defaults a bit
* 0.3 : New features
  - multiple ground configs depending of height
  - cactus and weeds generation on sand
  - fn and dotsz settings to tweak the look of the environment
  - richer set of presets

# TODO

- Add snow depending on temperature/height
  - Create a temperature map (using dsq). With seed corners at the north
    colder than seed corners at the south
  - In pickBlock if $y > $h && $y == $waterlevel && tempmap is cold we
    place ice
  - In pickBlock if $y == $h+1 and tempmap (+ height) is cold, we add
    snow-cover block

# Known Issues

- terrain can be somewhat cracked


