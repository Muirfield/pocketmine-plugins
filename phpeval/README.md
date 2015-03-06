phpeval
========

A very basic Plugin that lets you execute `PHP` code directly from
_PocketMine-MP_.

Basic Usage:

    /php <php code>

Documentation
-------------

This plugin evaluates `PHP` code directly from _PocketMine-MP_ console
and/or through `/` slash commands on a Minecraft client.

It is meant for debugging purposes.  I use it to test if extensios are
available and to check if regular expressions do what they are supposed
to.

*BE CAREFUL AS THIS CAN CRASH A RUNNING SERVER*.

If the first character in the PHP code is a `=` then a `return`
statement is inserted at the beginning of it.  This will return the
results of whatever is being executed and it will shown on screen.

### Available Variables

* `$sender` is an available variable that is a `CommandSender` object.
  It can be used with: `$sender->sendMessage("Hello world)`.
* `$server` instance of the server object.
* `$logger` instance of the main logger object.


### Permission Nodes:

* phpeval.cmd.php - Allows the user to execute arbitrary PHP code


Copyright
=========

    phpeval
    Copyright (C) 2015 Alejandro Liu  
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
