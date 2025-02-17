<?php

namespace xSuper\OqexPractice\items\custom\lobby;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\duel\queue\QueueManager;
use xSuper\OqexPractice\items\custom\CustomItem;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\ui\form\Forms;
use xSuper\OqexPractice\ui\menu\duel\UnrankedDuelMenu;
use xSuper\OqexPractice\ui\menu\Menus;

class DuelSwordItem extends CustomItem
{
    public function interact(PracticePlayer $p): void
    {
        if (QueueManager::getInstance()->isInQueue($p)) {
            return;
        }
        if($p->getData()->getSettings()->getSetting(SettingIDS::UI_TYPE) === SettingIDS::UI_TYPE_CHEST){
            Menus::UNRANKED_DUEL()->create($p, ['rankedGames' => $p->getData()->getTotalRankedGames()]);
        }else{
            $p->sendForm(Forms::UNRANKED_DUEL()->create($p));
        }
    }

    public function getActualItem(PracticePlayer $player): Item
    {
        $i = VanillaItems::IRON_SWORD()->setCustomName('§r§l§6Play Unranked §r§7(Interact)');
        $i->getNamedTag()->setString('customItem', $this->getName());
        return $i;
    }
}