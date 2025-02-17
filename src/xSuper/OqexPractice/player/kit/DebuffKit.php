<?php

namespace xSuper\OqexPractice\player\kit;

use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\utils\ItemUtils;

class DebuffKit extends Kit
{
    public function getContents(): array
    {
        $contents = [];
        $contents[0] = ItemUtils::enchant(VanillaItems::DIAMOND_SWORD()->setUnbreakable(), [VanillaEnchantments::SHARPNESS(), VanillaEnchantments::UNBREAKING()], [2, 3]);
        $contents[1] = VanillaItems::ENDER_PEARL()->setCount(16);
        $contents[2] = VanillaItems::SPLASH_POTION()->setType(PotionType::LONG_POISON());
        $contents[3] = VanillaItems::SPLASH_POTION()->setType(PotionType::LONG_SLOWNESS());
        $contents[18] = VanillaItems::SPLASH_POTION()->setType(PotionType::LONG_POISON());
        $contents[19] = VanillaItems::SPLASH_POTION()->setType(PotionType::LONG_SLOWNESS());
        $contents[27] = VanillaItems::SPLASH_POTION()->setType(PotionType::LONG_POISON());
        $contents[28] = VanillaItems::SPLASH_POTION()->setType(PotionType::LONG_SLOWNESS());
        for ($i = 2; $i <= 35; $i++) {
            if (!in_array($i, [2, 3, 18, 19, 27, 28], true)) $contents[$i] = VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_HEALING());
        }
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