<?php

namespace aliuly\grabbag;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

//<!-- start-includes -->
use pocketmine\event\block\BlockFormEvent;
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\event\entity\EntityBlockChangeEvent;
use pocketmine\event\entity\EntityCombustByBlockEvent;
use pocketmine\event\entity\EntityCombustByEntityEvent;
use pocketmine\event\entity\EntityCombustEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntityInventoryChangeEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\event\entity\ItemDespawnEvent;
use pocketmine\event\entity\ItemSpawnEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\FurnaceBurnEvent;
use pocketmine\event\inventory\FurnaceSmeltEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\level\ChunkPopulateEvent;
use pocketmine\event\level\ChunkUnloadEvent;
use pocketmine\event\level\LevelInitEvent;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\level\LevelSaveEvent;
use pocketmine\event\level\LevelUnloadEvent;
use pocketmine\event\level\SpawnChangeEvent;
use pocketmine\event\player\PlayerAchievementAwardedEvent;
use pocketmine\event\player\PlayerAnimationEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerBedLeaveEvent;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\plugin\PluginDisableEvent;
use pocketmine\event\plugin\PluginEnableEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\server\RemoteServerCommandEvent;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\event\server\LowMemoryEvent;
use pocketmine\event\server\QueryRegenerateEvent;
//<!-- end-includes -->

class TraceListener implements Listener {
  protected $plugin;
  protected $callback;
  /**
   * @param PluginBase $owner - plugin that owns this session
   */
  public function __construct(PluginBase $owner,$callback) {
    $this->plugin = $owner;
    $this->callback = $callback;
  }
  private function trace($ev) {
    $callback = $this->callback;
    $callback($ev);
  }
  //<!-- start-methods -->
	public function onBlockFormEvent(BlockFormEvent $ev){
		$this->trace($ev);
	}
	public function onBlockGrowEvent(BlockGrowEvent $ev){
		$this->trace($ev);
	}
	public function onBlockPlaceEvent(BlockPlaceEvent $ev){
		$this->trace($ev);
	}
	public function onBlockSpreadEvent(BlockSpreadEvent $ev){
		$this->trace($ev);
	}
	public function onBlockUpdateEvent(BlockUpdateEvent $ev){
		$this->trace($ev);
	}
	public function onLeavesDecayEvent(LeavesDecayEvent $ev){
		$this->trace($ev);
	}
	public function onSignChangeEvent(SignChangeEvent $ev){
		$this->trace($ev);
	}
	public function onBlockBreakEvent(BlockBreakEvent $ev){
		$this->trace($ev);
	}
	public function onEntityArmorChangeEvent(EntityArmorChangeEvent $ev){
		$this->trace($ev);
	}
	public function onEntityBlockChangeEvent(EntityBlockChangeEvent $ev){
		$this->trace($ev);
	}
	public function onEntityCombustEvent(EntityCombustEvent $ev){
		$this->trace($ev);
	}
	public function onEntityDeathEvent(EntityDeathEvent $ev){
		$this->trace($ev);
	}
	public function onEntityDespawnEvent(EntityDespawnEvent $ev){
		$this->trace($ev);
	}
	public function onEntityExplodeEvent(EntityExplodeEvent $ev){
		$this->trace($ev);
	}
	public function onEntityInventoryChangeEvent(EntityInventoryChangeEvent $ev){
		$this->trace($ev);
	}
	public function onEntityLevelChangeEvent(EntityLevelChangeEvent $ev){
		$this->trace($ev);
	}
	public function onEntityMotionEvent(EntityMotionEvent $ev){
		$this->trace($ev);
	}
	public function onEntityRegainHealthEvent(EntityRegainHealthEvent $ev){
		$this->trace($ev);
	}
	public function onEntityShootBowEvent(EntityShootBowEvent $ev){
		$this->trace($ev);
	}
	public function onEntitySpawnEvent(EntitySpawnEvent $ev){
		$this->trace($ev);
	}
	public function onEntityTeleportEvent(EntityTeleportEvent $ev){
		$this->trace($ev);
	}
	public function onExplosionPrimeEvent(ExplosionPrimeEvent $ev){
		$this->trace($ev);
	}
	public function onItemDespawnEvent(ItemDespawnEvent $ev){
		$this->trace($ev);
	}
	public function onItemSpawnEvent(ItemSpawnEvent $ev){
		$this->trace($ev);
	}
	public function onProjectileHitEvent(ProjectileHitEvent $ev){
		$this->trace($ev);
	}
	public function onProjectileLaunchEvent(ProjectileLaunchEvent $ev){
		$this->trace($ev);
	}
	public function onEntityDamageEvent(EntityDamageEvent $ev){
		$this->trace($ev);
	}
	public function onCraftItemEvent(CraftItemEvent $ev){
		$this->trace($ev);
	}
	public function onFurnaceBurnEvent(FurnaceBurnEvent $ev){
		$this->trace($ev);
	}
	public function onFurnaceSmeltEvent(FurnaceSmeltEvent $ev){
		$this->trace($ev);
	}
	public function onInventoryCloseEvent(InventoryCloseEvent $ev){
		$this->trace($ev);
	}
	public function onInventoryOpenEvent(InventoryOpenEvent $ev){
		$this->trace($ev);
	}
	public function onInventoryPickupArrowEvent(InventoryPickupArrowEvent $ev){
		$this->trace($ev);
	}
	public function onInventoryPickupItemEvent(InventoryPickupItemEvent $ev){
		$this->trace($ev);
	}
	public function onInventoryTransactionEvent(InventoryTransactionEvent $ev){
		$this->trace($ev);
	}
	public function onChunkLoadEvent(ChunkLoadEvent $ev){
		$this->trace($ev);
	}
	public function onChunkPopulateEvent(ChunkPopulateEvent $ev){
		$this->trace($ev);
	}
	public function onChunkUnloadEvent(ChunkUnloadEvent $ev){
		$this->trace($ev);
	}
	public function onLevelInitEvent(LevelInitEvent $ev){
		$this->trace($ev);
	}
	public function onLevelLoadEvent(LevelLoadEvent $ev){
		$this->trace($ev);
	}
	public function onLevelSaveEvent(LevelSaveEvent $ev){
		$this->trace($ev);
	}
	public function onLevelUnloadEvent(LevelUnloadEvent $ev){
		$this->trace($ev);
	}
	public function onSpawnChangeEvent(SpawnChangeEvent $ev){
		$this->trace($ev);
	}
	public function onPlayerAchievementAwardedEvent(PlayerAchievementAwardedEvent $ev){
		$this->trace($ev);
	}
	public function onPlayerAnimationEvent(PlayerAnimationEvent $ev){
		$this->trace($ev);
	}
	public function onPlayerBedEnterEvent(PlayerBedEnterEvent $ev){
		$this->trace($ev);
	}
	public function onPlayerBedLeaveEvent(PlayerBedLeaveEvent $ev){
		$this->trace($ev);
	}
	public function onPlayerBucketEmptyEvent(PlayerBucketEmptyEvent $ev){
		$this->trace($ev);
	}
	public function onPlayerBucketFillEvent(PlayerBucketFillEvent $ev){
		$this->trace($ev);
	}
	public function onPlayerCommandPreprocessEvent(PlayerCommandPreprocessEvent $ev){
		$this->trace($ev);
	}
	public function onPlayerCreationEvent(PlayerCreationEvent $ev){
		$this->trace($ev);
	}
	public function onPlayerDropItemEvent(PlayerDropItemEvent $ev){
		$this->trace($ev);
	}
	public function onPlayerGameModeChangeEvent(PlayerGameModeChangeEvent $ev){
		$this->trace($ev);
	}
	public function onPlayerItemConsumeEvent(PlayerItemConsumeEvent $ev){
		$this->trace($ev);
	}
	public function onPlayerItemHeldEvent(PlayerItemHeldEvent $ev){
		$this->trace($ev);
	}
	public function onPlayerKickEvent(PlayerKickEvent $ev){
		$this->trace($ev);
	}
	public function onPlayerLoginEvent(PlayerLoginEvent $ev){
		$this->trace($ev);
	}
	public function onPlayerMoveEvent(PlayerMoveEvent $ev){
		$this->trace($ev);
	}
	public function onPlayerPreLoginEvent(PlayerPreLoginEvent $ev){
		$this->trace($ev);
	}
	public function onPlayerRespawnEvent(PlayerRespawnEvent $ev){
		$this->trace($ev);
	}
	public function onPlayerDeathEvent(PlayerDeathEvent $ev){
		$this->trace($ev);
	}
	public function onPlayerInteractEvent(PlayerInteractEvent $ev){
		$this->trace($ev);
	}
	public function onPlayerJoinEvent(PlayerJoinEvent $ev){
		$this->trace($ev);
	}
	public function onPlayerQuitEvent(PlayerQuitEvent $ev){
		$this->trace($ev);
	}
	public function onPluginDisableEvent(PluginDisableEvent $ev){
		$this->trace($ev);
	}
	public function onPluginEnableEvent(PluginEnableEvent $ev){
		$this->trace($ev);
	}
	public function onDataPacketReceiveEvent(DataPacketReceiveEvent $ev){
		$this->trace($ev);
	}
	public function onDataPacketSendEvent(DataPacketSendEvent $ev){
		$this->trace($ev);
	}
	public function onRemoteServerCommandEvent(RemoteServerCommandEvent $ev){
		$this->trace($ev);
	}
	public function onServerCommandEvent(ServerCommandEvent $ev){
		$this->trace($ev);
	}
	public function onLowMemoryEvent(LowMemoryEvent $ev){
		$this->trace($ev);
	}
	public function onQueryRegenerateEvent(QueryRegenerateEvent $ev){
		$this->trace($ev);
	}

	public function checkEvent($evname){
		switch(strtolower($evname)){
		case "blockformevent":
			return ["BlockFormEvent"];
		case "blockgrowevent":
			return ["BlockGrowEvent"];
		case "blockplaceevent":
			return ["BlockPlaceEvent"];
		case "blockspreadevent":
			return ["BlockSpreadEvent"];
		case "blockupdateevent":
			return ["BlockUpdateEvent"];
		case "leavesdecayevent":
			return ["LeavesDecayEvent"];
		case "signchangeevent":
			return ["SignChangeEvent"];
		case "blockbreakevent":
			return ["BlockBreakEvent"];
		case "entityarmorchangeevent":
			return ["EntityArmorChangeEvent"];
		case "entityblockchangeevent":
			return ["EntityBlockChangeEvent"];
		case "entitycombustbyblockevent":
			return ["EntityCombustByBlockEvent"];
		case "entitycombustbyentityevent":
			return ["EntityCombustByEntityEvent"];
		case "entitycombustevent":
			return ["EntityCombustEvent"];
		case "entitydamagebyblockevent":
			return ["EntityDamageByBlockEvent"];
		case "entitydamagebychildentityevent":
			return ["EntityDamageByChildEntityEvent"];
		case "entitydeathevent":
			return ["EntityDeathEvent"];
		case "entitydespawnevent":
			return ["EntityDespawnEvent"];
		case "entityexplodeevent":
			return ["EntityExplodeEvent"];
		case "entityinventorychangeevent":
			return ["EntityInventoryChangeEvent"];
		case "entitylevelchangeevent":
			return ["EntityLevelChangeEvent"];
		case "entitymotionevent":
			return ["EntityMotionEvent"];
		case "entityregainhealthevent":
			return ["EntityRegainHealthEvent"];
		case "entityshootbowevent":
			return ["EntityShootBowEvent"];
		case "entityspawnevent":
			return ["EntitySpawnEvent"];
		case "entityteleportevent":
			return ["EntityTeleportEvent"];
		case "explosionprimeevent":
			return ["ExplosionPrimeEvent"];
		case "itemdespawnevent":
			return ["ItemDespawnEvent"];
		case "itemspawnevent":
			return ["ItemSpawnEvent"];
		case "projectilehitevent":
			return ["ProjectileHitEvent"];
		case "projectilelaunchevent":
			return ["ProjectileLaunchEvent"];
		case "entitydamagebyentityevent":
			return ["EntityDamageByEntityEvent"];
		case "entitydamageevent":
			return ["EntityDamageEvent"];
		case "craftitemevent":
			return ["CraftItemEvent"];
		case "furnaceburnevent":
			return ["FurnaceBurnEvent"];
		case "furnacesmeltevent":
			return ["FurnaceSmeltEvent"];
		case "inventorycloseevent":
			return ["InventoryCloseEvent"];
		case "inventoryopenevent":
			return ["InventoryOpenEvent"];
		case "inventorypickuparrowevent":
			return ["InventoryPickupArrowEvent"];
		case "inventorypickupitemevent":
			return ["InventoryPickupItemEvent"];
		case "inventorytransactionevent":
			return ["InventoryTransactionEvent"];
		case "chunkloadevent":
			return ["ChunkLoadEvent"];
		case "chunkpopulateevent":
			return ["ChunkPopulateEvent"];
		case "chunkunloadevent":
			return ["ChunkUnloadEvent"];
		case "levelinitevent":
			return ["LevelInitEvent"];
		case "levelloadevent":
			return ["LevelLoadEvent"];
		case "levelsaveevent":
			return ["LevelSaveEvent"];
		case "levelunloadevent":
			return ["LevelUnloadEvent"];
		case "spawnchangeevent":
			return ["SpawnChangeEvent"];
		case "playerachievementawardedevent":
			return ["PlayerAchievementAwardedEvent"];
		case "playeranimationevent":
			return ["PlayerAnimationEvent"];
		case "playerbedenterevent":
			return ["PlayerBedEnterEvent"];
		case "playerbedleaveevent":
			return ["PlayerBedLeaveEvent"];
		case "playerbucketemptyevent":
			return ["PlayerBucketEmptyEvent"];
		case "playerbucketfillevent":
			return ["PlayerBucketFillEvent"];
		case "playercommandpreprocessevent":
			return ["PlayerCommandPreprocessEvent"];
		case "playercreationevent":
			return ["PlayerCreationEvent"];
		case "playerdropitemevent":
			return ["PlayerDropItemEvent"];
		case "playergamemodechangeevent":
			return ["PlayerGameModeChangeEvent"];
		case "playeritemconsumeevent":
			return ["PlayerItemConsumeEvent"];
		case "playeritemheldevent":
			return ["PlayerItemHeldEvent"];
		case "playerkickevent":
			return ["PlayerKickEvent"];
		case "playerloginevent":
			return ["PlayerLoginEvent"];
		case "playermoveevent":
			return ["PlayerMoveEvent"];
		case "playerpreloginevent":
			return ["PlayerPreLoginEvent"];
		case "playerrespawnevent":
			return ["PlayerRespawnEvent"];
		case "playerdeathevent":
			return ["PlayerDeathEvent"];
		case "playerinteractevent":
			return ["PlayerInteractEvent"];
		case "playerjoinevent":
			return ["PlayerJoinEvent"];
		case "playerquitevent":
			return ["PlayerQuitEvent"];
		case "plugindisableevent":
			return ["PluginDisableEvent"];
		case "pluginenableevent":
			return ["PluginEnableEvent"];
		case "datapacketreceiveevent":
			return ["DataPacketReceiveEvent"];
		case "datapacketsendevent":
			return ["DataPacketSendEvent"];
		case "remoteservercommandevent":
			return ["RemoteServerCommandEvent"];
		case "servercommandevent":
			return ["ServerCommandEvent"];
		case "lowmemoryevent":
			return ["LowMemoryEvent"];
		case "queryregenerateevent":
			return ["QueryRegenerateEvent"];
		case "block":
			return ["BlockFormEvent", "BlockGrowEvent", "BlockPlaceEvent", "BlockSpreadEvent", "BlockUpdateEvent", "LeavesDecayEvent", "SignChangeEvent", "BlockBreakEvent"];
		case "entity":
			return ["EntityArmorChangeEvent", "EntityBlockChangeEvent", "EntityCombustByBlockEvent", "EntityCombustByEntityEvent", "EntityCombustEvent", "EntityDamageByBlockEvent", "EntityDamageByChildEntityEvent", "EntityDeathEvent", "EntityDespawnEvent", "EntityExplodeEvent", "EntityInventoryChangeEvent", "EntityLevelChangeEvent", "EntityMotionEvent", "EntityRegainHealthEvent", "EntityShootBowEvent", "EntitySpawnEvent", "EntityTeleportEvent", "ExplosionPrimeEvent", "ItemDespawnEvent", "ItemSpawnEvent", "ProjectileHitEvent", "ProjectileLaunchEvent", "EntityDamageByEntityEvent", "EntityDamageEvent"];
		case "inventory":
			return ["CraftItemEvent", "FurnaceBurnEvent", "FurnaceSmeltEvent", "InventoryCloseEvent", "InventoryOpenEvent", "InventoryPickupArrowEvent", "InventoryPickupItemEvent", "InventoryTransactionEvent"];
		case "level":
			return ["ChunkLoadEvent", "ChunkPopulateEvent", "ChunkUnloadEvent", "LevelInitEvent", "LevelLoadEvent", "LevelSaveEvent", "LevelUnloadEvent", "SpawnChangeEvent"];
		case "player":
			return ["PlayerAchievementAwardedEvent", "PlayerAnimationEvent", "PlayerBedEnterEvent", "PlayerBedLeaveEvent", "PlayerBucketEmptyEvent", "PlayerBucketFillEvent", "PlayerCommandPreprocessEvent", "PlayerCreationEvent", "PlayerDropItemEvent", "PlayerGameModeChangeEvent", "PlayerItemConsumeEvent", "PlayerItemHeldEvent", "PlayerKickEvent", "PlayerLoginEvent", "PlayerMoveEvent", "PlayerPreLoginEvent", "PlayerRespawnEvent", "PlayerDeathEvent", "PlayerInteractEvent", "PlayerJoinEvent", "PlayerQuitEvent"];
		case "plugin":
			return ["PluginDisableEvent", "PluginEnableEvent"];
		case "server":
			return ["DataPacketReceiveEvent", "DataPacketSendEvent", "RemoteServerCommandEvent", "ServerCommandEvent", "LowMemoryEvent", "QueryRegenerateEvent"];
		case "blockgrowevent":
			return ["BlockFormEvent"];
		case "blockevent":
			return ["BlockGrowEvent", "BlockPlaceEvent", "BlockUpdateEvent", "LeavesDecayEvent", "SignChangeEvent", "BlockBreakEvent", "FurnaceBurnEvent", "FurnaceSmeltEvent"];
		case "blockformevent":
			return ["BlockSpreadEvent"];
		case "entityevent":
			return ["EntityArmorChangeEvent", "EntityBlockChangeEvent", "EntityCombustEvent", "EntityDeathEvent", "EntityDespawnEvent", "EntityExplodeEvent", "EntityInventoryChangeEvent", "EntityLevelChangeEvent", "EntityMotionEvent", "EntityRegainHealthEvent", "EntityShootBowEvent", "EntitySpawnEvent", "EntityTeleportEvent", "ExplosionPrimeEvent", "ItemDespawnEvent", "ItemSpawnEvent", "ProjectileHitEvent", "ProjectileLaunchEvent", "EntityDamageEvent"];
		case "entitycombustevent":
			return ["EntityCombustByBlockEvent", "EntityCombustByEntityEvent"];
		case "entitydamageevent":
			return ["EntityDamageByBlockEvent", "EntityDamageByEntityEvent"];
		case "entitydamagebyentityevent":
			return ["EntityDamageByChildEntityEvent"];
		case "event":
			return ["CraftItemEvent", "InventoryTransactionEvent", "PlayerCreationEvent"];
		case "inventoryevent":
			return ["InventoryCloseEvent", "InventoryOpenEvent", "InventoryPickupArrowEvent", "InventoryPickupItemEvent"];
		case "chunkevent":
			return ["ChunkLoadEvent", "ChunkPopulateEvent", "ChunkUnloadEvent"];
		case "levelevent":
			return ["LevelInitEvent", "LevelLoadEvent", "LevelSaveEvent", "LevelUnloadEvent", "SpawnChangeEvent"];
		case "playerevent":
			return ["PlayerAchievementAwardedEvent", "PlayerAnimationEvent", "PlayerBedEnterEvent", "PlayerBedLeaveEvent", "PlayerCommandPreprocessEvent", "PlayerDropItemEvent", "PlayerGameModeChangeEvent", "PlayerItemConsumeEvent", "PlayerItemHeldEvent", "PlayerKickEvent", "PlayerLoginEvent", "PlayerMoveEvent", "PlayerPreLoginEvent", "PlayerRespawnEvent", "PlayerInteractEvent", "PlayerJoinEvent", "PlayerQuitEvent"];
		case "playerbucketevent":
			return ["PlayerBucketEmptyEvent", "PlayerBucketFillEvent"];
		case "entitydeathevent":
			return ["PlayerDeathEvent"];
		case "pluginevent":
			return ["PluginDisableEvent", "PluginEnableEvent"];
		case "serverevent":
			return ["DataPacketReceiveEvent", "DataPacketSendEvent", "ServerCommandEvent", "LowMemoryEvent", "QueryRegenerateEvent"];
		case "servercommandevent":
			return ["RemoteServerCommandEvent"];
		default:
			return null;
		}
	}

	public function getList(){
		return [
			"types" => ["block", "entity", "inventory", "level", "player", "plugin", "server"],
			"classes" => ["BlockGrowEvent", "BlockEvent", "BlockFormEvent", "EntityEvent", "EntityCombustEvent", "EntityDamageEvent", "EntityDamageByEntityEvent", "Event", "InventoryEvent", "ChunkEvent", "LevelEvent", "PlayerEvent", "PlayerBucketEvent", "EntityDeathEvent", "PluginEvent", "ServerEvent", "ServerCommandEvent"],
		];
	}
  //<!-- end-methods -->
}
