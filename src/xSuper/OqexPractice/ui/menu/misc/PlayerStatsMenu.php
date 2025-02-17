<?php

namespace xSuper\OqexPractice\ui\menu\misc;

use muqsit\customsizedinvmenu\CustomSizedInvMenu;
use muqsit\customsizedinvmenu\libs\muqsit\invmenu\InvMenu;
use muqsit\customsizedinvmenu\libs\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use Ramsey\Uuid\Uuid;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\menu\CustomInventory;

class PlayerStatsMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(9);
    }

    public function getTitle(Player $player): string
    {
        $target = $this->getData($player)['target'] ?? '??';
        return "$target's Stats";
    }

    public function render(Player $player): void
    {
        $stats = $this->getData($player)['stats'] ?? [];
        foreach (['daily', 'weekly', 'monthly', 'lifetime'] as $time) {
            $kills = $stats[$time]['kills'];
            $deaths = $stats[$time]['deaths'];

            if ($deaths === 0) $ratio[$time] = $kills;
            else $ratio[$time] = $kills / $deaths;
        }

        $this->getMenu($player)->getInventory()->setContents([
            0 => VanillaItems::EXPERIENCE_BOTTLE()->setCustomName('§r§l§6Kills')->setLore([
                '§r§8 - §7Daily: §a' . $stats['daily']['kills'],
                '§r§8 - §7Weekly: §e' . $stats['weekly']['kills'],
                '§r§8 - §7Monthly: §c' . $stats['monthly']['kills'],
                '§r§8 - §7Lifetime: §4' . $stats['lifetime']['kills']
            ]),
            2 => VanillaItems::EXPERIENCE_BOTTLE()->setCustomName('§r§l§6K.D Ratio')->setLore([
                '§r§8 - §7Daily: §a' . $ratio['daily'],
                '§r§8 - §7Weekly: §e' . $ratio['weekly'],
                '§r§8 - §7Monthly: §c' . $ratio['monthly'],
                '§r§8 - §7Lifetime: §4' . $ratio['lifetime'],
            ]),
            4 => VanillaItems::EXPERIENCE_BOTTLE()->setCustomName('§r§l§6Deaths')->setLore([
                '§r§8 - §7Daily: §a' . $stats['daily']['deaths'],
                '§r§8 - §7Weekly: §e' . $stats['weekly']['deaths'],
                '§r§8 - §7Monthly: §c' . $stats['monthly']['deaths'],
                '§r§8 - §7Lifetime: §4' . $stats['lifetime']['deaths']
            ]),
        ]);

    }
}