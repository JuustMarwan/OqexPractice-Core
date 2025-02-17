<?php

namespace xSuper\OqexPractice\items\custom\lobby;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use xSuper\OqexPractice\items\custom\CustomItem;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\menu\Menus;

class ProfileItem extends CustomItem
{
    public function interact(PracticePlayer $p): void
    {
		Menus::PROFILE()->create($p);
    }

    public function getActualItem(PracticePlayer $player): Item
    {
        $i = VanillaBlocks::REDSTONE_TORCH()->asItem()->setCustomName('§r§l§aProfile §r§7(Interact)');
        $i->getNamedTag()->setString('customItem', $this->getName());
        return $i;
    }
}