<?php

namespace xSuper\OqexPractice\entities\firework;

use pocketmine\utils\LegacyEnumShimTrait;
use pocketmine\world\sound\Sound;
use function spl_object_id;

enum FireworkRocketType{
    use LegacyEnumShimTrait;

    case SMALL_BALL;
    case LARGE_BALL;
    case STAR;
    case CREEPER;
    case BURST;

    public function getSound() : Sound{
        /** @phpstan-var array<int, Sound> $cache */
        static $cache = [];

        return $cache[spl_object_id($this)] ??= match($this){
            self::SMALL_BALL, self::CREEPER, self::STAR, self::BURST => new FireworkExplosionSound(),
            self::LARGE_BALL => new FireworkLargeExplosionSound(),
        };
    }
}