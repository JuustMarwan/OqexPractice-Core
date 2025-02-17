<?php

namespace xSuper\OqexPractice\entities\bots\sumo;

use xSuper\OqexPractice\entities\pathfinder\entity\SmartEntity;
use xSuper\OqexPractice\entities\pathfinder\entity\SumoEntity;
use xSuper\OqexPractice\player\data\PlayerInfo;

class DummySumoBot extends SumoEntity
{
    protected function getHitPoints(): int
    {
        return 20;
    }

    protected function getTag(): string
    {
        return "§r§l§aDummy Bot\n§r§f" . PlayerInfo::DEVICE_OS_UNICODES[PlayerInfo::LINUX] . ' VersAI';
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
        return 20;
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
        return 1;
    }

    protected function blocksBeforePearl(): int
    {
        return 30;
    }

    protected function potCoolDown(): int
    {
        return 80;
    }

    protected function getRefillPerSlotTicks(): int
    {
        return 10;
    }

    protected function getCPS(): int
    {
        return 5;
    }

    protected function checkPlayerPosInterval(): int
    {
        return 10;
    }

    protected function diffToAccuracy(): float
    {
        return 10;
    }
}