<?php

namespace xSuper\OqexPractice\items\custom\lobby;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use xSuper\OqexPractice\items\custom\CustomItem;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\ui\form\Forms;
use xSuper\OqexPractice\ui\menu\Menus;
use xSuper\OqexPractice\ui\menu\party\PartyMemberMenu;
use xSuper\OqexPractice\ui\menu\party\PartyMenu;
use xSuper\OqexPractice\ui\menu\party\PartyOwnerMenu;

class PartyItem extends CustomItem
{
    public function interact(PracticePlayer $p): void
    {
        if ($p->getParty() === null) {
            if($p->getData()->getSettings()->getSetting(SettingIDS::UI_TYPE) === SettingIDS::UI_TYPE_CHEST){
				Menus::PARTY()->create($p);
            }else{
                $p->sendForm(Forms::PARTY()->create($p));
            }
        } else {
            $party = Party::getParty($p->getParty());
            if ($party === null) return;

            if ($party->getOwner() === $p->getUniqueId()->toString()) {
                if($p->getData()->getSettings()->getSetting(SettingIDS::UI_TYPE) === SettingIDS::UI_TYPE_CHEST){
					Menus::PARTY_OWNER()->create($p);
                }else{
                    $p->sendForm(Forms::PARTY_OWNER()->create($p));
                }
            } else if($p->getData()->getSettings()->getSetting(SettingIDS::UI_TYPE) === SettingIDS::UI_TYPE_CHEST){
                Menus::PARTY_MEMBER()->create($p);
            }else{
                $p->sendForm(Forms::PARTY_MEMBER()->create($p));
            }
        }
    }

    public function getActualItem(PracticePlayer $player): Item
    {
        $i = VanillaItems::TOTEM()->setCustomName('§r§l§dParty §r§7(Interact)');
        $i->getNamedTag()->setString('customItem', $this->getName());
        return $i;
    }
}