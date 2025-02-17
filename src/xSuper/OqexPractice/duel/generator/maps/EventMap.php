<?php

namespace xSuper\OqexPractice\duel\generator\maps;

use pocketmine\math\Vector3;

class EventMap extends Map
{

	/** @param list<array{float, float, float}> $eventData */
    public function __construct(string $realName, Vector3 $spawn1, Vector3 $spawn2, protected array $eventData, int $type, string $name, string $author, string $season)
    {
        parent::__construct($realName, $spawn1, $spawn2, $type, $name, $author, $season);
    }

	/** @return list<array{float, float, float}> */
    public function getData(): array
    {
        return $this->eventData;
    }
}