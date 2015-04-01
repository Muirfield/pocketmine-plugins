pocketmine-plugins
==================

Repository for my PocketMine plugins

## Available Plugins

* NotSoFlat - *Outdated*!
* ManyWorlds - a multiple world implementation.
* SignWarp - A sign based teleport facility.
* GrabBag - My personal collection of commands and listener modules.
* Scorched - Major world destruction
* Voodoo - Animate zombies
* WorldProtect - Anti-griefing and per world pvp.
* RunePvP - A basic PvP manager

## Development

* pmptemlate - test and dev
* NotNormal - a terrain generator.
* NotSoFlat - to be deleted

## Available Tools

* rcon - An rcon client.
* pmimporter - Import/Convert into PocketMine-MP.  (Used by ImportMap)
    * ImportMap - Imports maps into PocketMine-MP.

## Notes

	specter spawn Playername # The full command to spawn a new dummy
	s s playername # Luckily there is shorthand
	s c playername /spawn # Execute /spawn as player


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

* Add Shift regions option to Copier.php
* ManyWorlds: Add world to the pocketmine.yml file -- Not possible: NO
  API
* pmimporter: merge chunks ... by selecting square regions and offsets
  - limit this at region|chunk resolution.
  - Always specify corners and merge
* Local chat.  Chat only works around you
* GrabBag: Interact peacefully
  - CompassTP : Use fast move?
  * Frost/Defrost
* Player Interact
  - Protect (player are not PvP)
  - command: attack/interact - defualts to interact
  - if holding a weapon attack (always)
  - if holding compass/clock/food/string/feather/seeds (never)

* Add a Snowball/Egg or something and use it as football..

* PMScript:
  {{ something }} the something is a PHP expression.
  @ php ... this is raw PHP code.. prefered altenate syntax.
  else goes to "PM command processor"
  Event handlers... per world.

v1.5 will bring:

1. new world generator: biomes
2. full Entity classes and physics
