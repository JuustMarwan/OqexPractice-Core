<?php

namespace xSuper\OqexPractice\items\custom\staff;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use xSuper\OqexPractice\items\custom\CustomItem;
use xSuper\OqexPractice\player\PracticePlayer;

class FreezeItem extends CustomItem
{
    public function interact(PracticePlayer $p): void
    {
    }

    public function getActualItem(PracticePlayer $player): Item
    {
        $i = VanillaBlocks::ICE()->asItem()->setCustomName('§r§bFreezer');
        $i->getNamedTag()->setString('customItem', $this->getName());
        return $i;
    }
}