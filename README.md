pocketmine-plugins
==================

Repository for my PocketMine plugins

## Available Plugins

* NotSoFlat - *Outdated*!
* ManyWorlds - a multiple world implementation.
* SignWarp - A sign based teleport facility.
* GrabBag - My personal collection of commands and listener modules.
* Scorched - Major world destruction
* WorldProtect - Anti-griefing and per world pvp.
* LocalChat - Localized chat

## Beta

* ItemCasePE - A port of Bukkit ItemCase

## Unofficial

* RunePvP - A basic PvP manager

## Development

* Voodoo - Animate zombies
* pmptemlate - test and dev
* NotNormal - a terrain generator.
* NotSoFlat - to be deleted

## Available Tools

* rcon - An rcon client.

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
  * Check Level->getName vs getFolderName
* pmimporter: merge chunks ... by selecting square regions and offsets
  - limit this at region|chunk resolution.
  - Always specify corners and merge
* GrabBag:
  * Frost/Defrost
  * after - certain time, execute command (scheduled stop)
  * clear entities
* Player Interact Peacefully
  - command: attack/interact - defualts to interact
  - if holding a weapon attack (always)
  - if holding compass/clock/food/string/feather/seeds (never)
* Add a Snowball/Egg or something and use it as football..
* RuneGM:
* Adds a GameMaster:
  * Automatically spawned villager or a NPC (Player)
  * If LocalChat is active we use it... otherwise you need to use /rp
    command.
  * If attacked it will retaliate (or kill you...)
  * Implements the RuinPvP casino and shop functionality.
  * Provide rankings and stats...
  * Moves around randomly in his spawn area...


* PMScript:
  {{ something }} the something is a PHP expression.
  Some syntax sugar:
	$[_A-Za-z][_a-zA-Z0-9]*.[_A-Za-z][_A-Za-z0-9]*[^(] ---> converted
  to  $<something>->get<something>()

  @ php ... this is raw PHP code.. prefered altenate syntax.
  else goes to "PM command processor"
  Event handlers... per world.
  Use closures:
      $example = function ($arg) {
        echo($arg);
      }
      $example("hello");
  Gets called onLevelLoad
  registerEvents($obj,$plugin);
  Check what "reload" does, can it unregister event? there is no API
  for it...
	?Loader: disablePlugin($plugin)
	>>>HandlerList::unregisterAll($plugin);
	?removePermissions

* * *


v1.5 will bring:

1. new world generator: biomes
2. full Entity classes and physics

MINI GAMES

DeathSwap
After the game has started, run away from your opponents.
A random timer runs in the backround, and when it finishes after upto 2 Minutes, 2 players postitions will be swapped. The timer will be restarted, and the next time it finishes, two new randomly chosen players will get swapped. Try to be the last person alive and kill other players without seeing them. It's up to you to find out how to do that.

* * *

## Notes

	specter spawn Playername # The full command to spawn a new dummy
	s s playername # Luckily there is shorthand
	s c playername /spawn # Execute /spawn as player

Give/Get items:

* zombie : spawn_egg:32
* villager : spawn_egg:15

- resubmit
  - RunePVP (after imporvemennts)
  - Voodoo - improve movement
