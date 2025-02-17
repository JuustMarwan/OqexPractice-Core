<?php

namespace xSuper\OqexPractice\items\custom\lobby;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use xSuper\OqexPractice\duel\queue\QueueManager;
use xSuper\OqexPractice\items\custom\CustomItem;
use xSuper\OqexPractice\items\custom\InteractiveItems;
use xSuper\OqexPractice\player\PracticePlayer;

class LeaveQueueItem extends CustomItem
{
    public function interact(PracticePlayer $p): void
    {
        if (!QueueManager::getInstance()->isInQueue($p)) {
            return;
        }
        QueueManager::getInstance()->removeQueue($p);

        $p->getInventory()->setContents([
            0 => InteractiveItems::DUEL_SWORD()->getActualItem($p),
            1 => InteractiveItems::RANKED_DUEL_SWORD()->getActualItem($p),
            3 => InteractiveItems::FFA()->getActualItem($p),
            5 => InteractiveItems::EVENT()->getActualItem($p),
            7 => InteractiveItems::PARTY()->getActualItem($p),
            8 => InteractiveItems::PROFILE()->getActualItem($p)
        ]);
    }

    public function getActualItem(PracticePlayer $player): Item
    {
        $i = VanillaBlocks::BARRIER()->asItem()->setCustomName('§r§l§cLeave Queue §r§7(Interact)');
        $i->getNamedTag()->setString('customItem', $this->getName());
        return $i;
    }
}