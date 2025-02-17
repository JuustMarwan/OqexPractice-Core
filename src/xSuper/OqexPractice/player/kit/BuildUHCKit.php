<?php

namespace xSuper\OqexPractice\player\kit;

use pocketmine\block\utils\MobHeadType;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\utils\ItemUtils;

class BuildUHCKit extends Kit
{
    public function getContents(): array
    {
        $contents = [];
        $contents[0] = ItemUtils::enchant(VanillaItems::DIAMOND_SWORD()->setUnbreakable(), [VanillaEnchantments::SHARPNESS()], [3]);
        $contents[1] = VanillaItems::FISHING_ROD();
        $contents[2] = VanillaItems::WATER_BUCKET();
        $contents[3] = VanillaItems::LAVA_BUCKET();
        $contents[4] = VanillaItems::GOLDEN_APPLE()->setCount(6);
        $contents[5] = VanillaBlocks::MOB_HEAD()->setMobHeadType(MobHeadType::CREEPER())->asItem()->setCount(3)->setCustomName('§r§6Golden Head');
        $contents[6] = VanillaBlocks::COBBLESTONE()->asItem()->setCount(64);
        $contents[7] = VanillaItems::DIAMOND_PICKAXE();
        $contents[8] = ItemUtils::enchant(VanillaItems::BOW(), [VanillaEnchantments::POWER()], [4]);
        $contents[9] = VanillaItems::ARROW()->setCount(16);
        $contents[10] = VanillaItems::WATER_BUCKET();
        $contents[11] = VanillaItems::LAVA_BUCKET();
        $contents[17] = VanillaBlocks::OAK_PLANKS()->asItem()->setCount(64);

        return $contents;
    }

	/** @return array<int, Item> */
    public function getArmor(): array
    {
        return [
            ItemUtils::enchant(VanillaItems::DIAMOND_HELMET()->setUnbreakable(), [VanillaEnchantments::PROTECTION()], [2]),
            ItemUtils::enchant(VanillaItems::DIAMOND_CHESTPLATE()->setUnbreakable(), [VanillaEnchantments::PROTECTION()], [2]),
            ItemUtils::enchant(VanillaItems::DIAMOND_LEGGINGS()->setUnbreakable(), [VanillaEnchantments::PROTECTION()], [2]),
            ItemUtils::enchant(VanillaItems::DIAMOND_BOOTS()->setUnbreakable(), [VanillaEnchantments::PROTECTION()], [2]),
        ];
    }

    public function getEffects(): array
    {
        return [];
    }
}