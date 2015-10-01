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

# Specter quick reference

* specter spawn|s <player> [address]
  - spawn a new specter player.
* specter kick|q <player>
  - kill a specter session.
* specter move|m <player> <x> <y> <z>
  - Move player
* specter attack|a <player> <victim>|<eid:victim eid>
  - attack
* specter command|c <player> <cmd>
  - execute command
* specter respawn|r <player>
  - respawn

