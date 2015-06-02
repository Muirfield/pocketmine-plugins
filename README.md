pocketmine-plugins
==================

Repository for my *official* PocketMine plugins.

## Available Plugins

<table>
<tr><th>Plugin</th><th>Description</th></tr>
<!---------------------------------------------------------------------->
<tr>
  <th>
    <a href="http://forums.pocketmine.net/plugins/manyworlds.1042/">
      <img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/ManyWorlds-icon.png" style="width:64px;height:64px" width="64" height="64"/>
      <br/>
      ManyWorlds
    </a>
  </th>
  <td>
    a multiple world implementation
  </td>
</tr>
<!---------------------------------------------------------------------->
<tr>
  <th>
    <a href="http://forums.pocketmine.net/plugins/signwarp.1043/">
      <img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/SignWarp-icon.png" style="width:64px;height:64px" width="64" height="64"/>
      <br/>
      SignWarp
    </a>
  </th>
  <td>
    A sign based teleport facility.
  </td>
</tr>
<!---------------------------------------------------------------------->

<tr>
  <th>
    <a href="http://forums.pocketmine.net/plugins/grabbag.1060/">
      <img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/GrabBag-icon.png" style="width:64px;height:64px" width="64" height="64"/>
      <br/>
      GrabBag
    </a>
  </th>
  <td>
    My personal collection of commands and listener modules.
  </td>
</tr>
<!---------------------------------------------------------------------->
<tr>
  <th>
    <a href="http://forums.pocketmine.net/plugins/scorched.1062/">
      <img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/Scorched-icon.jpg" style="width:64px;height:64px" width="64" height="64"/>
      <br/>
      Scorched
    </a>
  </th>
  <td>
    Major world destruction
  </td>
</tr>
<!---------------------------------------------------------------------->
<tr>
  <th>
    <a href="http://forums.pocketmine.net/plugins/worldprotect.1079/">
      <img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/WorldProtect-icon.png" style="width:64px;height:64px" width="64" height="64"/>
      <br/>
      WorldProtect
    </a>
  </th>
  <td>
    Anti-griefing and per-world PvP
  </td>
</tr>
<!---------------------------------------------------------------------->
<tr>
  <th>
    <a href="http://forums.pocketmine.net/plugins/localchat.1083/">
      <img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/localchat-icon.jpg" style="width:64px;height:64px" width="64" height="64"/>
      <br/>
      LocalChat
    </a>
  </th>
  <td>
    Localized chat
  </td>
</tr>
<!---------------------------------------------------------------------->
<tr>
  <th>
    <a href="http://forums.pocketmine.net/plugins/notsoflat.385/">
      <img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/Notsoflat-icon.png" style="width:64px;height:64px" width="64" height="64"/>
      <br/>
      NotSoFlat
    </a>
  </th>
  <td>
    An alternative world generator
  </td>
</tr>
<!---------------------------------------------------------------------->
<tr>
  <th>
    <a href="http://forums.pocketmine.net/plugins/itemcase.1138/">
      <img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/ItemCase-icon.png" style="width:64px;height:64px" width="64" height="64"/>
      <br/>
      ItemCasePE
    </a>
  </th>
  <td>
    A simplified implementation of Bukkit's ItemCase.
  </td>
</tr>
<!---------------------------------------------------------------------->
<tr>
  <th>
    <a href="http://forums.pocketmine.net/plugins/killrate.1137/">
      <img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/killrate.png" style="width:64px;height:64px" width="64" height="64"/>
      <br/>
      KillRate
    </a>
  </th>
  <td>
    Keep track of how much killing is going-on.
  </td>
</tr>
<!---------------------------------------------------------------------->
<tr>
  <th>
    <a href="http://forums.pocketmine.net/plugins/magicteleportal.1146/">
      <img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/portal-icon.jpg" style="width:64px;height:64px" width="64" height="64"/>
      <br/>
      MagicTelePortal
    </a>
  </th>
  <td>
    Simple portal plugin
  </td>
</tr>
<!---------------------------------------------------------------------->
<tr>
  <th>
    <a href="http://forums.pocketmine.net/plugins/simpleauthhelper.1112/">
      <img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/helper-icon.png" style="width:64px;height:64px" width="64" height="64"/>
      <br/>
      SimpleAuthHelper
    </a>
  </th>
  <td>
    Makes SimpleAuth easier to use.
  </td>
</tr>
<!---------------------------------------------------------------------->
</table>


# Plugin Access

* Internet Services: Any access to internet that is not done as the
  server. This includes acting as a server, downloading files, using
  external API services, or sending information. Sending custom
  packets through the normal Player interface or using external
  databases (like MySQL) are not considered for this section.
* Other Plugins: If the plugin calls methods on other plugins. This
  does not include managing plugins.
* Manages Permissions: Modifying permission nodes values from the
  default. Creating permission nodes is not considered for this
  section.
* Commands: Registering commands via the normal plugin interface or
  via custom interfaces.
* Data Saving: Saving data to disk. Usage of databases (like SQLite3
  or MySQL) is not covered here.
* Custom Threading: Only for plugins that create their own threads /
  workers. Plugins that only use AsyncTasks must not mark this.
* Databases: If using any kind of database (SQLite3, MySQL, ...)
* Entities: Tracking entities, spawning custom entities or managing
  them.
* Items / Blocks: Modifies/adds blocks or items (not editing the
  world, but adding new objects)
* Tile Entities: Tracking tiles, spawning custom tiles or managing
  them.
* World Editing: Changes things in loaded worlds
* Manages worlds: Load/unload/create worlds
* Manages plugins: Load/unload/enable/disable plugins


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

