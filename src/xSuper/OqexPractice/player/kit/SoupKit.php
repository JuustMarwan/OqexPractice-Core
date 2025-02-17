<?php

namespace xSuper\OqexPractice\player\kit;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\utils\ItemUtils;

class SoupKit extends Kit
{
    public function getContents(): array
    {
        $contents = [];
        $contents[0] = ItemUtils::enchant(VanillaItems::DIAMOND_SWORD()->setUnbreakable(), [VanillaEnchantments::UNBREAKING()], [10]);
        for ($i = 1; $i <= 35; $i++) {
            $contents[$i] = VanillaItems::MUSHROOM_STEW();
        }
        return $contents;
    }

    public function getArmor(): array
    {
        return [
            VanillaItems::IRON_HELMET()->setUnbreakable(),
            VanillaItems::IRON_CHESTPLATE()->setUnbreakable(),
            VanillaItems::IRON_LEGGINGS()->setUnbreakable(),
            VanillaItems::IRON_BOOTS()->setUnbreakable()
        ];
    }

    public function getEffects(): array
    {
        return [
            new EffectInstance(VanillaEffects::SPEED(), 2147483647, 0, false, false)
        ];
    }
}