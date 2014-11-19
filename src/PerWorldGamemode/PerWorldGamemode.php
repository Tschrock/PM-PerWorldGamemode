<?php

namespace PerWorldGamemode;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\Player;

class PerWorldGamemode extends PluginBase {

    private $utilities;

    const CONFIG_EXCLUDED = "excludedPlayers";
    const CONFIG_WORLDS = "worlds";

    public function __construct() {
        $this->utilities = new Utilities($this);
    }

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents(new PlayerEventListener($this), $this);

        $this->saveDefaultConfig();
        $this->reloadConfig();
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        switch ($command->getName()) {
            case "pwgm":
                switch (array_shift($args)) {
                    case "set":
                        $sender->sendMessage($this->setWorldCmd($sender, $args));
                        return true;
                    case "exclude":
                        $sender->sendMessage($this->excludePlayerCmd($sender, $args));
                        return true;
                    case "include":
                        $sender->sendMessage($this->includePlayerCmd($sender, $args));
                        return true;
                    /* case "countdowntest":
                      $this->startGamemodeCountdown($sender, 0);
                      return true; */
                    default:
                        $sender->sendMessage("Hello " . $sender->getName() . "!");
                        return true;
                }
            default:
                return false;
        }
    }

    public function checkAllPlayers($world){
        if (is_string($world)) {
            $world = $this->getServer()->getLevelByName($world);
        }
        if (!($world instanceof Level)) {
            return false;
        }
        
        $players = $world->getPlayers();
        
        foreach ($players as $player) {
            $this->checkPlayer($player);
        }
        
    }
    
    public function checkPlayer($player) {

        if (is_string($player)) {
            $player = $this->getServer()->getPlayerExact($player);
        }
        
        if (!($player instanceof Player)) {
            return false;
        }
        

        $world = $player->getLevel()->getName();

        $isExcluded = in_array(strtolower($player->getName()), array_map('strtolower', $this->getConfig()->get(PerWorldGamemode::CONFIG_EXCLUDED)));
        $worldGamemode = Utilities::getWorldGamemode($this->getConfig(), $world);
        
        if ($worldGamemode == "none") {
            $gamemodeTo = false;
        } else if (($gamemodeTo = Server::getGamemodeFromString($worldGamemode)) == -1) {
            $this->getLogger()->warning($worldGamemode . ' isn\'t a valid gamemode! (PerWorldGamemode/config.yml) Using default gamemode instead!');
            $gamemodeTo = Server::getDefaultGamemode();
        }
        
        $gamemodeNeedsChanged = $player->getGamemode() !== ($gamemodeTo);
        
        if (!$isExcluded && ($gamemodeTo !== false) && $gamemodeNeedsChanged) {

            $player->setGamemode($gamemodeTo);
        } else {
            return false;
        }
    }

    /**
     *   Command functions
     */
    public function setWorldCmd($sender, $params) {
        if (count($params) == 1) {
            if (($mode = Server::getGamemodeFromString($params[0])) !== -1 && $params[0] != "none") {

                if ($sender instanceof Player) {
                    $world = $sender->getLevel()->getName();
                } else {
                    return "You must put a world!";
                }
            } else {
                return "You must put a correct gamemode! (survival, creative, view, or adventure)";
            }
        } elseif (count($params) == 2) {

            if (($mode = Server::getGamemodeFromString($params[0])) !== -1 && $params[0] != "none") {

                if ($this->getServer()->getLevel($params[1]) !== null) {
                    $world = $params[1];
                } else {
                    return "You must put a correct world! (World names are case-sensitive)";
                }
            } elseif (($mode = Server::getGamemodeFromString($params[1])) !== -1 && $params[0] != "none") {

                if ($this->getServer()->getLevel($params[0]) !== null) {
                    $world = $params[0];
                } else {
                    return "You must put a correct world! (World names are case-sensitive)";
                }
            } else {
                return "You must put a correct gamemode! (survival, creative, view, or adventure)";
            }
        } else {
            return "Usage: /pwgm set <gamemode> (world)";
        }


        Utilities::setWorldGamemode($this->getConfig(), $world, $mode);
        $this->checkAllPlayers($world);
        return "Set world $world to gamemode $mode.";
    }

    public function excludePlayerCmd($sender, $params) {

        if (is_null($playerpar = array_shift($params))) {
            return "Usage: /pwgm exclude <player>";
        }
        if (null !== $player = $this->getServer()->getPlayer($playerpar)) {
            if (Utilities::addprop($this->getConfig(), PerWorldGamemode::CONFIG_EXCLUDED, $player->getName())) {
                return $player->getName() . " is now excluded.";
            } else {
                return $player->getName() . " is already excluded.";
            }
        } else {
            return "$playerpar is not a player or is not online!";
        }
    }

    public function includePlayerCmd($sender, $params) {

        if (is_null($playerpar = array_shift($params))) {
            return "Usage: /pwgm include <player>";
        }
        if (null !== $player = $this->getServer()->getPlayer($playerpar)) {
            if (Utilities::removeprop($this->getConfig(), PerWorldGamemode::CONFIG_EXCLUDED, $player->getName())) {
                $this->checkPlayer($player);
                return $player->getName() . " is now included.";
                
            } else {
                return $player->getName() . " is already included.";
            }
        } else {
            return "$playerpar is not a player or is not online!";
        }
    }

}
