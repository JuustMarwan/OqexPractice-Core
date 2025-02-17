<?php

namespace xSuper\OqexPractice\items\custom\lobby;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\items\custom\CustomItem;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\ui\form\Forms;
use xSuper\OqexPractice\ui\menu\event\EventMenu;

class EventItem extends CustomItem
{
    public function interact(PracticePlayer $p): void
    {
        //if($p->getData()->getSettings()->getSetting(SettingIDS::UI_TYPE) === SettingIDS::UI_TYPE_CHEST) {
			//EventMenu::create($p->getData()->getRankPermission() >= RankMap::permissionMap('owner'))->send($p);
        //} else {
            //$p->sendForm(Forms::EVENT()->create($p));
        //}
        $p->sendMessage('Coming soon...');
    }

    public function getActualItem(PracticePlayer $player): Item
    {
        $i = VanillaItems::NETHER_STAR()->setCustomName('§r§l§cEvents §r§7(Interacttest)');
        $i->getNamedTag()->setString('customItem', $this->getName());
        return $i;
    }
}