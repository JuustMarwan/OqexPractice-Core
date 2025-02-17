<?php

namespace xSuper\OqexPractice\ui\menu\party;

use muqsit\customsizedinvmenu\libs\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\menu\CustomInventory;
use xSuper\OqexPractice\ui\menu\Menus;

class PartyMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(9);
    }

    public function getTitle(Player $player): string
    {
        return 'Parties';
    }

    public function handle(DeterministicInvMenuTransaction $transaction): void
    {
        $slot = $transaction->getAction()->getSlot();
        $player = $transaction->getPlayer();
        if(!$player instanceof PracticePlayer){
            throw new AssumptionFailedError('$player should be a PracticePlayer');
        }
        if ($slot === 2) {
            $player->removeCurrentWindow();

            if ($player->getParty() !== null) {
                $player->sendMessage('§r§cYou are already in a party!');
                return;
            }

            $player->sendMessage('§r§l§dPARTY §r§8» §r§7You have created a party, invite players with §d/party invite <player>');
            Party::createParty($player);
        } else if ($slot === 6) {
            $invites = array_filter(array_map(Party::getParty(...), array_keys($player->getPartyInvites())), fn(?Party $party) => $party !== null);
            if (count($invites) === 0) {
                $player->removeCurrentWindow();
                $player->sendMessage('§r§7You have no party invites :(');
                return;
            }
            Menus::PARTY_INVITES()->create($player);
        }
    }

    public function render(Player $player): void
    {
        /** @var PracticePlayer $player */
        $invites = count($player->getPartyInvites());

        $this->getMenu($player)->getInventory()->setContents([
            2 => VanillaItems::NETHER_STAR()->setCustomName('§r§l§dCreate Party')->setLore([
                '§r§7Create your own party to duel friends',
                '§r§7or scrim other parties!',
                '§r',
                '§r§8 - §d/party invite <player>',
                '§r§8 - §d/party duel',
                '§r§8 - §d/party scrim <player>'
            ]),
            6 => VanillaBlocks::BARREL()->asItem()->setCustomName('§r§l§dParty Invites')->setLore([
                '§r§7Join someones party by §dclicking §7the invite',
                '§r§7shown in the §dinvites §7list.',
                '§r',
                '§r§8 - §7Your Invites: §d' . $invites
            ])
        ]);
    }
}