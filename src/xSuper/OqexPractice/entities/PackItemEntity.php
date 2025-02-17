<?php

namespace xSuper\OqexPractice\entities;

use pocketmine\entity\Location;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;

class PackItemEntity extends ItemEntity
{
    public bool $gravityEnabled = false;

    public function __construct(Location $location, Item $item, ?CompoundTag $nbt = null)
    {
        parent::__construct($location, $item, $nbt);
        $this->setNoClientPredictions(true);
    }

    public function onCollideWithPlayer(Player $player): void
    {
    }

    public function spawnTo(Player $player): void
    {
        $owner = $this->getOwningEntity();
        if ($owner !== null && $player->getId() === $owner->getId()) parent::spawnTo($player);
    }

    public function spawnToAll(): void
    {

    }
}