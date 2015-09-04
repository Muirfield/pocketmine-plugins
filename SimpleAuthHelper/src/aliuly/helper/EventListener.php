<?php

/*
 * Copied from SimpleAuth plugin for PocketMine-MP
 * Copyright (C) 2014 PocketMine Team <https://github.com/PocketMine/SimpleAuth>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
*/
namespace aliuly\helper;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\Player;
use pocketmine\inventory\PlayerInventory;
use aliuly\helper\Main as HelperPlugin;


class EventListener implements Listener{
	/** @var SimpleAuth */
	private $auth;
	private $owner;

	public function __construct(HelperPlugin $owner){
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$this->auth = $owner->auth;
		$owner->getServer()->getPluginManager()->registerEvents($this, $owner);
	}

	/**
	 * @priority LOWEST
	 */
	public function onCrafting(CraftItemEvent $event){
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		foreach ($event->getTransaction()->getInventories() as $inv) {
			echo __METHOD__.",".__LINE__."\n";//##DEBUG
			if (($inv instanceof PlayerInventory)) continue;
			echo __METHOD__.",".__LINE__."\n";//##DEBUG
			$player = $inv->getHolder();
			if (!$this->auth->isPlayerAuthenticated($inv->getHolder())) {
				echo __METHOD__.",".__LINE__."\n";//##DEBUG
				$event->setCancelled(true);
				return;
			}
		}
	}

	/**
	 * @param PlayerMoveEvent $event
	 *
	 * @priority LOWEST
	 */
	public function onPlayerMove(PlayerMoveEvent $event){
		if(!$this->auth->isPlayerAuthenticated($event->getPlayer())){
			if(!$event->getPlayer()->hasPermission("simpleauth.move")){
				$event->setCancelled(true);
				$event->getPlayer()->onGround = true;
			}
		}
	}

	/**
	 * @param PlayerInteractEvent $event
	 *
	 * @priority HIGHEST
	 */
	public function onPlayerInteract(PlayerInteractEvent $event){
		echo  __METHOD__.",".__LINE__."\n";//##DEBUG

		if(!$this->auth->isPlayerAuthenticated($event->getPlayer())){
			echo  __METHOD__.",".__LINE__."\n";//##DEBUG
			$event->setCancelled(true);
		}
	}

	/**
	 * @param PlayerDropItemEvent $event
	 *
	 * @priority HIGHEST
	 */
	public function onPlayerDropItem(PlayerDropItemEvent $event){
		if(!$this->auth->isPlayerAuthenticated($event->getPlayer())){
			$event->setCancelled(true);
		}
	}

	/**
	 * @param PlayerItemConsumeEvent $event
	 *
	 * @priority LOWEST
	 */
	public function onPlayerItemConsume(PlayerItemConsumeEvent $event){
		if(!$this->auth->isPlayerAuthenticated($event->getPlayer())){
			$event->setCancelled(true);
		}
	}

	/**
	 * @param EntityDamageEvent $event
	 *
	 * @priority LOWEST
	 */
	public function onEntityDamage(EntityDamageEvent $event){
		echo  __METHOD__.",".__LINE__."\n";//##DEBUG
		if($event->getEntity() instanceof Player and !$this->auth->isPlayerAuthenticated($event->getEntity())){
			echo  __METHOD__.",".__LINE__."\n";//##DEBUG
			$event->setCancelled(true);
			return;
		}
		// Also check if we are inflicting damage to others
		if(!($event instanceof EntityDamageByEntityEvent)) return;
		echo  __METHOD__.",".__LINE__."\n";//##DEBUG
		$giver = $event->getDamager();
		if (!($giver instanceof Player)) return;
		if (!$this->auth->isPlayerAuthenticated($giver)) {
			echo  __METHOD__.",".__LINE__."\n";//##DEBUG
			$event->setCancelled(true);
			return;
		}
	}

	/**
	 * @param BlockBreakEvent $event
	 *
	 * @priority LOWEST
	 */
	public function onBlockBreak(BlockBreakEvent $event){
		if($event->getPlayer() instanceof Player and !$this->auth->isPlayerAuthenticated($event->getPlayer())){
			$event->setCancelled(true);
		}
	}

	/**
	 * @param BlockPlaceEvent $event
	 *
	 * @priority LOWEST
	 */
	public function onBlockPlace(BlockPlaceEvent $event){
		if($event->getPlayer() instanceof Player and !$this->auth->isPlayerAuthenticated($event->getPlayer())){
			$event->setCancelled(true);
		}
	}

	/**
	 * @param InventoryOpenEvent $event
	 *
	 * @priority LOWEST
	 */
	public function onInventoryOpen(InventoryOpenEvent $event){
		if(!$this->auth->isPlayerAuthenticated($event->getPlayer())){
			$event->setCancelled(true);
		}
	}

	/**
	 * @param InventoryPickupItemEvent $event
	 *
	 * @priority LOWEST
	 */
	public function onPickupItem(InventoryPickupItemEvent $event){
		$player = $event->getInventory()->getHolder();
		if($player instanceof Player and !$this->auth->isPlayerAuthenticated($player)){
			$event->setCancelled(true);
		}
	}
}
