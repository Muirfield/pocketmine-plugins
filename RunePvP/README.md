RunePvP
=======

* Summary: A funny PvP manager plugin inspired in RuinPvP
* Dependency Plugins: N/A
* PocketMine-MP version: 1.4 - API 1.10.0
* DependencyPlugins: -
* OptionalPlugins: SignShop,PocketMoney,MassiveEconomy,EconomyAPI
* Categories: Fun
* Plugin Access: Commands, Entity
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/RunePvP)

Overview
--------

A simple PvP manager inspired by RuinPvP.  It needs a economy plugin
like PocketMoney, for example.

Rules:

At first, every users gets 500 points.  This is configured in
PocketMoney (or whatever Economy plugin you use).

If players kills another player, the winner gets 100 points and the
loser loses 100 points.  Once players are under 100 points they will
not loose any points.  And winners only get 50 points for these kills.

Whenever players reach 10 kills, they get a level up and get a 1,000
points prize.

For the full RuinPvP experience you need additional plugins.  For
example you can get a SignShop plugin or a Gambling plugin.

Basic Usage:

* runepvp - Will show your RunePvP stats.
* runepvp stats [player] - Show the RunePvP stats for [player]

Documentation
-------------

This plugin supports PocketMoney and has experimental support for
MassiveEconomy and EconomyAPI.

### Configuration

These can be configured from `config.yml`:

	points:
	  kills: 100
	  level: 1000

Configure the number of points awared per `kill` or when a player
reaches a level up.

### Permission Nodes:

* runepvp.cmd: Give players access to RunePvP command

### TODO

* Rankings
* Show rankings on a sign
* Show stats on a sign.  Tap a sign and we send a packet update sign
  with stats.  TileEntity is not changed!

Changes
-------

* 1.1.0 : More econmoy support
  * Some code fixes and removed some debug code that slipped through
  * Added support for Economy and MassiveEconomy.
* 1.0.0 : First public release

Copyright
---------

    RunePvP
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
