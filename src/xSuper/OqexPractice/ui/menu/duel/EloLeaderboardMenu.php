<?php

namespace xSuper\OqexPractice\ui\menu\duel;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use xSuper\OqexPractice\duel\type\Types;
use xSuper\OqexPractice\duel\utils\Leaderboard;
use xSuper\OqexPractice\duel\utils\LeaderboardIds;
use xSuper\OqexPractice\ui\menu\CustomInventory;

class EloLeaderboardMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(45);
    }

    public function getTitle(Player $player): string
    {
        return 'Ranked Leaderboards';
    }

    public function render(Player $player): void
    {
        $contents = [];
        $ladders = [
            [LeaderboardIds::NO_DEBUFF_ELO, 10, Types::NO_DEBUFF()->getMenuItem(true)->setCustomName('§r§l§6NoDebuff §r§7| §fTop 10')],
            [LeaderboardIds::DEBUFF_ELO, 11, Types::DEBUFF()->getMenuItem(true)->setCustomName('§r§l§6Debuff §r§7| §fTop 10')],
            [LeaderboardIds::GAPPLE_ELO, 12,Types::GAPPLE()->getMenuItem(true)->setCustomName('§r§l§6Gapple §r§7| §fTop 10')],
            [LeaderboardIds::BUILD_UHC_ELO, 13, Types::BUILD_UHC()->getMenuItem(true)->setCustomName('§r§l§6BuildUHC §r§7| §fTop 10')],
            [LeaderboardIds::COMBO_ELO, 14, Types::COMBO()->getMenuItem(true)->setCustomName('§r§l§6Combo §r§7| §fTop 10')],
            [LeaderboardIds::SUMO_ELO, 15, Types::SUMO()->getMenuItem(true)->setCustomName('§r§l§6Sumo §r§7| §fTop 10')],
            [LeaderboardIds::VANILLA_ELO, 16, Types::VANILLA()->getMenuItem(true)->setCustomName('§r§l§6Vanilla §r§7| §fTop 10')],
            [LeaderboardIds::ARCHER_ELO, 19, Types::ARCHER()->getMenuItem(true)->setCustomName('§r§l§6Archer §r§7| §fTop 10')],
            [LeaderboardIds::SOUP_ELO, 20, Types::SOUP()->getMenuItem(true)->setCustomName('§r§l§6Soup §r§7| §fTop 10')],
            [LeaderboardIds::BRIDGE_ELO, 21, Types::BRIDGE()->getMenuItem(true)->setCustomName('§r§l§6Bridge §r§7| §fTop 10')],
            [LeaderboardIds::AVERAGE_ELO, 41, VanillaBlocks::BEACON()->asItem()->setCustomName('§r§l§6Average Elo §r§7| §fTop 10')]
        ];

        foreach ($ladders as $ladder) {
            $id = $ladder[0];
            $slot = $ladder[1];
            $item = $ladder[2];

            $d = Leaderboard::getLeaderboard($id)->getData();

            if ($id === LeaderboardIds::NO_DEBUFF_ELO || $id === LeaderboardIds::DEBUFF_ELO) $lore = [];
            else $lore = ['§r'];

            $c = 0;
            foreach ($d as $i => $data) {
                $x = $i + 1;
                if ($x > 10) break;

                $color = match ($x) {
                    1 => '§6',
                    2 => '§e',
                    3 => '§f',
                    default => '§7'
                };

                $lore[] = ' §r§8- ' . $color . $x . '. ' . $data[0] . ': §e' . $data[1];
                $c++;
            }

            if ($c !== 5) {
                for ($x = $c + 1; $x <= 10; $x++) {
                    $color = match ($x) {
                        1 => '§6',
                        2 => '§e',
                        3 => '§f',
                        default => '§7'
                    };
                    $lore[] = ' §r§8- ' . $color . $x . '. Unknown: §e?';
                }
            }

            $lore[] = '§r';

            $item->setLore($lore);
            $contents[$slot] = $item;
        }

        $contents[39] = VanillaItems::DIAMOND()->setCustomName('§r§l§6Your Stats')->setLore([
            '§r',
            '§r§7Open this menu to check out your',
            '§r§7personal stats, modify your settings,',
            '§r§7and edit your cosmetics.',
            '§r',
            '§r§l§aClick §r§7here to check your stats'
        ]);
        $this->getMenu($player)->getInventory()->setContents($contents);
    }
}