<?php

namespace PerWorldGamemode;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;

class PlayerEventListener implements Listener {

    private $plugin;

    public function __construct(PerWorldGamemode $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @param EntityLevelChangeEvent $event
     *
     * @priority NORMAL
     * @ignoreCancelled false
     */
    public function onLevelChange(EntityLevelChangeEvent $event) {
        $entity = $event->getEntity();
        if ($entity instanceof Player) {
            $this->plugin->checkPlayer($entity);
        }
    }

    /**
     * @param PlayerRespawnEvent $event
     *
     * @priority NORMAL
     * @ignoreCancelled false
     */
    public function onRespawn(PlayerRespawnEvent $event) {
        $this->plugin->checkPlayer($event->getPlayer());
    }

    /**
     * @param PlayerQuitEvent $event
     *
     * @priority NORMAL
     * @ignoreCancelled true
     */
    public function onQuit(PlayerQuitEvent $event) {
        $this->plugin->checkPlayer($event->getPlayer());
    }

}
