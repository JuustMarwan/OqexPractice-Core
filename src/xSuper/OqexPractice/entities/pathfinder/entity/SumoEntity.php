<?php

namespace xSuper\OqexPractice\entities\pathfinder\entity;

use pocketmine\entity\Human;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\matze\pathfinder\setting\Settings;
use pocketmine\entity\animation\Animation;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\animation\CriticalHitAnimation;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\Server;
use xSuper\OqexPractice\duel\type\Types;

abstract class SumoEntity extends Human
{
    private const SAFE_DISTANCE = 0.8;
    private const UPDATE_DISTANCE = 1.5;

    public int $attackCoolDown;

    public int $potCoolDown;
    public ?Vector3 $lastPlayerPos = null;
    public int $comboHits = 0;
    public bool $agro = false;


    public float $speed = 0.75;

    public int $cpsTicks = 0;

    public bool $tBag = false;

    public function __construct(Location $location, public ?Player $target, ?CompoundTag $nbt)
    {
        if ($this->target === null) return;

        parent::__construct($location, $this->target->getSkin(), $nbt);

        $this->setMaxHealth($this->getHitPoints());
        $this->setHealth($this->getHitPoints());

        $this->setNameTag($this->getTag());
        $this->setNameTagAlwaysVisible();
        $this->setCanSaveWithChunk(false);

        $this->attackCoolDown = 0;
    }

    abstract protected function getHitPoints(): int;

    abstract protected function getTag(): string;

    abstract protected function getAttackCoolDown(): int;

    abstract protected function getReach(): float;

    abstract protected function getHitAccuracy(): float;

    abstract protected function blocksBeforePearl(): int;

    abstract protected function potCoolDown(): int;

    abstract protected function getRefillPerSlotTicks(): int;

    abstract protected function getCPS(): int;

    abstract protected function checkPlayerPosInterval(): int;

    abstract protected function diffToAccuracy(): float;

    public function tBag(): void
    {
        $this->tBag = true;
    }

    public function broadcastAnimation(Animation $animation, ?array $targets = null): void
    {
        if ($animation instanceof CriticalHitAnimation) return;
        parent::broadcastAnimation($animation, $targets);
    }

    public function attack(EntityDamageEvent $source): void
    {
        if ($source->getCause() === EntityDamageEvent::CAUSE_VOID) {
            $target = $this->getTarget();
            if ($target === null || $this->isFlaggedForDespawn()) return;

            if ($target->getWorld()->getId() !== $this->getWorld()->getId()) {
                $this->flagForDespawn();
                return;
            }

            $this->parentTeleport($target->getPosition());
        }
        if ($source instanceof EntityDamageByEntityEvent) $this->comboHits++;

        if ($source->getModifier(EntityDamageEvent::MODIFIER_CRITICAL) > 0) {
            $source->setModifier(0, EntityDamageEvent::MODIFIER_CRITICAL);
        }
        parent::attack($source);
    }

    public function getTarget(): ?Player
    {
        if ($this->target === null) return null;
        if (!$this->target->isOnline() || $this->target->isCreative()) return null;
        else return $this->target;
    }

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        parent::entityBaseTick($tickDiff);

        $target = $this->getTarget();
        if ($target === null) return false;

        if ($target->getWorld()->getId() !== $this->getWorld()->getId()) {
            $this->flagForDespawn();
            return false;
        }

        $position = $target->getPosition();
        if (!$position->getWorld()->isInWorld(intval($position->x), intval($position->y), intval($position->z))) return false;

        if ($this->lastPlayerPos === null || $tickDiff % $this->checkPlayerPosInterval() === 0) $this->lastPlayerPos = $position->asVector3();

        if (!$this->tBag) {
            if ($this->attackCoolDown >= 0) $this->attackCoolDown--;

            if ($this->cpsTicks < PHP_INT_MAX) $this->cpsTicks++;
            else $this->cpsTicks = 0;
        }

        if ($this->isOnGround()) {
            if ($this->tBag) {
                if ($this->ticksLived % 7 === 0) {
                    if ($this->sneaking) $this->setSneaking(false);
                    else $this->setSneaking();
                }
                return true;
            }

            if ($this->getPosition()->distance($target->getPosition()) > self::UPDATE_DISTANCE) {
                $cont = true;
                if ($this->getPosition()->getY() - $target->getPosition()->getY() >= 1.3) $cont = false;

                if ($cont) {
                    if ($target->isOnGround()) $this->goTo($position);
                    else {
                        $world = $target->getWorld();
                        $lowest = 0;
                        $x = $target->getPosition()->getFloorX();
                        $z = $target->getPosition()->getFloorZ();
                        for ($y = ceil($target->getPosition()->getY()); $y >= ceil($target->getPosition()->getY()) - 3; $y--) {
                            $b = $world->getBlockAt($x, $y, $z);
                            if (!$b->hasEntityCollision()) $lowest++;
                        }

                        if ($lowest <= 1) $this->goTo($position);
                    }
                }
            }
        }

        return $this->attackTarget();
    }

    private function goTo(Vector3 $position): void
    {
        $pos = $this->getPosition()->asVector3();
        $x = $position->x - $pos->getX();
        $z = $position->z - $pos->getZ();
        if ($x !== 0 || $z !== 0) {
            $this->motion->x = $this->speed * 0.35 * ($x / (abs($x) + abs($z)));
            $this->motion->z = $this->speed * 0.35 * ($z / (abs($x) + abs($z)));
        }
        if (!$this->isOnGround()) {
            if ($this->motion->y > -$this->gravity * 4) {
                $this->motion->y = -$this->gravity * 4;
            } else {
                $this->motion->y -= $this->gravity;
            }
        } else {
            $this->motion->y -= $this->gravity;
        }

        $this->move($this->motion->x, $this->motion->y, $this->motion->z);

        if ($pos->distance($pos) > 8) {
            $this->setSprinting();
            $this->speed = 0.75;
        } else {
            $this->setSprinting(false);
            $this->speed = 0.6;
        }
    }

    /** @return array{float, float} */
    private static function maxMin(float $first, float $second) : array{
        return $first > $second ? [$first, $second] : [$second, $first];
    }

    public function knockBack(float $x, float $z, float $force = 0.4, ?float $verticalLimit = 0.4): void
    {
        $f = sqrt($x * $x + $z * $z);
        if ($f <= 0) {
            return;
        }

        $type = Types::BOT();
        $xzKb = $type->getKB()['xzKb'];
        $yKb = $type->getKB()['yKb'];
        $maxHeight = $type->getKB()['maxHeight'];
        $revert = $type->getKB()['revert'];

        if (!$this->isOnGround()) {
            $entity = $this->getTarget();
            if ($entity !== null) {
                [$max, $min] = self::maxMin($this->getPosition()->getY(), $entity->getPosition()->getY());
                if ($max - $min >= $maxHeight) {
                    $yKb *= $revert;
                }
            }
        }

        if($this->agro){
            $xzKb *= 0.85;
            $yKb *= 0.85;
            $this->agro = false;
        }

        if (mt_rand() / mt_getrandmax() > $this->knockbackResistanceAttr->getValue()) {
            $f = 1 / $f;
            $motion = Vector3::zero();
            $motion->x /= 2;
            $motion->y /= 2;
            $motion->z /= 2;
            $motion->x += $x * $f * $xzKb;
            $motion->y += $yKb;
            $motion->z += $z * $f * $xzKb;
            if ($motion->y > $yKb) {
                $motion->y = $yKb;
            }

            $this->setMotion($motion);
        }
    }

    public static function getRandomFloatNumber(int $min, int $max): float
    {
        return lcg_value() * ($max - $min) + $min;
    }

    public function attackTarget(): bool
    {
        if (!$this->isAlive()) {
            if (!$this->closed) $this->flagForDespawn();
            return false;
        }
        $target = $this->getTarget();
        if ($target === null) {
            $this->target = null;
            return false;
        }

        if ($this->getWorld()->getFolderName() !== $target->getWorld()->getFolderName()) return false;

        $eyePos = $this->getEyePos();
        $optimalPos = new Vector3(
            max($target->boundingBox->minX, min($target->boundingBox->maxX, $eyePos->x)),
            max($target->boundingBox->minY, min($target->boundingBox->maxY, $eyePos->y)),
            max($target->boundingBox->minZ, min($target->boundingBox->maxZ, $eyePos->z))
        );
        $this->lookAt($optimalPos);
        if ($this->cpsTicks % $this->cpsToTicks() === 0){
            if($optimalPos->distanceSquared($this->getEyePos()) <= $this->getReach() ** 2) {
                $this->broadcastAnimation(new ArmSwingAnimation($this));
                //$dist = $this->lastPlayerPos->distance($target->getPosition());

                //$subtract = $this->diffToAccuracy() * $dist;
                if ($this->getHitAccuracy() >= self::getRandomFloatNumber(1, 100)) {
                    if (0 >= $this->attackCoolDown) {
                        $event = new EntityDamageByEntityEvent($this, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getInventory()->getItemInHand()->getAttackPoints());
                        $target->attack($event);
                        $this->comboHits = 0;
                        $this->attackCoolDown = $this->getAttackCoolDown();
                    }
                }
            }
        }

        return true;
    }

    private function cpsToTicks(): int
    {
        return (int)floor(20 / $this->getCPS());
    }

    public function getDrops(): array
    {
        return [];
    }

    public function teleport(Vector3 $pos, ?float $yaw = null, ?float $pitch = null): bool{
        $deltaVector = $pos->subtractVector($this->location);
        $this->move($deltaVector->x, $deltaVector->y, $deltaVector->z);
        $this->setRotation($yaw ?? $this->location->yaw, $pitch ?? $this->location->pitch);
        return true;
    }

    public function parentTeleport(Vector3 $pos, ?float $yaw = null, ?float $pitch = null): bool
    {
        return parent::teleport($pos, $yaw, $pitch);
    }
}