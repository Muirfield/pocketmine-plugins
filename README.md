pocketmine-plugins
==================

Repository for my PocketMine plugins

## Available Plugins

* NotSoFlat - *Outdated*!
* ImportMap - Imports maps into PocketMine-MP.
* ManyWorlds - a multiple world implementation.
* SignWarp - A sign based teleport facility.
* GrabBag - My personal collection of commands.

## Available Tools

* rcon - An rcon client.
* pmimporter - Import/Convert into PocketMine-MP.  (Used by ImportMap)

Copyright
=========

    pocketmine-plugins
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

Git Recipes
===========

## Keep Dev in sync with master

    git checkout <plugin>-dev
    git merge --no-ff master

## Release

    git checkout dev
    git push ; git pull
    git checkout master
    git push ; git pull
    git merge dev
    # ... Check version number ...
    # ... Test phar ...
    git commit -a -m'preparing <plugin> release X.Y'
    git tag -a <plugin>-X.Yrel -m'Release X.Y'
    git push origin --tags
    git push
    git checkout dev
    git merge master
    # ... bump version number ...
    git commit -a -m"Bump version number"
    git push origin

## Set-up

    git checkout -b <plugin>-dev master
    git push origin <plugin>-dev
    git push origin --tags

To-do
-----

* Create a Generator based of flat that creates infinite maze
* Port the Minetest Map Generator
* Add Shift regions option to Copier.php
* pmimporter: merge chunks ... by selecting square regions and offsets
  - limit this at region|chunk resolution.
  - Always specify corners and merge
* ManyWorlds: Add world to the pocketmine.yml file -- Not possible: NO
  API

v1.5 will bring:

1. new world generator: biomes
2. full Entity classes and physics


* Frost/Defrost
* Add a Snowball/Egg or something and use it as football..
* AI for Chickens?
* Minecarts
* More complex terrain: Based on normal
  - Height - (Normal has *base*)
  - Incline - Multiplier to height (to generate more ruggged terrain)

  - Altitude (computed)
  - Precipitation -
  - Temperature - (softer)
  - Diversity - Changes the selection of stuff

High Temp

Alt \ Precp	High	Medium	Low
High		Mesa	Mesa	DesHill
Medium		Jungle	Savan	Desert
Low		Swamp	Savan	Desert


Medium Temp

Alt \ Precp	High	Medium	Low
High		SnowHll	Hills	Mesa
Medium		Forest	Plains	Savanna
Low		Jungle	Swamps	Beach

Low Temp

Alt \ Precp	High	Medium		Low
High		SnowHil	ColdHill	Taiga
Medium		SnowPla	SnowPlain	Taiga
Low		IcePlai	ColdBeach	IcePla

Simplex:
(OCTAVES #:int) (FREQUENCY #:float) (AMPLITUDE #:float -- Int?)

OCTAVES is the number of octaves of noise to sample,
The number of octaves control the amount of detail of Perlin
noise. Adding more octaves increases the detail of Perlin noise, with
the added drawback of increasing the calculation time.

FREQUENCY is the frequency of the first octave,  The number of cycles
per unit length that a specific coherent-noise function outputs.


AMPLITUDE is the amplitude of the first octave.  Max abs value that
can be output. (-n to +n? or just 0 to n)
