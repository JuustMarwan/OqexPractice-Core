<?php

namespace xSuper\OqexPractice\player\kit;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\utils\ItemUtils;

class ArcherKit extends Kit
{
	/** @return array<int, Item> */
    public function getContents(): array
    {
        $contents = [];
        $contents[0] = VanillaItems::WOODEN_PICKAXE()->setUnbreakable();
        $contents[1] = ItemUtils::enchant(VanillaItems::BOW()->setUnbreakable(), [VanillaEnchantments::POWER(), VanillaEnchantments::UNBREAKING(), VanillaEnchantments::INFINITY()], [3, 3, 1]);
        $contents[8] = VanillaItems::ARROW();
        return $contents;
    }

	/** @return array<int, Item> */
    public function getArmor(): array
    {
        return [
            VanillaItems::LEATHER_CAP()->setUnbreakable(),
            VanillaItems::LEATHER_TUNIC()->setUnbreakable(),
            VanillaItems::LEATHER_PANTS()->setUnbreakable(),
            VanillaItems::LEATHER_BOOTS()->setUnbreakable()
        ];
    }

    public function getEffects(): array
    {
        return [
            new EffectInstance(VanillaEffects::SPEED(), 2147483647, 1, false, false)
        ];
    }
}