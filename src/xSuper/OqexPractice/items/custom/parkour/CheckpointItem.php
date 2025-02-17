<?php

namespace xSuper\OqexPractice\items\custom\parkour;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\items\custom\CustomItem;
use xSuper\OqexPractice\player\PracticePlayer;

class CheckpointItem extends CustomItem
{
    public function interact(PracticePlayer $p): void
    {
        if ($p->getParkour() !== null && $p->getCheckpoint() !== null) {
            $pos = $p->getCheckpoint();

            $p->teleport($pos);
        }
    }

    public function getActualItem(PracticePlayer $player): Item
    {
        $i = VanillaItems::ARROW()->setCustomName('§r§l§6Last Checkpoint §r§7(Interact)');
        $i->getNamedTag()->setString('customItem', $this->getName());
        return $i;
    }
}