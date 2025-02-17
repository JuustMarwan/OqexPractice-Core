<?php

namespace xSuper\OqexPractice\entities\bots\archer;

use xSuper\OqexPractice\entities\pathfinder\entity\ArcherEntity;
use xSuper\OqexPractice\player\data\PlayerInfo;

class HardArcherBot extends ArcherEntity
{
    protected function getHitPoints(): int
    {
        return 20;
    }

    protected function getTag(): string
    {
        return "§r§l§6Hard Bot\n§r§f" . PlayerInfo::DEVICE_OS_UNICODES[PlayerInfo::LINUX] . ' VersAI';
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


    protected function getCPS(): int
    {
        return 20;
    }

    protected function fireFromBowTicksChance(): int
    {
        return 10;
    }

    protected function playerBowTicksToFire(): int
    {
        return 13;
    }

    protected function playerBowTicksToCharge(): int
    {
        return 9;
    }

    protected function maxTimeBetweenShots(): int
    {
        return 65;
    }

    protected function strafeInterval(): int
    {
        return 15;
    }


}