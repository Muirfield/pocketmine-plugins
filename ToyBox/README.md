<img src="https://raw.githubusercontent.com/alejandroliu/bad-plugins/master/Media/icon-toy-box.png" style="width:64px;height:64px" width="64" height="64"/>

# ToyBox

* Summary: A box full of fun toys and tools
* Dependency Plugins: N/A
* PocketMine-MP version: 1.4 - API 1.10.0
* DependencyPlugins: -
* OptionalPlugins: N/A
* Categories: Fun
* Plugin Access: Blocks, Commands
* WebSite: [github](https://github.com/alejandroliu/bad-plugins/tree/master/ToyBox)

## Overview

<!-- php: $v_forum_thread = "http://forums.pocketmine.net/plugins/toybox.1135/"; -->
<!-- template: prologue.md -->
INSERTED BY TEMPLATE
<!-- template-end -->

Provide additional items with special functionality to PocketMine.

* TreeCapitator - axe that destroys trees quickly
* CompassTP - Teleporting compass
* Trampoline - Jump and down blocks
* CloakClock - Clock that gives Invisibility
* PowerTool - pickax that destroys blocks instantly
* Floating Torch - Floating torch that follows you around
* Magic Carpet - Fly with a carpet made of glass

### TreeCapitator

Equip an Axe.  Then enter the command `/treecapitator`.  Go to a tree
and break the lowest block.  This will then eliminate the entire tree
above.  If it did'nt block the first time, break the next lowest block
(which now is floating over the air).

### CompassTP

Holding the compass, tap on the screen for one second.  It will
teleport you in the direction you were looking at.

### Trampoline

Place some Sponge on the ground.  Jump on them.  Watch out your
landing!

### CloakClock

Holding a Clock, tap on the sreen.  If it doesn't work, hold the
screen for 1 second.  Will enable invisibility.

### Power Tool

Enter the command `/powertool`.  Equip a pick axe and start tapping on
blocks.  They will be destroyed instantly.

### Floating Toch

Equip a torch, and tap on the screen.  This will create a torch that
will follow you around iluminating the way you are going.

### Magic Carpet

Enter the command `/magiccarpet`.  Walk normally, to go up jump, to go
down look down.  Be careful when going down as you can easily hurt
yourself.


Documentation
-------------

### Commands

* *treecapitator*  
  Toggles treecapitator usage.
* *powertool*  
  Toggles powertool usage
* *magiccarpet*  
  Toggle magic carpet

## Configuration

	---
	# Enable or disable specifif toys
	modules:
	  treecapitator: true
	  compasstp: true
	  trampoline: true
	  powertool: true
	  cloakclock: true
	  floating-torch: true
	  magic-carpet: true
	# Configure torch data
	floating-torch:
	  item: TORCH
	  block: TORCH
	# Configure compass items
	compasstp:
	  item: COMPASS
	# Configure cloaking item
	cloakclock:
	  item: CLOCK
	# Configure power tools
	powertool:
	  ItemIDs:
	  - IRON_PICKAXE
	  - WOODEN_PICKAXE
	  - STONE_PICKAXE
	  - DIAMOND_PICKAXE
	  - GOLD_PICKAXE
	  need-item: true
	  item-wear: 1
	  creative: true
	# Configure TeeCapitator
	treecapitator:
	  ItemIDs:
	  - IRON_AXE
	  - WOODEN_AXE
	  - STONE_AXE
	  - DIAMOND_AXE
	  - GOLD_AXE
	  need-item: true
	  break-leaves: true
	  item-wear: 1
	  broadcast-use: true
	  creative: true
	# Configure trampoline blocks
	trampoline:
	  blocks:
	  - SPONGE
	magic-carpet:
	  block: GLASS
	...

### Permission Nodes

* toybox.treecapitator: Allow treecapitator
* toybox.compasstp: Allow treecapitator
* toybox.powertool: Allow the use of powertool
* toybox.cloakclock.use: Can use cloakclock
* toybox.cloakclock.inmune: Can see players using cloakclock
* toybox.torch: Allow use of torch
* toybox.magiccarpet: Allow use of Magic carpet


## Changes

* 1.1.1: Maintenance release
  * Fixed magic carpet for 1.5
* 1.1.0 : Next release
  * Added Floating Torch
  * Added magic carpet
  * Configuration is more readable
  * Removed CallbackTask deprecation warnings
* 1.0.0 : First submission

## Copyright

    ToyBox
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
