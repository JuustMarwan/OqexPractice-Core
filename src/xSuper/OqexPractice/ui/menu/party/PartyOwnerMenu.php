<?php

namespace xSuper\OqexPractice\ui\menu\party;

use muqsit\customsizedinvmenu\libs\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\menu\CustomInventory;
use xSuper\OqexPractice\ui\menu\Menus;

class PartyOwnerMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(27);
    }

    public function getTitle(Player $player): string
    {
        return 'Your Party';
    }

    public function handle(DeterministicInvMenuTransaction $transaction): void
    {
        $slot = $transaction->getAction()->getSlot();
        $player = $transaction->getPlayer();
        if(!$player instanceof PracticePlayer){
            throw new AssumptionFailedError('$player should be a PracticePlayer');
        }
        if ($slot === 10) {
            if ($player->getParty() === null) {
                $player->sendMessage('§r§cYou are not in a party!');
                return;
            }

            $party = Party::getParty($player->getParty());
            if ($party === null) return;

            if ($party->getOwner() !== $player->getUniqueId()->toString()) {
                $player->sendMessage('§r§cYou are not the party owner!');
                return;
            }

            if ($party->getDuel() !== null) {
                $player->sendMessage('§r§cYour party is already in a duel!');
                return;
            }

            Menus::DUEL_REQUEST()->create($player, ['recipient' => null]);
        } else if ($slot === 12) {
            if (($partyId = $player->getParty()) === null) {
                $player->removeCurrentWindow();
                $player->sendMessage('§r§cYou are not in a party!');
                return;
            }
            $party = Party::getParty($partyId) ?? throw new AssumptionFailedError('Party should exist');
            Menus::PARTY_MEMBERS()->create($player);
        } else if ($slot === 14) {
            Menus::PARTY_INVITE()->create($player);
        } else if ($slot === 16) {
            Menus::PARTY_SCRIM()->create($player);
        }
    }

    public function render(Player $player): void
    {
        $this->getMenu($player)->getInventory()->setContents([
            10 => VanillaItems::IRON_SWORD()->setCustomName('§r§l§cParty Duel')->setLore([
                '§r§7Start a duel with everyone in your party'
            ]),
            12 => VanillaItems::PAPER()->setCustomName('§r§l§bParty Members')->setLore([
                '§r§7Click this to view the members of your',
                '§r§7party, or to kick a member'
            ]),
            14 => VanillaItems::TOTEM()->setCustomName('§r§l§dInvite Players')->setLore([
                '§r§7Invite a player from a list of online players',
                '§r§7or run §d/party invite <player>'
            ]),
            16 => VanillaItems::GOLDEN_SWORD()->setCustomName('§r§l§aParty Scrim')->setLore([
                "§r§7Send a scrim request to another player's party",
                '§r§7or run §a/party scrim <player>'
            ])
        ]);
    }
}