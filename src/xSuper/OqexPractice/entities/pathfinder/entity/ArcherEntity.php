<?php

namespace xSuper\OqexPractice\entities\pathfinder\entity;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\matze\pathfinder\setting\Settings;
use pocketmine\entity\animation\Animation;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\animation\CriticalHitAnimation;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\ItemTypeIds;
use pocketmine\math\Facing;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\sound\BowShootSound;
use xSuper\OqexPractice\duel\type\Types;
use xSuper\OqexPractice\entities\ArrowEntity;

abstract class ArcherEntity extends Human
{
    private const SAFE_DISTANCE = 0.8;
    private const UPDATE_DISTANCE = 1.5;

    public SmartEntityNavigator $navigator;

    public int $attackCoolDown;

    public int $cpsTicks = 0;

    public int $bowCooldown = 0;
    public int $charging = 0;
    public int $lastShotTicks = 0;

    private int $startAction = -1;

    public function __construct(Location $location, public ?Player $target, ?CompoundTag $nbt)
    {
        if ($this->target === null) return;

        parent::__construct($location, $this->target->getSkin(), $nbt);

        $settings = Settings::get()
            ->setJumpHeight(1)
            ->setFallDistance(2)
            ->setTimeout(2 / Server::TARGET_TICKS_PER_SECOND);

        $settings->setSize($this->getInitialSizeInfo());

        $this->navigator = new SmartEntityNavigator($settings);

        $this->navigator->setSpeed(0.85);


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

    abstract protected function getCPS(): int;

    abstract protected function strafeInterval(): int;

    abstract protected function playerBowTicksToCharge(): int;

    abstract protected function playerBowTicksToFire(): int;

    abstract protected function fireFromBowTicksChance(): int;

    abstract protected function maxTimeBetweenShots(): int;

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

    protected function syncNetworkData(EntityMetadataCollection $properties) : void{
        parent::syncNetworkData($properties);

        $properties->setGenericFlag(EntityMetadataFlags::ACTION, $this->startAction > -1);
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

        if ($this->attackCoolDown >= 0) $this->attackCoolDown--;
        if ($this->bowCooldown >= 0) $this->bowCooldown--;

        $this->lastShotTicks++;

        if ($this->cpsTicks < PHP_INT_MAX) $this->cpsTicks++;
        else $this->cpsTicks = 0;

        $pos = $this->getPosition();
        $distance = $pos->distance($position);
        $xzDistance = $this->positiveDifference($pos->x, $position->x) + $this->positiveDifference($pos->z, $position->z);

        if ($this->charging === 0) {
            if ($xzDistance > 6) $this->navigator->setSpeed(0.85);
            else $this->navigator->setSpeed(0.6);
        }

        $this->lookAt($position);

        if ($distance < 40 && $distance > 5) {
            if ($this->getInventory()->getItemInHand()->getTypeId() === ItemTypeIds::WOODEN_PICKAXE) {
                $this->navigator->setTargetVector3(null);
                $this->getInventory()->setHeldItemIndex(1);
            } else if ($this->ticksLived % $this->strafeInterval() === 0) {
                $facing = $this->getHorizontalFacing();

                $pos = $this->getPosition();

                switch ($facing) {
                    case Facing::EAST: // -x
                    case Facing::WEST: // +x
                        $dir = rand(0, 1);

                        if ($dir === 0) $pos = $pos->add(0, 0, rand(2, 7));
                        else $pos = $pos->subtract(0, 0, rand(2, 7));

                        break;
                    case Facing::SOUTH: // -z
                    case Facing::NORTH: // +z
                        $dir = rand(0, 1);

                        if ($dir === 0) $pos = $pos->add(rand(2, 7), 0, 0);
                        else $pos = $pos->subtract(rand(2, 7), 0, 0);
                        break;
                }

                $this->navigator->setTargetVector3($pos);
            }

            $this->navigator->update($this);

            if ($this->bowCooldown <= 0 && ($this->charging > 0 || $target->getItemUseDuration() > $this->playerBowTicksToCharge() || $this->lastShotTicks >= $this->maxTimeBetweenShots())) {
                $this->charging++;
                $this->startAction = $this->server->getTick();
                $this->networkPropertiesDirty = true;

                $this->navigator->setSpeed(0.1);

                if ($this->charging >= 35 || ((rand(0, 100) >= $this->fireFromBowTicksChance() && $target->getItemUseDuration() > $this->playerBowTicksToFire() && $this->charging > 11))) {
                    $p = $target->getPosition()->subtractVector($pos);
                    $yaw = atan2($p->z, $p->x) * 180 / M_PI - 90;
                    $length = (new Vector2($p->x, $p->z))->length();
                    if ((int)$length !== 0) {
                        $g = 0.006;
                        $tmp = 1 - $g * ($g * ($length * $length) + 2 * $p->y);
                        $pitch = 180 / M_PI * -(atan((1 - sqrt($tmp)) / ($g * $length)));
                        $this->setRotation($yaw, $pitch);
                    }

                    $location = $this->getLocation();

                    $this->getWorld()->addSound($location, new BowShootSound());

                    $baseForce = min(((1 ** 2) + 2) / 3, 1);
                    $arrow = new ArrowEntity(Location::fromObject(
                        $this->getEyePos(),
                        $this->getWorld(),
                        ($location->yaw > 180 ? 360 : 0) - $location->yaw,
                        -$location->pitch
                    ), $this, $this->charging >= 30);
                    $arrow->setMotion($this->getDirectionVector()->multiply($baseForce * 3));

                    $arrow->spawnToAll();

                    $this->startAction = -1;
                    $this->networkPropertiesDirty = true;

                    $this->navigator->setSpeed(0.85);

                    $this->charging = 0;
                    $this->bowCooldown = 50;
                    $this->lastShotTicks = 0;

                    return true;
                }
            }
        } else if ($distance > 40 || $distance < 5) {
            $facing = $this->getHorizontalFacing();

            if ($this->getPosition()->distance($target->getPosition()) > self::UPDATE_DISTANCE) {
                switch ($facing) {
                    case Facing::EAST: // -x
                        $position->add(self::SAFE_DISTANCE, 0, 0);
                        break;
                    case Facing::WEST: // +x
                        $position->subtract(self::SAFE_DISTANCE, 0, 0);
                        break;
                    case Facing::SOUTH: // -z
                        $position->add(0, 0, self::SAFE_DISTANCE);
                        break;
                    case Facing::NORTH: // +z
                        $position->subtract(0, 0, self::SAFE_DISTANCE);
                        break;
                }

                $this->goTo($position);
            }

            if ($distance < 5) {
                if ($this->getInventory()->getItemInHand()->getTypeId() === ItemTypeIds::BOW) {
                    $this->getInventory()->setHeldItemIndex(0);

                    if ($this->startAction !== -1) {
                        $this->startAction = -1;
                        $this->networkPropertiesDirty = true;
                    }
                }
                return $this->attackTarget();
            }
        }

        return true;
    }

    private function goTo(Vector3 $position): void
    {
        $pos = $this->getPosition()->asVector3();
        $x = $position->x - $pos->getX();
        $z = $position->z - $pos->getZ();

        if ($x !== 0 || $z !== 0) {
            $this->motion->x = 0.85 * 0.35 * ($x / (abs($x) + abs($z)));
            $this->motion->z = 0.85 * 0.35 * ($z / (abs($x) + abs($z)));
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
    }

    private function positiveDifference($num1, $num2) {
        if ($num1 > $num2) {
            return $num1 - $num2;
        } else {
            return $num2 - $num1;
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