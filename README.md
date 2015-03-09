pocketmine-plugins
==================

Repository for my PocketMine plugins

Ideas
-----

* Create a Generator based of flat that creates infinite maze


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

    git checkout <plugin>-dev
    git push ; git pull
    git checkout master
    git push ; git pull
    git merge --no-ff <plugin>-dev
    # ... Check version number ...
    # ... Test phar ...
    git commit -a -m'preparing <plugin> release X.Y'
    git tag -a <plugin>-X.Yrel -m'Release X.Y'
    git push
    git checkout <plugin>-dev
    git merge --no-ff master
    # ... bump version number ...
    git commit -a -m"Bump version number"
    git tag -a <plugin>-X.Y+1pre -m"New dev cycle"
    git push origin <plugin>-dev
    git push origin --tags

## Set-up

    git checkout -b <plugin>-dev master
    git tag -a "<plugin>-X.Ypre" -m "Dev branch"
    git push origin <plugin>-dev
    git push origin --tags
