<?php

namespace xSuper\OqexPractice\entities\bots\nodebuff;

use xSuper\OqexPractice\entities\pathfinder\entity\SmartEntity;
use xSuper\OqexPractice\player\data\PlayerInfo;

class HardNoDebuffBot extends SmartEntity
{
    protected function getHitPoints(): int
    {
        return 20;
    }

    protected function getTag(): string
    {
        return "§r§l§cHard Bot\n§r§f" . PlayerInfo::DEVICE_OS_UNICODES[PlayerInfo::LINUX] . ' VersAI';
    }

    protected function getAttackCoolDown(): int
    {
        return 10;
    }

    protected function getReach(): float
    {
        return 3;
    }

    protected function getAttackDamage(): int
    {
        return 1;
    }

    protected function getHitAccuracy(): float
    {
        return 80;
    }

    protected function canStrafe(): bool
    {
        return true;
    }

    protected function getStrafeCoolDown(): int
    {
        return 5;
    }

    protected function getStrafeChance(): int
    {
        return 100;
    }

    protected function getMaxStrafeDistance(): int
    {
        return 3;
    }

    protected function getMinStrafeDistance(): int
    {
        return 2;
    }

    protected function blocksBeforePearl(): int
    {
        return 15;
    }

    protected function potCoolDown(): int
    {
        return 40;
    }

    protected function getRefillPerSlotTicks(): int
    {
        return 6;
    }

    protected function getCPS(): int
    {
        return 20;
    }

    protected function checkPlayerPosInterval(): int
    {
        return 5;
    }

    protected function diffToAccuracy(): float
    {
        return 30;
    }
}