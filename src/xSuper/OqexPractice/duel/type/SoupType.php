<?php

namespace xSuper\OqexPractice\duel\type;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\duel\Duel;
use xSuper\OqexPractice\duel\queue\QueueManager;

class SoupType extends Type
{
    public function getMenuItem(bool $ranked = false): Item
    {
        return VanillaItems::MUSHROOM_STEW()->setCustomName($this->parseName([
            '§r§7In soup you need to use mushroom stew',
            '§r§7to heal yourself and prevent yourself',
            '§r§7from dying!',
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
        return 'Soup';
    }

    public function getName(): string
    {
        return 'Soup';
    }

	/** @param list<string> $data */
    public function parseName(array $data): string
    {
        $s = '§r§l§6Soup';
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