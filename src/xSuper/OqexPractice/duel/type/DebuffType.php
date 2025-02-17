<?php

namespace xSuper\OqexPractice\duel\type;

use pocketmine\item\Item;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\duel\Duel;
use xSuper\OqexPractice\duel\queue\QueueManager;

class DebuffType extends Type
{
    public function getMenuItem(bool $ranked = false): Item
    {
        return VanillaItems::SPLASH_POTION()->setType(PotionType::HARMING())->setCustomName($this->parseName([
            '§r§7Debuff is a game mode where you need to',
            '§r§7fight and use the debuff potions given to you',
            '§r§7to cripple your opponent and gain the upper hand.',
        ]))->setLore([
            ' §r§8- §r§7In queue: §e' . count(QueueManager::getInstance()->getQueuesByType($this, $ranked)),
            ' §r§8- §r§7Fighting: §e' . count(Duel::getDuelsByType($this, $ranked)) * 2,
            ' §r',
            '§r§l§aClick §r§7to queue.'
        ]);
    }

    public function getFormImage(): ?string
    {
        return '';
    }

    public function getKit(): string
    {
        return 'Debuff';
    }

    public function getName(): string
    {
        return 'Debuff';
    }

	/** @param list<string> $data */
    public function parseName(array $data): string
    {
        $s = '§r§l§6Debuff';
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