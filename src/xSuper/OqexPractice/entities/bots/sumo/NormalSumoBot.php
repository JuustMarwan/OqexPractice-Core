<?php

namespace xSuper\OqexPractice\entities\bots\sumo;

use xSuper\OqexPractice\entities\pathfinder\entity\SmartEntity;
use xSuper\OqexPractice\entities\pathfinder\entity\SumoEntity;
use xSuper\OqexPractice\player\data\PlayerInfo;

class NormalSumoBot extends SumoEntity
{
    protected function getHitPoints(): int
    {
        return 20;
    }

    protected function getTag(): string
    {
        return "§r§l§eNormal Bot\n§r§f" . PlayerInfo::DEVICE_OS_UNICODES[PlayerInfo::LINUX] . ' VersAI';
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
        return 60;
    }

    protected function canStrafe(): bool
    {
        return true;
    }

    protected function getStrafeCoolDown(): int
    {
        return 7;
    }

    protected function getStrafeChance(): int
    {
        return 80;
    }

    protected function getMaxStrafeDistance(): int
    {
        return 2;
    }

    protected function getMinStrafeDistance(): int
    {
        return 1;
    }

    protected function blocksBeforePearl(): int
    {
        return 20;
    }

    protected function potCoolDown(): int
    {
        return 60;
    }

    protected function getRefillPerSlotTicks(): int
    {
        return 8;
    }

    protected function getCPS(): int
    {
        return 15;
    }

    protected function checkPlayerPosInterval(): int
    {
        return 5;
    }

    protected function diffToAccuracy(): float
    {
        return 20;
    }
}