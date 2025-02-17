<?php

namespace xSuper\OqexPractice\player\kit;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\utils\ItemUtils;

class NoDebuffKit extends Kit
{
    public function getContents(): array
    {
        $contents = [];
        $contents[0] = ItemUtils::enchant(VanillaItems::DIAMOND_SWORD()->setUnbreakable(), [VanillaEnchantments::SHARPNESS(), VanillaEnchantments::UNBREAKING()], [2, 3]);
        $contents[1] = VanillaItems::ENDER_PEARL()->setCount(16);
        for ($i = 2; $i <= 35; $i++) {
            $contents[$i] = VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_HEALING());
        }
        return $contents;
    }

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
        return [
            new EffectInstance(VanillaEffects::SPEED(), 2147483647, 0, false, false)
        ];
    }
}