<?php

namespace xSuper\OqexPractice\duel\generator\maps;

use pocketmine\math\Vector3;

class SumoMap extends Map
{

    public function __construct(string $realName, Vector3 $spawn1, Vector3 $spawn2, protected int $min, int $type, string $name, string $author, string $season)
    {
        parent::__construct($realName, $spawn1, $spawn2, $type, $name, $author, $season);
    }

    public function getMin(): int
    {
        return $this->min;
    }
}