<?php

namespace xSuper\OqexPractice\entities\firework;

use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\math\VoxelRayTrace;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\utils\Utils;

class FireworkRocket extends Entity
{

    public const TAG_FIREWORK_DATA = "Fireworks"; //TAG_Compound
    public const TAG_EXPLOSIONS = "Explosions"; //TAG_List

    public static function getNetworkTypeId(): string
    {
        return EntityIds::FIREWORKS_ROCKET;
    }

    /* Maximum number of ticks this will live for. */
    protected int $lifeTicks;

    /** @var FireworkRocketExplosion[] */
    protected array $explosions = [];

    /**
     * @param FireworkRocketExplosion[] $explosions
     */
    public function __construct(Location $location, int $lifeTicks, array $explosions, ?CompoundTag $nbt = null)
    {
        if ($lifeTicks < 0) {
            throw new \InvalidArgumentException("Life ticks cannot be negative");
        }
        $this->lifeTicks = $lifeTicks;
        $this->setExplosions($explosions);

        parent::__construct($location, $nbt);
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.25, 0.25);
    }

    protected function getInitialDragMultiplier(): float
    {
        return 0.0;
    }

    protected function getInitialGravity(): float
    {
        return 0.0;
    }

    /**
     * Returns maximum number of ticks this will live for.
     */
    public function getLifeTicks(): int
    {
        return $this->lifeTicks;
    }

    /**
     * Sets maximum number of ticks this will live for.
     *
     * @return $this
     */
    public function setLifeTicks(int $lifeTicks): self
    {
        if ($lifeTicks < 0) {
            throw new \InvalidArgumentException("Life ticks cannot be negative");
        }
        $this->lifeTicks = $lifeTicks;
        return $this;
    }

    /**
     * @return FireworkRocketExplosion[]
     */
    public function getExplosions(): array
    {
        return $this->explosions;
    }

    /**
     * @param FireworkRocketExplosion[] $explosions
     *
     * @return $this
     */
    public function setExplosions(array $explosions): self
    {
        Utils::validateArrayValueType($explosions, function (FireworkRocketExplosion $_): void {
        });
        $this->explosions = $explosions;
        return $this;
    }

    /**
     * TODO: The entity should be saved and loaded, but this is not possible.
     * @see https://bugs.mojang.com/browse/MCPE-165230
     */
    public function canSaveWithChunk(): bool
    {
        return false;
    }

    protected function onFirstUpdate(int $currentTick): void
    {
        parent::onFirstUpdate($currentTick);

        $this->broadcastSound(new FireworkLaunchSound(), [$this->getOwningEntity()]);
    }

    protected function entityBaseTick(int $tickDiff = 1): bool
    {
        $hasUpdate = parent::entityBaseTick($tickDiff);

        if (!$this->isFlaggedForDespawn()) {
            $this->addMotion($this->motion->x * 0.15, 0.04, $this->motion->z * 0.15);

            if ($this->ticksLived >= $this->lifeTicks) {
                $this->explode();
            }
        }

        return $hasUpdate;
    }

    public function explode(): void
    {
        if (count($this->explosions) !== 0) {
            $this->broadcastAnimation(new FireworkParticlesAnimation($this), [$this->getOwningEntity()]);
            foreach ($this->explosions as $explosion) {
                $this->broadcastSound($explosion->getType()->getSound(), [$this->getOwningEntity()]);
                if ($explosion->willTwinkle()) {
                    $this->broadcastSound(new FireworkCrackleSound(), [$this->getOwningEntity()]);
                }
            }
        }

        $this->flagForDespawn();
    }

    public function canBeCollidedWith(): bool
    {
        return false;
    }

    protected function syncNetworkData(EntityMetadataCollection $properties): void
    {
        parent::syncNetworkData($properties);

        $explosions = new ListTag();
        foreach ($this->explosions as $explosion) {
            $explosions->push($explosion->toCompoundTag());
        }
        $fireworksData = CompoundTag::create()
            ->setTag(self::TAG_FIREWORK_DATA, CompoundTag::create()
                ->setTag(self::TAG_EXPLOSIONS, $explosions)
            );

        $properties->setCompoundTag(EntityMetadataProperties::FIREWORK_ITEM, new CacheableNbt($fireworksData));
    }
}