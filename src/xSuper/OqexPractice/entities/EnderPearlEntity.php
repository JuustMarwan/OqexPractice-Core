<?php

namespace xSuper\OqexPractice\entities;

use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\EnderPearl;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\particle\Particle;
use pocketmine\world\sound\EndermanTeleportSound;
use xSuper\OqexPractice\entities\pathfinder\entity\ArcherEntity;
use xSuper\OqexPractice\entities\pathfinder\entity\SmartEntity;
use xSuper\OqexPractice\player\cosmetic\misc\ParticleInformation;
use xSuper\OqexPractice\player\PracticePlayer;

class EnderPearlEntity extends EnderPearl
{
    public float $gravity = 0.027;
    public float $drag = 0.01;

    private ?Particle $particle = null;

    public function __construct(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null)
    {
        parent::__construct($location, $shootingEntity, $nbt);

        if ($shootingEntity instanceof PracticePlayer) {
            //$this->particle = ParticleInformation::getInformation((int) $shootingEntity->getData()->getCosmetics()->getProjectile(true))?->getParticle();
        }
    }

    public function canCollideWith(Entity $entity): bool
    {
        $owner = $this->getOwningEntity();
        if ($owner instanceof SmartEntity && $entity instanceof PracticePlayer) {
            if ($entity->getVanished() || $entity->getSpectator()) {
                return false;
            }

            return true;
        }

        if ($entity instanceof PracticePlayer) {
            if ($entity->getVanished() || $entity->getSpectator()) {
                return false;
            }

            $owner = $this->getOwningEntity();
            if ($owner instanceof PracticePlayer && !$owner->canSee($entity)) return false;
        }

        return parent::canCollideWith($entity);
    }

    protected function onHit(ProjectileHitEvent $event): void{
        $owner = $this->getOwningEntity();
        if ($owner !== null && $owner->getWorld()->getId() === $this->getWorld()->getId()) {
            if($event instanceof ProjectileHitEntityEvent && $owner instanceof PracticePlayer){
                $owner->setAgro(true);
            }

            $hitResult = $event->getRayTraceResult();
            $pos = $hitResult->getHitVector();

            $targets = [];
            if ($owner instanceof PracticePlayer) {
                foreach ($this->getViewers() as $v) {
                    if ($v->canSee($owner)) $targets[] = $v;
                }
            } else $targets = $this->getViewers();

            $this->getWorld()->addParticle($origin = $owner->getPosition(), new EndermanTeleportParticle(), $targets);
            $this->getWorld()->addSound($origin, new EndermanTeleportSound(), $targets);
            $owner->teleport($pos);
            if ($owner instanceof PracticePlayer) $owner->broadcastMovement(true);

            if($owner instanceof PracticePlayer){
                $owner->getNetworkSession()->syncMovement($location = $owner->getLocation(), $location->yaw, $location->pitch);
            }
            $this->flagForDespawn();
        }
    }

    public function entityBaseTick(int $tickDiff = 1) : bool{
        $hasUpdate = parent::entityBaseTick($tickDiff);
        $owner = $this->getOwningEntity();
        if ($owner instanceof PracticePlayer) {
            foreach ($this->getViewers() as $v) {
                if (!$v->canSee($owner)) $this->despawnFrom($v);
            }
        }

        if($this->particle !== null && !$this->isFlaggedForDespawn() && $this->isAlive()){
            $this->getWorld()->addParticle($this->lastLocation->subtractVector($this->lastMotion), $this->particle);
        }
        return $hasUpdate;
    }
}