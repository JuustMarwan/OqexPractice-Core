<?php

namespace xSuper\OqexPractice\player\data;

use pocketmine\utils\TextFormat;
use xSuper\OqexPractice\player\PracticePlayer;

class RankMap {
    
    const RANKS = [
        'guest',
        'ultra',
        'elite',
        'nitro',
        'media',
        'famous',
        'build',
        'event_team',
        'helper',
        'moderator',
        'sr_moderator',
        'administrator',
        'manager',
        'developer',
        'owner'
    ];
    
    public static function permissionMap(string $rank): int {
        return match ($rank) {
            'guest' => 0,
            'nitro' => 1,
            'ultra' => 2,
            'elite' => 3,
            'media' => 4,
            'famous' => 5,
            'build' => 6,
            'event_team' => 7,
            'helper' => 8,
            'moderator' => 9,
            'sr_moderator' => 10,
            'administrator' => 11,
            'manager' => 12,
            'developer' => 13,
            'owner' => 14,
            default => throw new \InvalidArgumentException("Unknown rank $rank")
        };
    }
    
    public static function formatTag(PracticePlayer $player): void {

        $prefix = self::getRankTag($player->getData()->getHighestRank());

        $base = match ($player->getData()->getHighestRank()) {
            default => '§r§f' . $player->getName(),
            'nitro' => $prefix . ' §r§d' . $player->getName(),
            'ultra' => $prefix . ' §r§a' . $player->getName(),
            'elite' => $prefix . ' §r§g' . $player->getName(),
            'media' => $prefix . ' §r§3' . $player->getName(),
            'famous' => $prefix . ' §r§3' . $player->getName(),
            'build' => $prefix . ' §r§2' . $player->getName(),
            'event_team' => $prefix . ' §r§g' . $player->getName(),
            'helper' => $prefix . ' §r§l§e' . $player->getName(),
            'moderator' => $prefix . ' §r§l§d' . $player->getName(),
            'sr_moderator' => $prefix . ' §r§l§d' . $player->getName(),
            'administrator' => $prefix . ' §r§l§c' . $player->getName(),
            'manager' => $prefix . ' §r§l§4' . $player->getName(),
            'developer' => $prefix . ' §r§l§1' . $player->getName(),
            'owner' => $prefix . ' §r§l§3' . $player->getName()
        };

        $player->setNameTag($base . "\n" . $player->getData()->getInfo()->getUnicode() . ' ' . $player->getData()->getInfo()->getDeviceOS() . ' - ' . $player->getData()->getInfo()->getVersion());
    }

    public static function formatChat(PracticePlayer $player, string $message): string
    {
        $t = $player->getData()->getCosmetics()->getTag();
        if ($t !== '') $t .= ' ';

        $message = '§r' . $player->getData()->getCosmetics()->getChatColor() . TextFormat::clean($message);

        $prefix = self::getRankTag($player->getData()->getHighestRank());

        return match ($player->getData()->getHighestRank()) {
            default => $prefix . $t . '§r§f' . $player->getName() . "§8: §f" . $message,
            'nitro' => $prefix . $t . '§r§d' . $player->getName() . "§8: §f" . $message,
            'ultra' => $prefix . $t . '§r§a' . $player->getName() . "§8: §f" . $message,
            'elite' => $prefix . $t . '§r§g' . $player->getName() . "§8: §f" . $message,
            'build' => $prefix . $t . '§r§2' . $player->getName() . "§8: §f" . $message,
            'media' => $prefix . $t . '§r§3' . $player->getName() . "§8: §f" . $message,
            'famous' => $prefix . $t . '§r§3' . $player->getName() . "§8: §f" . $message,
            'event_team' => $prefix . $t . '§r§g' . $player->getName() . "§8: §f" . $message,
            'developer' => $prefix . $t . '§r§1' . $player->getName() . "§8: §f" . $message,
            'helper' => $prefix . $t . '§r§e' . $player->getName() . "§8: §f" . $message,
            'moderator' => $prefix . $t . '§r§d' . $player->getName() . "§8: §f" . $message,
            'sr_moderator' => $prefix . $t . '§r§d' . $player->getName() . "§8: §f" . $message,
            'administrator' => $prefix . $t . '§r§c' . $player->getName() . "§8: §f" . $message,
            'manager' => $prefix . $t . '§r§4' . $player->getName() . "§8: §f" . $message,
            'owner' => $prefix . $t . '§r§3' . $player->getName() . "§8: §f" . $message,
        };
    }

    public static function getRankTag(string $rank): string
    {
        return match($rank) {
          default => '',
          'nitro' => '',
            'ultra' => '',
            'elite' => '',
            'media' => '',
            'famous' => '',
            'build' =>  '',
            'event_team' => '',
            'developer' => '',
            'helper' => '',
            'moderator' => '',
            'sr_moderator' => '',
            'administrator' => '',
            'manager' => '',
            'owner' => ''
        };
    }
}