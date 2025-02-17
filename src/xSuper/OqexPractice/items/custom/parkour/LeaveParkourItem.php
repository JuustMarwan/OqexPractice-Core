<?php

namespace xSuper\OqexPractice\items\custom\parkour;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use xSuper\OqexPractice\items\custom\CustomItem;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;

class LeaveParkourItem extends CustomItem
{
    public function interact(PracticePlayer $p): void
    {
        if ($p->getParkour() !== null && $p->getCheckpoint() !== null) {
            $p->endParkour();
            $p->reset(OqexPractice::getInstance());
        }
    }

    public function getActualItem(PracticePlayer $player): Item
    {
        $i = VanillaBlocks::BARRIER()->asItem()->setCustomName('§r§l§cQuit Parkour §r§7(Interact)');
        $i->getNamedTag()->setString('customItem', $this->getName());
        return $i;
    }
}