<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\player\cosmetic\misc\pack;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\player\cosmetic\CosmeticManager;

class PackReward
{
    public int $type;
	/** @var numeric-string */
    public string $cosmeticID;
    public int $chance;
    public int $duplicate;
    public Item $item;

	/** @param numeric-string $cosmeticID */
    public function __construct(int $type, string $cosmeticID, int $chance)
    {
        $this->type = $type;
        $this->cosmeticID = $cosmeticID;
        $this->chance = $chance;
        $g = match ($type) {
            CosmeticManager::CAPE => CosmeticManager::getCapeFromId($cosmeticID),
            CosmeticManager::ARTIFACT => CosmeticManager::getArtifactFromId($cosmeticID),
            CosmeticManager::PROJECTILE => CosmeticManager::getProjectileFromId($cosmeticID),
            default => null
        };

        if ($g === null){
			$this->item = VanillaItems::AIR();
			return;
		}
        $i = match ($type) {
            CosmeticManager::CAPE => VanillaItems::FEATHER()->setCustomName('§r§l§dCapes'),
            CosmeticManager::ARTIFACT => VanillaBlocks::CARVED_PUMPKIN()->asItem()->setCustomName('§r§l§cCostumes'),
            CosmeticManager::PROJECTILE => VanillaItems::BOW()->setCustomName('§r§l§bProjectile Trails'),
            default => throw new AssumptionFailedError('Unreachable')
        };

        $this->item = $i->setCustomName($g->getItem()->getCustomName());
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getChance(): int
    {
        return $this->chance;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

	/** @return numeric-string */
    public function getCID(): string
    {
        return $this->cosmeticID;
    }

    public function getDuplicateReward(): int
    {
        return $this->duplicate;
    }
}