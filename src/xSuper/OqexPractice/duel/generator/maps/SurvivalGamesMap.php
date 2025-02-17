<?php

namespace xSuper\OqexPractice\duel\generator\maps;

use pocketmine\math\Vector3;

class SurvivalGamesMap extends Map
{

    public function __construct(string $realName, Vector3 $spawn1, Vector3 $spawn2, protected Vector3 $middle, protected int $low, protected int $mid, protected int $high, int $type, string $name, string $author, string $season)
    {
        parent::__construct($realName, $spawn1, $spawn2, $type, $name, $author, $season);
    }

    public function getMiddle(): Vector3
    {
        return $this->middle;
    }

    public function getLow(): int
    {
        return $this->low;
    }

    public function getMid(): int
    {
        return $this->mid;
    }

    public function getHigh(): int
    {
        return $this->high;
    }
}