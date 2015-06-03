<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/killrate.png" style="width:64px;height:64px" width="64" height="64"/>

# KillRate

* Summary: Keep track of how much killing is going-on
* Dependency Plugins: N/A
* PocketMine-MP version: 1.4 - API 1.10.0
* DependencyPlugins: -
* OptionalPlugins: PocketMoney,MassiveEconomy,EconomyAPI,GoldStd
* Categories: Informational
* Plugin Access: Commands, Databases, Entities, Tile Entities
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/KillRate)

## Overview

Keep track on how much killing is going-on on a server.

It may optionally use an economy plugin like for example, PocketMoney,
to reward killing.

Basic Usage:

* killrate - Will show your KillRate stats.
* killrate stats [player] - Show the KillRate stats for [player]
* killrate top [online] - Show top players.

You can also place signs with the following text in the first line:

* [STATS] - Current player statistics
* [RANKINGS] - Top 3 players + scores
* [ONLINE TOPS] - Top 3 on-line players + scores
* [RANKNAMES] - Top 3 player names
* [RANKPOINTS] - Top 3 player scores
* [TOPNAMES] - Top 3 on-line player names
* [TOPPOINTS] - Top 3 on-line player scores

These signs will then display current statistics.  These signs can be
further customized by adding the following:

* LINE2 - Title, first line of the sign.
* LINE3 - What statistic to count (i.e. deaths, points, player), etc.
* LINE4 - format line, selects a format from the config.yml formats section.

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

```YAML
settings:
    points: true
    rewards: true
    creative: false
    dynamic-updates: 80
    kill-streak: false or value
    reset-on-death: false or value
    pop-up: false
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
  '[STATS]': stats
  '[RANKINGS]': rankings
  '[ONLINE TOPS]': online-ranks
  '[RANKNAMES]': rankings-names
  '[RANKPOINTS]': rankings-points
  '[TOPNAMES]': online-top-names
  '[TOPPOINTS]': online-top-points
formats:
  default: '{sname} {count}'
  names: '{n}.{player}'
  scores: '{count}'
```

If `creative` is set to true, kills done when the player is in
`creative` will be counted.  The default is false, *NOT* to count
them.

If `points` is true, points are awarded and tracked.  You need to
enable `points` for the rankings to work.

If `rewards` is true, money is awarded.  You need an economy plugin
for this to work.

`dynamic-updates` show in tick intervals how often signs are updated.

`pop-up` will use the _pop up_ message to show the player's score.

`kill-streak`: Set to `false` or to a number.  Will show the kill
streak of a player.

`reset-on-death`: Set to `false` or to a number.  When the player dies
that number of times, scores will reset. (It is GAME OVER).

`values` are used to configure how many points or how much money is
awarded per kill type.  The first number is points, the second is
money.  You can use negative values.

`signs` are used to configure sign texts.  These texts will be used to
identify which signs should show player stats.

`formats` are used to show what data are shown in the different
signs.  The following variables are available in these formats:

* {player} - player's name
* {n} - rank number
* {count} - score
* {sname} - only the first 8 characters of a name

## Translations

This plugin will honour the server language configuration.  The
languages currently available are:

* English
* Spanish

You can provide your own message file by creating a file called
`messages.ini` in the plugin config directory.  Check
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/KillRate/resources/messages/)
for sample files.

The contents of these "ini" files are key-value pairs:

	"Base text"="Translated Text"

# Changes

* 1.1.1:
  * Minor tweaks
  * Signs are more configurable
* 1.1.0: General improvements
  * Added experimental MySQL support
  * Messages file and translations: spanish
  * Dynamic signs
  * Pop-up scores
  * Kill Streak
  * Reset scores on deaths
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

