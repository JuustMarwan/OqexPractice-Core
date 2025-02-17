<?php

namespace xSuper\OqexPractice\player\kit;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\utils\ItemUtils;

class ComboKit extends Kit
{
    public function getContents(): array
    {
        $contents = [];
        $contents[0] = ItemUtils::enchant(VanillaItems::DIAMOND_SWORD()->setUnbreakable(), [VanillaEnchantments::SHARPNESS(), VanillaEnchantments::UNBREAKING()], [5, 3]);
        $contents[1] = VanillaItems::ENCHANTED_GOLDEN_APPLE()->setCount(64);
        $contents[2] = ItemUtils::enchant(VanillaItems::DIAMOND_HELMET(), [VanillaEnchantments::PROTECTION(), VanillaEnchantments::UNBREAKING()], [2, 1]);
        $contents[3] = ItemUtils::enchant(VanillaItems::DIAMOND_CHESTPLATE(), [VanillaEnchantments::PROTECTION(), VanillaEnchantments::UNBREAKING()], [2, 1]);
        $contents[4] = ItemUtils::enchant(VanillaItems::DIAMOND_LEGGINGS(), [VanillaEnchantments::PROTECTION(), VanillaEnchantments::UNBREAKING()], [2, 1]);
        $contents[5] = ItemUtils::enchant(VanillaItems::DIAMOND_BOOTS(), [VanillaEnchantments::PROTECTION(), VanillaEnchantments::UNBREAKING()], [2, 1]);

        return $contents;
    }

	/** @return array<int, Item> */
    public function getArmor(): array
    {
        return [
            ItemUtils::enchant(VanillaItems::DIAMOND_HELMET(), [VanillaEnchantments::PROTECTION(), VanillaEnchantments::UNBREAKING()], [2, 1]),
            ItemUtils::enchant(VanillaItems::DIAMOND_CHESTPLATE(), [VanillaEnchantments::PROTECTION(), VanillaEnchantments::UNBREAKING()], [2, 1]),
            ItemUtils::enchant(VanillaItems::DIAMOND_LEGGINGS(), [VanillaEnchantments::PROTECTION(), VanillaEnchantments::UNBREAKING()], [2, 1]),
            ItemUtils::enchant(VanillaItems::DIAMOND_BOOTS(), [VanillaEnchantments::PROTECTION(), VanillaEnchantments::UNBREAKING()], [2, 1])
        ];
    }

    public function getEffects(): array
    {
        return [
            new EffectInstance(VanillaEffects::SPEED(), 2147483647, 1, false, false)
        ];
    }
}