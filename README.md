pocketmine-plugins
==================

Repository for my PocketMine plugins

## Available Plugins

* [ManyWorlds](http://forums.pocketmine.net/plugins/manyworlds.1042/)
  - a multiple world implementation.
* [SignWarp](http://forums.pocketmine.net/plugins/signwarp.1043/) - A
  sign based teleport facility.
* [GrabBag](http://forums.pocketmine.net/plugins/grabbag.1060/) - My
  personal collection of commands and listener modules.
* [Scorched](http://forums.pocketmine.net/plugins/scorched.1062/) -
  Major world destruction
* [WorldProtect](http://forums.pocketmine.net/plugins/worldprotect.1079/) -
  Anti-griefing and per world pvp.
* [LocalChat](http://forums.pocketmine.net/plugins/localchat.1083/) -
  Localized chat
* [NotSoFlat](http://forums.pocketmine.net/plugins/notsoflat.385/) -
  An altenative world generator.
* [ItemCasePE](http://forums.pocketmine.net/plugins/itemcase.1138/) -
  A simplified implementation of Bukkit's ItemCase.
* [KillRate](http://forums.pocketmine.net/plugins/killrate.1137/) -
  Keep track of how much killing is going-on.


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

