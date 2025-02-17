<?php
/*
 * Copyright (c) Matze997
 * All rights reserved.
 * Under GPL license
 */

declare(strict_types=1);

namespace xSuper\OqexPractice\entities\pathfinder\entity\handler;


use xSuper\OqexPractice\entities\pathfinder\entity\SumoEntity;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\matze\pathfinder\node\Node;
use xSuper\OqexPractice\entities\pathfinder\entity\ArcherEntity;
use xSuper\OqexPractice\entities\pathfinder\entity\Navigator;
use xSuper\OqexPractice\entities\pathfinder\entity\SmartEntity;

abstract class MovementHandler {
    protected float $gravity = 0.08;

    public function getGravity(): float{
        return $this->gravity;
    }

    public function setGravity(float $gravity): void{
        $this->gravity = $gravity;
    }

    abstract public function handle(SmartEntity|ArcherEntity|SumoEntity $entity, Navigator $navigator, Node $node): void;
}