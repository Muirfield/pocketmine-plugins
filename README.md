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

* Implement carts?
  - Rail|PoweredRail
* ManyWorlds: Add world to the pocketmine.yml file.
* ArmorSpawn/SpawnWithItems -> Genesis and other RIP plugins
* Frost/Defrost
* Merge old pmf worlds into pacboy.

- test conversions (including lobbies)
  - mcpe - ok (with quirks)
  - 2x mcr
  - lobbies
  - 2x Anvil
  - convert Aboreal with shift 64.

- GO back to single file.
  pmimporter : contains plugin + cli applet
- Add an ticking task that will show status...
- Also lock it so it knows one is running.
- mv ImportMap into pmimporter/plugin
- Document slow speed.

1. Should change to PluginTask.
2. add singleton characteristics

worker:
lock file, unlock when done.

checker:
check if lock exists.  If exists send status:
1. count filesizes in region and report that.
