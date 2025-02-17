<?php

namespace xSuper\OqexPractice\items\custom\lobby;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\items\custom\CustomItem;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\ui\form\Forms;
use xSuper\OqexPractice\ui\menu\ffa\FFASelectionMenu;
use xSuper\OqexPractice\ui\menu\Menus;

class FFASelectionItem extends CustomItem
{
    public function interact(PracticePlayer $p): void
    {
        if ($p->getFFA() === null) {
            if ($p->getData()->getSettings()->getSetting(SettingIDS::UI_TYPE) === SettingIDS::UI_TYPE_CHEST) {
                Menus::FFA_SELECTION()->create($p);
            } else {
                $p->sendForm(Forms::FFA_SELECTION()->create($p));
            }
        }
    }

    public function getActualItem(PracticePlayer $player): Item
    {
        $i = VanillaItems::DIAMOND_AXE()->setCustomName('§r§l§bPlay FFA §r§7(Interact)')->setUnbreakable();
        $i->getNamedTag()->setString('customItem', $this->getName());
        return $i;
    }
}