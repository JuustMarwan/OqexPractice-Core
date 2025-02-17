<?php

namespace xSuper\OqexPractice\items\custom\lobby;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\duel\queue\QueueManager;
use xSuper\OqexPractice\duel\utils\Elo;
use xSuper\OqexPractice\items\custom\CustomItem;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\ui\form\Forms;
use xSuper\OqexPractice\ui\menu\duel\RankedDuelMenu;
use xSuper\OqexPractice\ui\menu\Menus;

class RankedDuelSwordItem extends CustomItem
{
    public function interact(PracticePlayer $p): void
    {
        if (QueueManager::getInstance()->isInQueue($p)) {
            return;
        }
        if($p->getData()->getSettings()->getSetting(SettingIDS::UI_TYPE) === SettingIDS::UI_TYPE_CHEST){
			$rankName = Elo::getLadderRank($p->getData()->getAverageElo());
			if($rankName === null){
				$p->sendMessage('You haven\'t played any games yet');
				return;
			}
			$elos = $p->getData()->getElos();
			unset($elos['average']);
            Menus::RANKED_DUEL()->create($p, ['elos' => $elos, 'rankName' => $rankName, 'rankedGames' => $p->getData()->getTotalRankedGames()]);
        }else{
            $p->sendForm(Forms::RANKED_DUEL()->create($p));
        }
    }

    public function getActualItem(PracticePlayer $player): Item
    {
        $i = VanillaItems::GOLDEN_SWORD()->setCustomName('§r§l§6Play Ranked §r§7(Interact)');
        $i->getNamedTag()->setString('customItem', $this->getName());
        return $i;
    }
}