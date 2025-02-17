<?php

namespace xSuper\OqexPractice\duel\type;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\duel\Duel;
use xSuper\OqexPractice\duel\queue\QueueManager;

class TheBridgeType extends Type
{
    public function getMenuItem(bool $ranked = false): Item
    {
        return VanillaItems::STICK()->setCustomName($this->parseName([
            '§r§7The Bridge you must attempt to get yourself in',
            '§r§7your opponents goal 3 times to win the match.',
            '§r§7Defend your goal at all costs!',
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
        return 'TheBridge';
    }

    public function getName(): string
    {
        return 'Bridge';
    }

	/** @param list<string> $data */
    public function parseName(array $data): string
    {
        $s = '§r§l§6The Bridge';
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
        return [
            VanillaBlocks::SANDSTONE()
        ];
    }

    public function getPlaceableBlocks(): array
    {
        return [
            VanillaBlocks::SANDSTONE()
        ];
    }
}