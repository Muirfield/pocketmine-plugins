<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/killrate.png" style="width:64px;height:64px" width="64" height="64"/>

# KillRate

* Summary: Keep track of how much killing is going-on
* Dependency Plugins: N/A
* PocketMine-MP version: 1.4 - API 1.10.0
* DependencyPlugins: -
* OptionalPlugins: PocketMoney,MassiveEconomy,EconomyAPI,GoldStd
* Categories: Informational
* Plugin Access: Commands, Entity
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/KillRate)

## Overview

Keep track on how much killing is going-on on a server.

It may optionally use an economy plugin like for example, PocketMoney,
to reward killing.

Basic Usage:

* killrate - Will show your KillRate stats.
* killrate stats [player] - Show the KillRate stats for [player]
* killrate top [online] - Show top players.

You can also place signs with the following text:

* [STATS]
* [RANKINGS]
* [ONLINE TOPS]

These signs will then display current statistics.

## Documentation

This plugin supports PocketMoney and GoldStd and has experimental
support for MassiveEconomy and EconomyAPI.

### Permission Nodes

* killrate.cmd : Give players access to KillRate command
* killrate.cmd.stats : Access to stats command
* killrate.cmd.stats.other : View other's stats
  (Defaults to Op)
* killrate.cmd.rank : View top players
* killrate.signs.place : Allow to place KillRate signs
  (Defaults to Op)
* killrate.signs.use : Allow to use KillRate signs


## Configuration

These can be configured from `config.yml`:

    settings:
	points: true
	rewards: true
	creative: false
	dynamic-updates: 80
    values:
	zombie: [10,100]
	Player: [100, 100]
	'*': [0, -10]
    backend: SQLiteMgr
    MySQL:
	host: localhost
	user: nobody
	password: secret
	database: KillRateDb
	port: 3306
    signs:
	"[STATS]": stats
	"[RANKINGS]": rankings
	"[ONLINE TOPS]": online-ranks

If `creative` is set to true, kills done when the player is in
`creative` will be counted.  The default is false, *NOT* to count
them.

If `points` is true, points are awarded and tracked.  You need to
enable `points` for the rankings to work.

If `rewards` is true, money is awarded.  You need an economy plugin
for this to work.

`dynamic-updates` show in tick intervals how often signs are updated.

`values` are used to configure how many points or how much money is
awarded per kill type.  The first number is points, the second is
money.  You can use negative values.

## Translations

This plugin will honour the server language configuration.  The
languages currently available are:

* English
* Spanish

You can provide your own message file by creating a file called
`messages.ini` in the plugin config directory.  Check
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/KillRate/resources/messages/)
for sample files.

# Changes

* 1.1.0: General improvements
  * Added experimental MySQL support
  * Translations: spanish
  * Dynamic signs
* 1.0.2 : Arrow
  * Improved scoring of Exploding arrows
  * Fixed a bug in the way we call the EconomyAPI
* 1.0.1 : Bugfixes
  * Removed warnings
  * Improve the scoring detection
  * Scores deaths
  * Added support for GoldStd
* 1.0.0 : First submission

# Copyright

    KillRate
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

