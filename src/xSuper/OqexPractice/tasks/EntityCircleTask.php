<?php

namespace xSuper\OqexPractice\tasks;

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\Task;
use pocketmine\world\Position;

class EntityCircleTask extends Task
{
    private array $entities;
    private Vector3 $center;
    private float $radius;

    private bool $cancel = false;

    private int $currentAngle = 0;

    /** @param $entities Entity[]  */
    public function __construct(array $entities, Vector3 $center, float $radius, private int $speed) {
        $this->entities = $entities;
        $this->center = $center;
        $this->radius = $radius;
    }

    public function cancel(): void
    {
        $this->cancel = true;
    }

    public function onRun(): void
    {
        if ($this->cancel) throw new CancelTaskException();
        if (count($this->entities) === 0) throw new CancelTaskException();
        $count = count($this->entities);
        $angleStep = 360 / $count;
        foreach ($this->entities as $k => $entity) {
            if ($entity->isClosed()) unset($this->entities[$k]);
            else {
                $angle = $this->currentAngle + ($entity->getId() % $count) * $angleStep;
                $x = $this->center->getX() + $this->radius * cos(deg2rad($angle));
                $z = $this->center->getZ() + $this->radius * sin(deg2rad($angle));

                $entity->teleport(new Vector3($x, $this->center->getY(), $z));

                $loc = $entity->getLocation();

                $entity->setRotation($loc->getYaw() + $this->speed, $loc->getPitch());
                $this->currentAngle += $this->speed;
                if ($this->currentAngle >= 360) {
                    $this->currentAngle = 0;
                }
            }
        }
    }
}