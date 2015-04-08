KillRate
=======

* Summary: Keep track of how much killing is going-on
* Dependency Plugins: N/A
* PocketMine-MP version: 1.4 - API 1.10.0
* DependencyPlugins: -
* OptionalPlugins: PocketMoney,MassiveEconomy,EconomyAPI
* Categories: Informational
* Plugin Access: Commands, Entity
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/KillRate)

Overview
--------

Keep track on how much killing is going-on on a server.

It may optionally use an economy plugin like for example, PocketMoney,
to reward killing.

Basic Usage:

* killrate - Will show your KillRate stats.
* killrate stats [player] - Show the KillRate stats for [player]
* killrate top [online] - Show top players.

Documentation
-------------

This plugin supports PocketMoney and has experimental support for
MassiveEconomy and EconomyAPI.

### Configuration

These can be configured from `config.yml`:

    settings:
	points: true
	rewards: true
	creative: false
    values:
	zombie: [10,100]
	Player: [100, 100]
	'*': [0, -10]

If `creative` is set to true, kills done when the player is in
`creative` will be counted.  The default is false, *NOT* to count
them.

If `points` is true, points are awarded and tracked.  You need to
enable `points` for the rankings to work.

If `rewards` is true, money is awarded.  You need an economy plugin
for this to work.

`values` are used to configure how many points or how much money is
awarded per kill type.

### Permission Nodes:

* killrate.cmd:
  * default: true
  * description: "Give players access to KillRate command"
* killrate.cmd.stats:
  * default: true
  * description: "Access to stats command"
* killrate.cmd.stats.other:
  * default: op
  * description: "View other's stats"
* killrate.cmd.rank:
  * default: true
  * description: "View top players"

Changes
-------

* 1.0.0 : First submission

Copyright
---------

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
