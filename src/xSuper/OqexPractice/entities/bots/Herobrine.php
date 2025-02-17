<?php

namespace xSuper\OqexPractice\entities\bots;

use xSuper\OqexPractice\entities\pathfinder\entity\SmartEntity;

class Herobrine extends SmartEntity
{
    protected function getHitPoints(): int
    {
        return 20;
    }

    protected function getTag(): string
    {
        return "§r§fYou shouldn't be here...";
    }

    protected function getAttackCoolDown(): int
    {
        return 10000;
    }

    protected function getReach(): float
    {
        return 0;
    }
    protected function getAttackDamage(): int
    {
        return 0;
    }

    protected function getHitAccuracy(): float
    {
        return 0;
    }

    protected function canStrafe(): bool
    {
        return false;
    }

    protected function getStrafeCoolDown(): int
    {
        return 0;
    }

    protected function getStrafeChance(): int
    {
        return 0;
    }

    protected function getMaxStrafeDistance(): int
    {
        return 0;
    }

    protected function getMinStrafeDistance(): int
    {
        return 0;
    }

    protected function blocksBeforePearl(): int
    {
        return 1000;
    }

    protected function potCoolDown(): int
    {
        return 0;
    }

    protected function getRefillPerSlotTicks(): int
    {
        return 0;
    }

    protected function getCPS(): int
    {
        return 1;
    }

    protected function checkPlayerPosInterval(): int
    {
        return 20;
    }

    protected function diffToAccuracy(): float
    {
        return 10;
    }
}