<?php

namespace xSuper\OqexPractice\items\custom\staff;

use pocketmine\block\utils\DyeColor;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\items\custom\CustomItem;
use xSuper\OqexPractice\items\custom\InteractiveItems;
use xSuper\OqexPractice\player\PracticePlayer;

class VanishItem extends CustomItem
{
    public function interact(PracticePlayer $p): void
    {
        if ($p->getVanished()) $p->unVanish(true);
        else $p->vanish(true);

        $p->getInventory()->setContents([
            5 => InteractiveItems::FREEZE()->getActualItem($p),
            7 => InteractiveItems::VANISH()->getActualItem($p)
        ]);
    }

    public function getActualItem(PracticePlayer $player): Item
    {
        if ($player->getVanished()) {
            $i = VanillaItems::DYE()->setColor(DyeColor::LIME());
            $i->setCustomName('§r§aYou are vanished');
        } else {
            $i = VanillaItems::DYE()->setColor(DyeColor::GRAY());
            $i->setCustomName('§r§cYou are not vanished');
        }

        $i->getNamedTag()->setString('customItem', $this->getName());
        return $i;
    }
}