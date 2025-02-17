<?php

namespace xSuper\OqexPractice\entities;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Arrow;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\item\VanillaItems;
use pocketmine\math\RayTraceResult;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\EntityEventBroadcaster;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\player\Player;
use pocketmine\world\particle\Particle;
use xSuper\OqexPractice\duel\type\BuildUHCType;
use xSuper\OqexPractice\ffa\OITCFFA;
use xSuper\OqexPractice\player\cosmetic\misc\ParticleInformation;
use xSuper\OqexPractice\player\PracticePlayer;

class ArrowEntity extends Arrow
{
    private ?Particle $particle = null;

    public function __construct(Location $location, ?Entity $shootingEntity, bool $critical, ?CompoundTag $nbt = null)
    {
        parent::__construct($location, $shootingEntity, $critical, $nbt);

        if ($shootingEntity instanceof PracticePlayer) {
            //$this->particle = ParticleInformation::getInformation((int) $shootingEntity->getData()->getCosmetics()->getProjectile(true))?->getParticle();
            if ($shootingEntity->getFFA() instanceof OITCFFA) {
                $shootingEntity->arrowTask();
            }
        }
    }

    public function canCollideWith(Entity $entity): bool
    {
        if ($entity instanceof PracticePlayer && $this->getOwningEntityId() !== null && $entity->getId() !== $this->getOwningEntityId()) {
            if ($entity->getVanished() || $entity->getSpectator()) {
                return false;
            }

            $owner = $this->getOwningEntity();
            if ($owner instanceof PracticePlayer && !$owner->canSee($entity)) return false;
        }

        return parent::canCollideWith($entity);
    }

    public function onHitEntity(Entity $entity, RayTraceResult $hitResult): void
    {
        if ($entity instanceof PracticePlayer) {
            if ($entity->getFFA() instanceof OITCFFA) {
                $damage = $this->getResultDamage();

                if ($this->getOwningEntity() instanceof PracticePlayer && $this->getOwningEntity()->getId() === $entity->getId()) {
                    $this->flagForDespawn();
                    return;
                }

                if($damage >= 0) {
                    if ($this->getOwningEntity() instanceof PracticePlayer) {
                        $ev = new EntityDamageByChildEntityEvent($this->getOwningEntity(), $this, $entity, EntityDamageEvent::CAUSE_PROJECTILE, 100);
                    } else {
                        $ev = new EntityDamageByEntityEvent($this, $entity, EntityDamageEvent::CAUSE_PROJECTILE, 100);
                    }

                    $entity->attack($ev);
                    $this->flagForDespawn();
                    return;
                }
            }
        }

        parent::onHitEntity($entity, $hitResult);
    }

    public function onCollideWithPlayer(Player $player) : void{
        if($this->blockHit === null){
            return;
        }

        if (!$player instanceof PracticePlayer) return;

        if (($d = $player->getDuel()) !== null && !$d->getType() instanceof BuildUHCType) return;
        if (($f = $player->getFFA()) !== null && $f instanceof OITCFFA) return;
        if ($player->getVanished() || $player->getSpectator()) return;

        $item = VanillaItems::ARROW();
        $playerInventory = match(true){
            !$player->hasFiniteResources() => null, //arrows are not picked up in creative
            $player->getOffHandInventory()->getItem(0)->canStackWith($item) && $player->getOffHandInventory()->canAddItem($item) => $player->getOffHandInventory(),
            $player->getInventory()->canAddItem($item) => $player->getInventory(),
            default => null
        };

        $ev = new EntityItemPickupEvent($player, $this, $item, $playerInventory);
        if($player->hasFiniteResources() && $playerInventory === null){
            $ev->cancel();
        }
        if($this->pickupMode === self::PICKUP_NONE || ($this->pickupMode === self::PICKUP_CREATIVE && !$player->isCreative())){
            $ev->cancel();
        }

        $ev->call();
        if($ev->isCancelled()){
            return;
        }

        NetworkBroadcastUtils::broadcastEntityEvent(
            $this->getViewers(),
            fn(EntityEventBroadcaster $broadcaster, array $recipients) => $broadcaster->onPickUpItem($recipients, $player, $this)
        );

        $ev->getInventory()?->addItem($ev->getItem());
        $this->flagForDespawn();
    }

    public function onHitBlock(Block $blockHit, RayTraceResult $hitResult): void
    {
        $this->flagForDespawn();
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