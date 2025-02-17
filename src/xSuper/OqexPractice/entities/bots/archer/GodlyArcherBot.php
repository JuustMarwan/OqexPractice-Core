<?php

namespace xSuper\OqexPractice\entities\bots\archer;

use xSuper\OqexPractice\entities\pathfinder\entity\ArcherEntity;
use xSuper\OqexPractice\player\data\PlayerInfo;

class GodlyArcherBot extends ArcherEntity
{
    protected function getHitPoints(): int
    {
        return 20;
    }

    protected function getTag(): string
    {
        return "§r§l§cGodly Bot\n§r§f" . PlayerInfo::DEVICE_OS_UNICODES[PlayerInfo::LINUX] . ' VersAI';
    }

    protected function getAttackCoolDown(): int
    {
        return 10;
    }

    protected function getReach(): float
    {
        return 3.1;
    }

    protected function getAttackDamage(): int
    {
        return 1;
    }

    protected function getHitAccuracy(): float
    {
        return 100;
    }

    protected function getCPS(): int
    {
        return 20;
    }

    protected function fireFromBowTicksChance(): int
    {
        return 15;
    }

    protected function playerBowTicksToFire(): int
    {
        return 12;
    }

    protected function playerBowTicksToCharge(): int
    {
        return 7;
    }

    protected function maxTimeBetweenShots(): int
    {
        return 60;
    }

    protected function strafeInterval(): int
    {
        return 10;
    }


}