<?php

namespace xSuper\OqexPractice\entities\bots\sumo;

use xSuper\OqexPractice\entities\pathfinder\entity\SmartEntity;
use xSuper\OqexPractice\entities\pathfinder\entity\SumoEntity;
use xSuper\OqexPractice\player\data\PlayerInfo;

class GodlySumoBot extends SumoEntity
{
    protected function getHitPoints(): int
    {
        return 20;
    }

    protected function getTag(): string
    {
        return "§r§l§4Godly Bot\n§r§f" . PlayerInfo::DEVICE_OS_UNICODES[PlayerInfo::LINUX] . ' VersAI';
    }

    protected function getAttackCoolDown(): int
    {
        return 10;
    }

    protected function getReach(): float
    {
        return 3.25;
    }

    protected function getAttackDamage(): int
    {
        return 1;
    }

    protected function getHitAccuracy(): float
    {
        return 100;
    }

    protected function canStrafe(): bool
    {
        return true;
    }

    protected function getStrafeCoolDown(): int
    {
        return 1;
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
        return 10;
    }

    protected function potCoolDown(): int
    {
        return 20;
    }

    protected function getRefillPerSlotTicks(): int
    {
        return 2;
    }

    protected function getCPS(): int
    {
        return 20;
    }

    protected function checkPlayerPosInterval(): int
    {
        return 3;
    }

    protected function diffToAccuracy(): float
    {
        return 30;
    }
}