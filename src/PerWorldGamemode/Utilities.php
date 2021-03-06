<?php

namespace PerWorldGamemode;

use pocketmine\utils\Config;
use pocketmine\Server;

class Utilities {

    public static function getWorldGamemode(Config $config, $world) {
        return (isset($config->get(PerWorldGamemode::CONFIG_WORLDS)[$world])) ? $config->get(PerWorldGamemode::CONFIG_WORLDS)[$world] : Server::getInstance()->getDefaultGamemode();
    }

    public static function setWorldGamemode(Config $config, $world, $gamemode) {
        $worlds = $config->get(PerWorldGamemode::CONFIG_WORLDS);
        $worlds[$world] = $gamemode;
        $config->set(PerWorldGamemode::CONFIG_WORLDS, $worlds);
        $config->save();
    }

    public static function unsetWorldGamemode(Config $config, $world) {
        $worlds = $config->get(PerWorldGamemode::CONFIG_WORLDS);
        unset($worlds[$world]);
        $config->set(PerWorldGamemode::CONFIG_WORLDS, $worlds);
        $config->save();
    }

    public static function removeprop(Config $config, $arrname, $value) {
        if (in_array(strtolower($value), array_map('strtolower', $conf = $config->get($arrname)))) {
            $config->set($arrname, array_diff($conf, array($value)));
            $config->save();
            return true;
        } else {
            return false;
        }
    }

    public static function addprop(Config $config, $arrname, $value) {
        if (!in_array(strtolower($value), array_map('strtolower', $conf = $config->get($arrname)))) {
            $arr = $config->get($arrname);
            $arr[] = $value;
            $config->set($arrname, $arr);
            $config->save();
            return true;
        } else {
            return false;
        }
    }

}