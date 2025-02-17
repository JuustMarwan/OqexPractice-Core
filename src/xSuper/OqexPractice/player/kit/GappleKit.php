<?php

namespace xSuper\OqexPractice\player\kit;

use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\utils\ItemUtils;

class GappleKit extends Kit
{
    public function getContents(): array
    {
        $contents = [];
        $contents[0] = ItemUtils::enchant(VanillaItems::DIAMOND_SWORD()->setUnbreakable(), [VanillaEnchantments::SHARPNESS(), VanillaEnchantments::UNBREAKING()], [3, 3]);
        $contents[1] = VanillaItems::GOLDEN_APPLE()->setCount(12);

        return $contents;
    }

	/** @return array<int, Item> */
    public function getArmor(): array
    {
        return [
            ItemUtils::enchant(VanillaItems::DIAMOND_HELMET()->setUnbreakable(), [VanillaEnchantments::PROTECTION(), VanillaEnchantments::UNBREAKING()], [2, 3]),
            ItemUtils::enchant(VanillaItems::DIAMOND_CHESTPLATE()->setUnbreakable(), [VanillaEnchantments::PROTECTION(), VanillaEnchantments::UNBREAKING()], [2, 3]),
            ItemUtils::enchant(VanillaItems::DIAMOND_LEGGINGS()->setUnbreakable(), [VanillaEnchantments::PROTECTION(), VanillaEnchantments::UNBREAKING()], [2, 3]),
            ItemUtils::enchant(VanillaItems::DIAMOND_BOOTS()->setUnbreakable(), [VanillaEnchantments::PROTECTION(), VanillaEnchantments::UNBREAKING()], [2, 3])
        ];
    }

    public function getEffects(): array
    {
        return [];
    }
}