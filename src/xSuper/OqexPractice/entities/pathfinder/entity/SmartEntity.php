<?php

namespace xSuper\OqexPractice\entities\pathfinder\entity;

use Co\MySQL\Exception;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\matze\pathfinder\setting\Settings;
use pocketmine\entity\animation\Animation;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\animation\CriticalHitAnimation;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\item\PotionType;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\Position;
use pocketmine\world\sound\ThrowSound;
use xSuper\OqexPractice\duel\type\Types;
use xSuper\OqexPractice\entities\EnderPearlEntity;
use xSuper\OqexPractice\entities\SplashPotionEntity;

abstract class SmartEntity extends Human
{
    protected const PEARL_COOLDOWN = 15 * 20;
    private const SAFE_DISTANCE = 0.8;
    private const UPDATE_DISTANCE = 1.5;

    public int $attackCoolDown;

    public int $pearlCoolDown;
    public int $pearlsRemaining = 16;

    public int $potCoolDown;
    public int $throwingPotTick = 0;
    public ?SplashPotionEntity $pot = null;
    public ?Vector3 $lastPlayerPos = null;
    public int $comboHits = 0;
    public bool $agro = false;

    public int $hotBarPots = 7;
    public int $totalPots = 34;

    public float $speed = 0.95;

    public int $refillTicks;

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
        $this->pearlCoolDown = 0;
        $this->potCoolDown = 0;
        $this->refillTicks = 0;
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

    public function turnBack() : void{
        $lastLocation = $this->getTarget()?->getLocation() ?? throw new AssumptionFailedError('Target should not be null at this point');
        $xDist = $lastLocation->x - $this->getPosition()->x;
        $zDist = $lastLocation->z - $this->getPosition()->z;
        $yaw = rad2deg(atan2($zDist, $xDist)) - 90;
        if($yaw < 0){
            $yaw += 360.0;
        }
        $this->setRotation(fmod($yaw - 180, 360), $this->onGround ? 15 : -15);
    }

    private function followPot(): void
    {
        if ($this->pot === null) {
            $this->pot = null;
            $this->throwingPotTick = 0;
            return;
        }
        if (!$this->pot->isAlive()) {
            return;
        }
        $botPosition = $this->getPosition();
        $potPosition = $this->pot->getPosition();
        $x = $potPosition->x - $botPosition->getX();
        $z = $potPosition->z - $botPosition->getZ();
        if (abs($x) > 0 || abs($z) > 0) {
            $distance = abs($x) + abs($z);
            $this->motion->x = 0.85 * ($x / $distance);
            $this->motion->z = 0.85 * ($z / $distance);
        }
        $this->setMotion($this->motion);
    }

    public function pearl(bool $target): void
    {
        $this->getInventory()->setHeldItemIndex(1);
        $pitch_value = -15;
        if ($target) {
			$player = $this->target;
			if($player === null){
				throw new AssumptionFailedError('Target should not be null at this point');
			}
            $this->lookAt($player->getPosition()->add(0, 0.7, 0));
            $dist = $this->getPosition()->distance($player->getPosition());

            if ($dist > 18) $pitch_value = -25;
            else if ($dist > 14) $pitch_value = -15;
            else if ($dist > 10) $pitch_value = -8;
        }

        $location = $this->getLocation();
        $this->setRotation($location->yaw, $pitch_value);
        $location = $this->getLocation();

        $projectile = new EnderPearlEntity(Location::fromObject($this->getEyePos(), $location->getWorld(), $location->getYaw(), $location->getPitch()), $this);
        $projectile->setMotion($this->getDirectionVector()->multiply(2.5 / 1.5));

        ($ev = new ProjectileLaunchEvent($projectile))->call();
        if ($ev->isCancelled()) {
            $projectile->flagForDespawn();
            return;
        }

        $projectile->spawnToAll();

        $location->getWorld()->addSound($location, new ThrowSound());

        $this->getInventory()->setHeldItemIndex(0);

        $this->pearlsRemaining--;
        $this->pearlCoolDown = self::PEARL_COOLDOWN;
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

        $pos = $this->getPosition();

        if (!$this->tBag) {
            if ($this->attackCoolDown >= 0) $this->attackCoolDown--;
            if ($this->pearlCoolDown >= 0) $this->pearlCoolDown--;
            if ($this->potCoolDown >= 0) $this->potCoolDown--;

            if ($this->cpsTicks < PHP_INT_MAX) $this->cpsTicks++;
            else $this->cpsTicks = 0;

            if ($this->refillTicks > 0) {
                $this->refillTicks--;
                return true;
            }

            if ($this->throwingPotTick >= 0) {
                if ($this->throwingPotTick === 18) {
                    $this->setRotation($this->getLocation()->yaw, -45);

                    $location = $this->getLocation();

                    $motion = $this->getDirectionVector();
                    $motion = $motion->multiply(0.2);

                    $this->pot = new SplashPotionEntity(Location::fromObject($this->getEyePos(), $location->getWorld(), $location->getYaw(), $location->getPitch()), $this, PotionType::STRONG_HEALING(), $motion);
                    ($ev = new ProjectileLaunchEvent($this->pot))->call();
                    if ($ev->isCancelled()) {
                        $this->pot->flagForDespawn();
                        return true;
                    }

                    $this->hotBarPots--;
                    $this->totalPots--;

                    $this->pot->spawnToAll();
                    $location->getWorld()->addSound($location, new ThrowSound());
                } elseif ($this->throwingPotTick === 10) {
                    $this->getInventory()->setHeldItemIndex(0);
                    $this->lookAt($this->getTarget()?->getPosition()->add(0, 0.7, 0) ?? throw new AssumptionFailedError('Target should not be null at this point'));
                }
                $this->followPot();
                $this->throwingPotTick--;
                return true;
            }

            if ($this->getHealth() < 10 && $this->potCoolDown <= 0 && $this->hotBarPots > 0) {
                $this->turnBack();
                $this->getInventory()->setHeldItemIndex(2);
                $this->throwingPotTick = 18;
                return true;
            }

            if ($this->hotBarPots <= 0 && $this->totalPots > 0) {
                $this->turnBack();
                if ($this->pearlCoolDown <= 0 && $this->pearlsRemaining > 0) $this->pearl(false);

                if ($this->pearlsRemaining <= 0) $refill = 8;
                else $refill = 7;

                $refill = min($refill, $this->totalPots);
                $this->refillTicks = $this->getRefillPerSlotTicks() * $refill;
                $this->hotBarPots += $refill;
                $this->totalPots -= $refill;
                return true;
            }

            $distance = $pos->distance($position);

            if ($this->pearlCoolDown <= 0 && $this->pearlsRemaining > 0 && ($distance > $this->blocksBeforePearl() || $this->comboHits > 6)) {
                if ($this->comboHits > 6) $this->agro = true;

                $this->pearl(true);
                return true;
            }
        }

        if ($this->isOnGround()) {
            $facing = $this->getHorizontalFacing();

            if ($this->tBag) {
                if ($this->ticksLived % 7 === 0) {
                    if ($this->sneaking) $this->setSneaking(false);
                    else $this->setSneaking();
                }
                return true;
            }

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
        }

        return $this->attackTarget();
    }

    private function goTo(Vector3 $position): void
    {
        $pos = $this->getPosition()->asVector3();
        $x = $position->x - $pos->getX();
        $z = $position->z - $pos->getZ();
        if (abs($x) > 0 || abs($z) > 0) {
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
            $this->speed = 0.85;
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