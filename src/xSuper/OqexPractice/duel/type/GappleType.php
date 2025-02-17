<?php

namespace xSuper\OqexPractice\duel\type;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\duel\Duel;
use xSuper\OqexPractice\duel\queue\QueueManager;

class GappleType extends Type
{
    public function getMenuItem(bool $ranked = false): Item
    {
        return VanillaItems::GOLDEN_APPLE()->setCustomName($this->parseName([
            '§r§7Gapple is a game mode where you need to',
            '§r§7fight and time your golden apples to give',
            '§r§7yourself the upper hand in battle.',
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
        return 'Gapple';
    }

    public function getName(): string
    {
        return 'Gapple';
    }

	/** @param list<string> $data */
    public function parseName(array $data): string
    {
        $s = '§r§l§6Gapple';
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