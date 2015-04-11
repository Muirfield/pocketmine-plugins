pocketmine-plugins
==================

Repository for my PocketMine plugins

## Available Plugins

* ManyWorlds - a multiple world implementation.
* SignWarp - A sign based teleport facility.
* GrabBag - My personal collection of commands and listener modules.
* Scorched - Major world destruction
* WorldProtect - Anti-griefing and per world pvp.
* LocalChat - Localized chat
* NotSoFlat - An altenative world generator.

## Pending Moderation

* ItemCasePE - A port of Bukkit ItemCase
* KillRate - Keep track of killing


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

* ManyWorlds: Add world to the pocketmine.yml file -- Not possible: NO
  API
