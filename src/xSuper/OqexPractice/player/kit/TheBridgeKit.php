<?php

namespace xSuper\OqexPractice\player\kit;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\utils\ItemUtils;

class TheBridgeKit extends Kit
{
    public function getContents(): array
    {
        return [
            0 => ItemUtils::enchant(VanillaItems::IRON_SWORD(), [VanillaEnchantments::SHARPNESS()], [2])->setUnbreakable(),
            1 => ItemUtils::enchant(VanillaItems::DIAMOND_PICKAXE(), [VanillaEnchantments::EFFICIENCY()], [1]),
            2 => VanillaBlocks::SANDSTONE()->asItem()->setCount(64),
            3 => VanillaBlocks::SANDSTONE()->asItem()->setCount(64),
            4 => VanillaItems::GOLDEN_APPLE()->setCount(5),
            8 => VanillaItems::SNOWBALL()->setCount(4)
        ];
    }

    public function getArmor(): array
    {
        return [
            ItemUtils::enchant(VanillaItems::IRON_HELMET(), [VanillaEnchantments::PROTECTION()], [1])->setUnbreakable(),
            ItemUtils::enchant(VanillaItems::IRON_CHESTPLATE(), [VanillaEnchantments::PROTECTION()], [1])->setUnbreakable(),
            ItemUtils::enchant(VanillaItems::IRON_LEGGINGS(), [VanillaEnchantments::PROTECTION()], [1])->setUnbreakable(),
            ItemUtils::enchant(VanillaItems::IRON_BOOTS(), [VanillaEnchantments::PROTECTION()], [1])->setUnbreakable()
        ];
    }

    public function getEffects(): array
    {
        return [];
    }
}