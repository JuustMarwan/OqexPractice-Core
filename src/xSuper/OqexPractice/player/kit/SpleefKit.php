<?php

namespace xSuper\OqexPractice\player\kit;

use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\utils\ItemUtils;

class SpleefKit extends Kit
{
    public function getContents(): array
    {
        $contents = [];
        $contents[0] = ItemUtils::enchant(VanillaItems::IRON_SHOVEL(), [VanillaEnchantments::SHARPNESS(), VanillaEnchantments::UNBREAKING()], [5, 5]);
        return $contents;
    }

    public function getArmor(): array
    {
        return [];
    }

    public function getEffects(): array
    {
        return [];
    }
}