<?php

namespace xSuper\OqexPractice\duel\type;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\duel\Duel;
use xSuper\OqexPractice\duel\queue\QueueManager;

class BuildUHCType extends Type
{
    public function getMenuItem(bool $ranked = false): Item
    {
        return VanillaItems::LAVA_BUCKET()->setCustomName($this->parseName([
            '§r§7BuildUHC is a game mode where you are given',
            '§r§7lava, water, and blocks. You have to use these',
            '§r§7items to give yourself an advantage in battle.',
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
        return 'BuildUHC';
    }

    public function getName(): string
    {
        return 'BuildUHC';
    }

	/** @param list<string> $data */
    public function parseName(array $data): string
    {
        $s = '§r§l§6BuildUHC';
        foreach ($data as $line) {
            $s .= "\n" . $line;
        }
        return $s;
    }

    public function fallDamage(): bool
    {
        return true;
    }

    public function getBreakableBlocks(): array
    {
        return [
            VanillaBlocks::COBBLESTONE(),
            VanillaBlocks::OAK_PLANKS()
        ];
    }

    public function getPlaceableBlocks(): array
    {
        return [
            VanillaBlocks::OAK_PLANKS(),
            VanillaBlocks::COBBLESTONE()
        ];
    }
}