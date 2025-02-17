<?php

namespace xSuper\OqexPractice\duel\type;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\duel\Duel;
use xSuper\OqexPractice\duel\queue\QueueManager;

class SurvivalGamesType extends Type
{
    public function getMenuItem(bool $ranked = false): Item
    {
        return VanillaItems::FLINT_AND_STEEL()->setCustomName($this->parseName([
            '§r§7More commonly know as Hunger Games, you race to the middle',
            '§r§7of the arena and loot the chests, finding items to fight',
            '§r§7your opponent with. And may the odds be ever in your favor!',
            '§r',
            ' §r§8- §r§7In queue: §e' . count(QueueManager::getInstance()->getQueuesByType($this, $ranked)),
            ' §r§8- §r§7Fighting: §e' . count(Duel::getDuelsByType($this, $ranked)) * 2,
            ' §r',
            '§r§l§aClick §r§7to queue.'
        ]));
    }

    public function getFormImage(): ?string
    {
        return '';
    }

    public function getKit(): string
    {
        return 'SurvivalGames';
    }

    public function getName(): string
    {
        return 'Survival Games';
    }

	/** @param list<string> $data */
    public function parseName(array $data): string
    {
        $s = '§r§l§6Survival Games';
        foreach ($data as $line) {
            $s .= "\n" . $line;
        }
        return $s;
    }

    public function fallDamage(): bool
    {
        return false;
    }

    public function getBreakableBlocks(): array
    {
        return [];
    }

    public function getPlaceableBlocks(): array
    {
        return [];
    }
}