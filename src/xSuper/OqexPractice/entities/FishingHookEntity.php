<?php

namespace xSuper\OqexPractice\entities;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\ItemTypeIds;
use pocketmine\math\RayTraceResult;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\utils\Random;
use xSuper\OqexPractice\player\PracticePlayer;

class FishingHookEntity extends Projectile
{
    protected float $gravity = 0.1;
    private bool $flame = false;

    public function __construct(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null)
    {
        parent::__construct($location, $shootingEntity, $nbt);
        if ($shootingEntity instanceof PracticePlayer) {
            $this->setPosition($this->location->add(0, $shootingEntity->getEyeHeight() - 1.5, 0));
            $this->setMotion($shootingEntity->getDirectionVector()->multiply(1.5));
            $shootingEntity->startFishing($this);
            $this->handleHookCasting($this->motion->x, $this->motion->y, $this->motion->z, 1.0, 1.0);
        }
    }

    public function flame(): void
    {
        $this->flame = true;
    }

    public function onHitEntity(Entity $entityHit, RayTraceResult $hitResult): void
    {
        $damage = $this->getResultDamage();
        $owner = $this->getOwningEntity();
        if ($owner === null) $ev = new EntityDamageByEntityEvent($this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
        else {
            if ($owner instanceof PracticePlayer) $owner->stopFishing();
            $ev = new EntityDamageByChildEntityEvent($owner, $this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
        }

        if (!$ev->isCancelled()) {
            $entityHit->attack($ev);
            if ($this->flame) $entityHit->setOnFire(2);
        }
        //$deltaX=$entityHit->x - $owner->x;
        //$deltaZ=$entityHit->z - $owner->z;
        //$entityHit->knockBack($owner, 1, $deltaX, $deltaZ, 0.300);
        //$entityHit->setMotion($owner->getDirectionVector()->multiply(0)->add(0, 0.25, 0));
        $this->isCollided = true;
        $this->flagForDespawn();
    }

    protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult): void
    {
        $owner = $this->getOwningEntity();
        if ($owner instanceof PracticePlayer) $owner->stopFishing();
        parent::onHitBlock($blockHit, $hitResult);
    }

    public function handleHookCasting(float $x, float $y, float $z, float $f1, float $f2): void
    {
        $rand = new Random();
        $f = sqrt($x * $x + $y * $y + $z * $z);
        $x = $x / $f;
        $y = $y / $f;
        $z = $z / $f;
        $x = $x + $rand->nextSignedFloat() * 0.007499999832361937 * $f2;
        $y = $y + $rand->nextSignedFloat() * 0.007499999832361937 * $f2;
        $z = $z + $rand->nextSignedFloat() * 0.007499999832361937 * $f2;
        $x = $x * $f1;
        $y = $y * $f1;
        $z = $z * $f1;
        $this->motion->x += $x;
        //$this->motion->y += $y;
        $this->motion->y = $y;
        $this->motion->z += $z;
    }

    public function entityBaseTick(int $tickDiff=1): bool
    {
        $hasUpdate = parent::entityBaseTick($tickDiff);
        $owner = $this->getOwningEntity();
        if ($owner instanceof PracticePlayer) {
            if ($owner->getInventory()->getItemInHand()->getTypeId() !== ItemTypeIds::FISHING_ROD || !$owner->isAlive() || $owner->isClosed() || $this->isClosed()) $this->flagForDespawn();
        } else $this->flagForDespawn();

        if ($this->ticksLived >= 10) {
            if ($owner instanceof PracticePlayer) $owner->stopFishing();
            $this->flagForDespawn();
        }

        return $hasUpdate;
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.2, 0.2);
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::FISHING_HOOK;
    }

    public function canCollideWith(Entity $entity): bool
    {
        if ($entity instanceof PracticePlayer) {
            if ($entity->getVanished() || $entity->getSpectator()) {
                return false;
            }
        }

        return parent::canCollideWith($entity);
    }

    protected function getInitialDragMultiplier(): float
    {
        return 0.01;
    }

    protected function getInitialGravity(): float
    {
        return 0.01;
    }
}