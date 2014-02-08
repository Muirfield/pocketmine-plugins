# NetherPortal

* * *

    NetherPortal 0.2
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

*NetherPortal* is a simple Teleport plugin used to create portals between
worlds.

The way it works is that you create a teleport block and then stepping on
that block will teleport the player to the target world.

Special teleport blocks are by default of type *247* (*Nether Reactor
core*) but this can be changed in the configuration.  However it is
possible to create Portals anywhere.

Only players in "creative" mode or "op"'s can use these commands.

## Creating portals

### Method 1

Enter:

    /netherportal new [x,y,z,world] > [target world]
    /netherportal new [target world]

Alias: `/npnew`

This create a portal to `[target world]` at location `[x,y,z,world]`.
If the location is not specified, the portal will be created at the
location of the last placed teleport block.

### Method 2

Enter:

    /netherportal target [target world]

Alias: `/npto`

This defines the target world for the teleport.  Then place a teleport
block.  This will create a portal on the placed block to the target
world.

### Method 3

Enter:

    /netherportal set [target world]

Alias: `/npset`

This creates a portal to `[target world]` at the current player's
location.

## Removing portals

Enter:

    /netherportal delete [x,y,z,world]

Alias: `/nprm`

This deletes the portal specified by `[x,y,z,world]`.

## Portal management

These commands are available to manage portals:

- `/netherportal ls`  
  `/npls`  
   This lists all the available portals.
- `/netherportal gc`  
  `/npgc`  
   Checks that all the maps specified in the portals are available
   and deletes any portal that refer to missing levels.

## Configuration

- ItemID : Defaults to `247` (*Nether Reactor Core*).  
  This is the special teleport block.

# Background

The idea to use *Nether Reactor Core* came from the `NetherQuick`
plugin.

# Changes

* 0.1 : Initial version
* 0.2 : Completely revamped

# TODO

- Play test
- Publish

# Known Issues

- Needs more testing...


