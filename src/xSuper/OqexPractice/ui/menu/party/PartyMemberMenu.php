<?php

namespace xSuper\OqexPractice\ui\menu\party;

use muqsit\customsizedinvmenu\libs\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\ui\form\Forms;
use xSuper\OqexPractice\ui\menu\CustomInventory;
use xSuper\OqexPractice\ui\menu\Menus;

class PartyMemberMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(9);
    }

    public function getTitle(Player $player): string
    {
        return 'Your Party';
    }

    public function handle(DeterministicInvMenuTransaction $transaction): void
    {
        $player = $transaction->getPlayer();
        if(!$player instanceof PracticePlayer){
            throw new AssumptionFailedError('$player should be a PracticePlayer');
        }
        $slot = $transaction->getAction()->getSlot();
        if ($slot === 2) {
            if($player->getData()->getSettings()->getSetting(SettingIDS::UI_TYPE) === SettingIDS::UI_TYPE_CHEST){
                if (($partyId = $player->getParty()) === null) {
                    $player->removeCurrentWindow();
                    $player->sendMessage('§r§cYou are not in a party!');
                    return;
                }
                $party = Party::getParty($partyId) ?? throw new AssumptionFailedError('Party should exist');
                Menus::PARTY_MEMBERS()->create($player);
            }else{
                $player->removeCurrentWindow();
                $transaction->then(static fn() => Forms::PARTY_MEMBERS()->create($player));
            }
        } else if ($slot === 6) {
            $player->removeCurrentWindow();

            if (($partyId = $player->getParty()) === null) {
                $player->removeCurrentWindow();
                $player->sendMessage('§r§cYou are not in a party!');
                return;
            }
            $party = Party::getParty($partyId) ?? throw new AssumptionFailedError('Party should exist');
            $party->kick($player);
        }
    }

    public function render(Player $player): void
    {
        $this->getMenu($player)->getInventory()->setContents([
            2 => VanillaItems::PAPER()->setCustomName('§r§l§bParty Members')->setLore([
                '§r§7Click this to view the members of your',
                '§r§7party, or to view their stats.'
            ]),
            6 => VanillaBlocks::BARRIER()->asItem()->setCustomName('§r§l§cLeave Party')->setLore([
                '§r§7Click this to leave your current party',
                '§r§7or run §c/party leave'
            ])
        ]);
    }
}