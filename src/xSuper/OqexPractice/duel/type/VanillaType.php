<?php

namespace xSuper\OqexPractice\duel\type;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use xSuper\OqexPractice\duel\Duel;
use xSuper\OqexPractice\duel\queue\QueueManager;

class VanillaType extends Type
{
    public function getMenuItem(bool $ranked = false): Item
    {
        return VanillaBlocks::ENCHANTING_TABLE()->asItem()->setCustomName($this->parseName([
            '§r§7Vanilla is a game mode where you are given the',
            '§r§7normal NoDebuff kit, but without enchantments.',
            '§r§7Put your NoDebuff skills to the test!',
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
        return 'Vanilla';
    }

    public function getName(): string
    {
        return 'Vanilla';
    }

	/** @param list<string> $data */
    public function parseName(array $data): string
    {
        $s = '§r§l§6Vanilla';
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