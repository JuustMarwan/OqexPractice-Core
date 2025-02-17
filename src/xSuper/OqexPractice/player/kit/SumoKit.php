<?php

namespace xSuper\OqexPractice\player\kit;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;

class SumoKit extends Kit
{
    public function getContents(): array
    {
        return [];
    }

    public function getArmor(): array
    {
        return [];
    }

    public function getEffects(): array
    {
        return [
            new EffectInstance(VanillaEffects::RESISTANCE(), 2147483647, 255, false, false)
        ];
    }
}