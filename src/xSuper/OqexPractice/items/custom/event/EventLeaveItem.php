<?php

namespace xSuper\OqexPractice\items\custom\event;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\items\custom\CustomItem;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;

class EventLeaveItem extends CustomItem
{
    public function interact(PracticePlayer $p): void
    {
        $event = $p->getEvent();
		if($event === null){
			throw new AssumptionFailedError('This should not be null at this point');
		}
		$event->leave($p->getUniqueId()->toString());
        $p->reset(OqexPractice::getInstance());
    }

    public function getActualItem(PracticePlayer $player): Item
    {
        $i = VanillaBlocks::BARRIER()->asItem()->setCustomName('§r§l§cLeave Event §r§7(Interact)');
        $i->getNamedTag()->setString('customItem', $this->getName());
        return $i;
    }
}