# Goto

* * *

    Goto 0.1
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

*Goto* is a Teleport plugin to move around multiple worlds on command.

Commands are restricted to "creative", "ops" or "console", depending
on the context.

# Commands

The only command is `/go` but it accepts the following sub-commands:

- `/go to [world|player|chkpnt|marker]`  
  Alias: `goto`  
  Teleport to location.
- `/go push [world|player|chkpnt|marker]`  
  Alias: `push`  
  Saves current location on the stack and teleports to location.
- `/go pop`  
  Alias: `pop`  
  Teleports to the top location from the stack.
- `/go move [player] > [world|player|chkpnt|marker]`  
  Alias: `move`  
  **OP only command**  
  Teleport `[player]` to location.
- `/go summon [player]`  
  Alias: `summon`  
  **OP only command**  
  Teleports `[player]` to your location.  Original location is saved.
- `/go dismiss [player]`  
  Alias: `dismiss`  
  **OP only command**  
  Teleports `[player]` to the location of their first summoning.

# Changes

* 0.1 : Initial version

# TODO

- Implement
  - checkpoint
  - mark
  - warp
  - rm
  - ls
- test summon & dismiss

# Known Issues

- Needs more testing...


