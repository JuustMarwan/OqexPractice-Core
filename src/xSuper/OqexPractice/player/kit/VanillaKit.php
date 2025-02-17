<?php

namespace xSuper\OqexPractice\player\kit;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;

class VanillaKit extends Kit
{
    public function getContents(): array
    {
        $contents = [];
        $contents[0] = VanillaItems::DIAMOND_SWORD()->setUnbreakable();
        $contents[1] = VanillaItems::ENDER_PEARL()->setCount(16);
        for ($i = 2; $i <= 35; $i++) {
            $contents[$i] = VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_HEALING());
        }
        return $contents;
    }

    public function getArmor(): array
    {
        return [
            VanillaItems::DIAMOND_HELMET()->setUnbreakable(),
            VanillaItems::DIAMOND_CHESTPLATE()->setUnbreakable(),
            VanillaItems::DIAMOND_LEGGINGS()->setUnbreakable(),
            VanillaItems::DIAMOND_BOOTS()->setUnbreakable()
        ];
    }

    public function getEffects(): array
    {
        return [
            new EffectInstance(VanillaEffects::SPEED(), 2147483647, 0, false, false)
        ];
    }
}