<?php

namespace xSuper\OqexPractice\entities\pathfinder\entity;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\matze\pathfinder\result\PathResult;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\matze\pathfinder\type\AsyncPathfinder;
use function count;

class SmartEntityNavigator extends Navigator {
    public function update(SmartEntity|ArcherEntity|SumoEntity $entity): int {
        if($this->targetVector3 === null) return 0;

        $location = $entity->getLocation();
        if($this->pathResult === null) {
            if ($this->algorithm === null || !$this->algorithm->isRunning()) {
                $this->algorithm = new AsyncPathfinder($this->algorithmSettings, $location->getWorld());
                $id = spl_object_id($this->algorithm);
                $this->algorithm->findPath($location->floor(), $this->targetVector3, function (?PathResult $pathResult) use ($id): void {
                    if (isset($this->discardedResults[$id])) {
                        unset($this->discardedResults[$id]);
                        return;
                    }

                    $this->pathResult = $pathResult;
                    if ($pathResult === null) {
                        return;
                    }
                    $this->nodes = $pathResult->getNodes();
                    $count = count($this->nodes);
                    if($count > 1){
                        next($this->nodes);
                    }
                });
            }
            return 0;
        }
        $pathPoint = current($this->nodes);
        if($pathPoint === false){
            $this->lastNode = null;
            $this->recalculatePath();
            return 2;
        }

        if($location->withComponents(null, 0, null)->distanceSquared($pathPoint->withComponents(null, 0, null)) <= 0.2) {
            $pathPoint = next($this->nodes);
            if($pathPoint === false){
                $this->recalculatePath();
                return 2;
            }
        }
        if($this->jumpTicks > 0) $this->jumpTicks--;
        $this->movementHandler->handle($entity, $this, $pathPoint);
        if($this->lastVector3 !== null && $this->lastVector3->x === $location->x && $this->lastVector3->z === $location->z) {
            if(++$this->stuckTicks >= 10){
                $this->recalculatePath();
                $this->stuckTicks = 0;
            }
        } else {
            $this->stuckTicks = 0;
        }
        $this->lastVector3 = $location->asVector3();
        $this->lastNode = $pathPoint;
        return 2;
    }
}