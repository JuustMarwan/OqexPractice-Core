<?php

namespace xSuper\OqexPractice\entities;

use pocketmine\color\Color;
use pocketmine\entity\effect\InstantEffect;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\item\PotionType;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\Random;
use pocketmine\world\particle\PotionSplashParticle;
use pocketmine\world\sound\PotionSplashSound;
use xSuper\OqexPractice\entities\pathfinder\entity\ArcherEntity;
use xSuper\OqexPractice\entities\pathfinder\entity\SmartEntity;
use xSuper\OqexPractice\player\PracticePlayer;

class SplashPotionEntity extends SplashPotion
{
    protected float $gravity = 0.07;
    public function __construct(Location $location, ?Entity $shootingEntity, PotionType $potionType, ?Vector3 $motion = null, ?CompoundTag $nbt = null)
    {
        parent::__construct($location, $shootingEntity, $potionType, $nbt);


        if ($shootingEntity !== null) {
			if($motion === null){
				throw new \InvalidArgumentException('Motion should not be null');
			}
            $this->handleMotion($motion->x, $motion->y, $motion->z, 0.39, 0);
        }
    }

    public function handleMotion(float $x, float $y, float $z, float $f1, float $f2): void
    {
        $rand = new Random();
        $f = sqrt($x * $x + $y * $y + $z * $z);
        $x = $x / $f;
        $y = $y / $f;
        $z = $z / $f;
        $x = $x + $rand->nextSignedFloat() * 0.007499999832361937 * $f2;
        $y = $y + $rand->nextSignedFloat() * 0.008599999832361937 * $f2;
        $z = $z + $rand->nextSignedFloat() * 0.007499999832361937 * $f2;
        $x = $x * $f1;
        $y = $y * $f1;
        $z = $z * $f1;
        $this->motion->x += $x;
        $this->motion->y += $y;
        $this->motion->z += $z;
    }

    public function canCollideWith(Entity $entity): bool
    {
        if ($entity instanceof PracticePlayer) {
            if ($entity->getVanished() || $entity->getSpectator()) {
                return false;
            }

            $owner = $this->getOwningEntity();
            if ($owner instanceof PracticePlayer && !$owner->canSee($entity)) return false;
        }

        return parent::canCollideWith($entity);
    }

    protected function onHit(ProjectileHitEvent $event): void
    {
        $effects = $this->getPotionEffects();
        $color = 'default';
        if (count($effects) === 0) {
            $colors = [new Color(0x38, 0x5d, 0xc6)];
            $hasEffects = false;
        } else {
            // if($owner instanceof Player) $color=Utils::potSplashColor($owner);
            // TODO: Potion Colors
            $colors = match ($color) {
                'default' => [new Color(255, 0, 0)]/*,
                'pink' => [new Color(250, 10, 226)],
                'purple' => [new Color(147, 4, 255)],
                'blue' => [new Color(2, 2, 255)],
                'cyan' => [new Color(4, 248, 255)],
                'green' => [new Color(4, 255, 55)],
                'yellow' => [new Color(248, 255, 0)],
                'orange' => [new Color(255, 128, 0)],
                'white' => [new Color(255, 255, 255)],
                'grey' => [new Color(150, 150, 150)],
                'black' => [new Color(0, 0, 0)],
                default => [new Color(0xf8, 0x24, 0x23)],*/
            };
            $hasEffects = true;
        }

        $owner = $this->getOwningEntity();
        $targets = [];
        if ($owner instanceof PracticePlayer) {
            foreach ($this->getViewers() as $v) {
                if ($v->canSee($owner)) $targets[] = $v;
            }
        } else $targets = $this->getViewers();

        $this->getWorld()->addParticle($this->getLocation(), new PotionSplashParticle(Color::mix(...$colors)), $targets);
        $this->broadcastSound(new PotionSplashSound(), $targets);
        if ($hasEffects) {
            foreach ($this->getWorld()->getNearbyEntities($this->getBoundingBox()->expandedCopy(1.7, 5.7, 1.7)) as $nearby) {
                if (($nearby instanceof PracticePlayer || $nearby instanceof SmartEntity || $nearby instanceof ArcherEntity) && $nearby->isAlive()) {
                    $multiplier = 1 - (sqrt($nearby->getEyePos()->distanceSquared($this->getLocation())) / 6.15);
                    if ($multiplier > 0.578) $multiplier = 0.578;
                    if ($event instanceof ProjectileHitEntityEvent && $nearby === $event->getEntityHit()) $multiplier = 0.580;

                    foreach ($this->getPotionEffects() as $effect) {
                        if ($effect->getType() instanceof InstantEffect) {
                            $effect->getType()->applyEffect($nearby, $effect, $effect->getAmplifier() * $multiplier * 1.75, $this);
                        } else {
                            $newDuration = (int) round($effect->getDuration() * 1.75 * $multiplier);
                            if($newDuration < 20){
                                continue;
                            }

                            $effect->setDuration($newDuration);
                            $nearby->getEffects()->add($effect);
                        }
                    }
                }
            }
        }
    }

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        $owner = $this->getOwningEntity();
        if ($owner instanceof PracticePlayer) {
            foreach ($this->getViewers() as $v) {
                if (!$v->canSee($owner)) $this->despawnFrom($v);
            }
        }

        return parent::entityBaseTick($tickDiff);
    }
}