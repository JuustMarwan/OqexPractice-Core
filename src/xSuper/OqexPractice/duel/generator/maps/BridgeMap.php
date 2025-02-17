<?php

namespace xSuper\OqexPractice\duel\generator\maps;

use pocketmine\math\Vector3;

class BridgeMap extends Map
{

    public function __construct(string $realName, Vector3 $spawn1, Vector3 $spawn2, protected Vector3 $portal1, protected Vector3 $portal2, protected int $red, int $type, string $name, string $author, string $season)
    {
        parent::__construct($realName, $spawn1, $spawn2, $type, $name, $author, $season);
    }

    public function getPortal1(): Vector3
    {
        return $this->portal1;
    }

    public function getPortal2(): Vector3
    {
        return $this->portal2;
    }

    public function getRed(): int
    {
        return $this->red;
    }
}