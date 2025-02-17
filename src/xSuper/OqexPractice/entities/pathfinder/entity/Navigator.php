<?php
/*
 * Copyright (c) Matze997
 * All rights reserved.
 * Under GPL license
 */

declare(strict_types=1);

namespace xSuper\OqexPractice\entities\pathfinder\entity;

use Closure;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\matze\pathfinder\node\Node;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\matze\pathfinder\result\PathResult;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\matze\pathfinder\setting\Settings;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\matze\pathfinder\type\AsyncPathfinder;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use xSuper\OqexPractice\entities\pathfinder\entity\handler\DefaultMovementHandler;
use xSuper\OqexPractice\entities\pathfinder\entity\handler\MovementHandler;
use function count;

class Navigator {
    protected float $speed = 0.3;

    protected ?Vector3 $targetVector3 = null;
    protected ?PathResult $pathResult = null;

    protected ?Node $lastNode = null;
    protected ?Vector3 $lastVector3 = null;

	/** @var array<int, Closure> */
    protected array $blockValidators = [];

    protected ?AsyncPathfinder $algorithm = null;

    protected int $jumpTicks = 0;
    protected int $stuckTicks = 0;
    /** @var array<int, Node> */
    protected array $nodes = [];

    /** @var array<int, true> $discardedResults */
    protected array $discardedResults = [];

    public function __construct(
        protected Settings $algorithmSettings,
        protected MovementHandler $movementHandler = new DefaultMovementHandler(),
    ){
    }

    public function getSpeed(): float{
        return $this->speed;
    }

    public function setSpeed(float $speed): void{
        $this->speed = $speed;
    }

    public function getAlgorithmSettings(): Settings{
        return $this->algorithmSettings;
    }

    public function getPathResult(): ?PathResult{
        return $this->pathResult;
    }

    public function getStuckTicks(): int{
        return $this->stuckTicks;
    }

    public function getJumpTicks(): int{
        return $this->jumpTicks;
    }

    public function resetJumpTicks(int $ticks = 4): void {
        $this->jumpTicks = $ticks;
    }

    public function registerBlockValidator(Block $block, Closure $closure): void {
        $this->blockValidators[$block->getTypeId()] = $closure;
    }

    public function getTargetVector3(): ?Vector3{
        return $this->targetVector3;
    }

    public function setTargetVector3(?Vector3 $targetVector3): void{
        $this->targetVector3 = $targetVector3;
        $this->recalculatePath();
    }

    public function recalculatePath(): void {
        if ($this->algorithm !== null) $this->discardedResults[spl_object_id($this->algorithm)] = true;

        $this->algorithm = null;
        $this->pathResult = null;
        $this->lastNode = null;
    }

    public function onUpdate(SmartEntity|ArcherEntity|SumoEntity $entity): void {
        if($this->targetVector3 === null) return;

        $location = $entity->getLocation();
        if($this->pathResult === null) {
            if($this->algorithm === null || !$this->algorithm->isRunning()) {
                $this->algorithm = new AsyncPathfinder($this->algorithmSettings, $location->getWorld(), 80);
                $id = spl_object_id($this->algorithm);
                $this->algorithm->findPath($location->floor(), $this->targetVector3, function (?PathResult $pathResult) use ($id): void {
                    if (isset($this->discardedResults[$id])) {
                        unset($this->discardedResults[$id]);
                        return;
                    }

                    $this->pathResult = $pathResult;
                    if ($this->pathResult === null) return;
                    $this->nodes = $this->pathResult->getNodes();
                    if(count($this->nodes) > 1){
                        next($this->nodes);
                    }
                });
            }
            return;
        }
        $pathPoint = current($this->nodes);
        if($pathPoint === false){
            $this->lastNode = null;
            $this->recalculatePath();
            return;
        }

        if($location->withComponents(null, 0, null)->distanceSquared($pathPoint->withComponents(null, 0, null)) <= 0.2) {
            $pathPoint = next($this->nodes);
            if($pathPoint === false){
                $this->recalculatePath();
                return;
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
    }

    /**
     * @return Node|null
     */
    public function getLastNode(): ?Node
    {
        return $this->lastNode;
    }
}