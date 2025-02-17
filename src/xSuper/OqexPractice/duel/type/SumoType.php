<?php

namespace xSuper\OqexPractice\duel\type;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\duel\Duel;
use xSuper\OqexPractice\duel\queue\QueueManager;

class SumoType extends Type
{
    public function getMenuItem(bool $ranked = false): Item
    {
        return VanillaItems::SUSPICIOUS_STEW()->setCustomName($this->parseName([
            '§r§7You spawn on a small platform with another player',
            '§r§7where you have to knock them off the platform to',
            '§r§7win the fight.',
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
        return 'Sumo';
    }

    public function getName(): string
    {
        return 'Sumo';
    }

	/** @param list<string> $data */
    public function parseName(array $data): string
    {
        $s = '§r§l§6Sumo';
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