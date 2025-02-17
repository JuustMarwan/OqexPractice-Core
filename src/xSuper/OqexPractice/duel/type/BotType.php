<?php

namespace xSuper\OqexPractice\duel\type;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;

class BotType extends Type
{
    public function getMenuItem(bool $ranked = false): Item
    {
        return VanillaItems::AIR();
    }

    public function getFormImage(): ?string
    {
        return '';
    }

    public function getKit(): string
    {
        return 'NoDebuff';
    }

    public function getName(): string
    {
        return 'Bot';
    }

	/** @param list<string> $data */
    public function parseName(array $data): string
    {
        return '';
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