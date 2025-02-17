<?php

namespace xSuper\OqexPractice\commands\party;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\ui\form\Forms;
use xSuper\OqexPractice\ui\menu\duel\DuelRequestMenu;
use xSuper\OqexPractice\ui\menu\Menus;

class PartyDuelSubCommand extends BaseSubCommand
{
	/** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof PracticePlayer) {
            if ($sender->getParty() === null) {
                $sender->sendMessage('§r§cYou are not in a party!');
                return;
            }

            $party = Party::getParty($sender->getParty());
            if ($party === null) return;

            if ($party->getOwner() !== $sender->getUniqueId()->toString()) {
                $sender->sendMessage('§r§cYou are not the party owner!');
                return;
            }

            if ($party->getDuel() !== null) {
                $sender->sendMessage('§r§cYour party is already in a duel!');
                return;
            }

            if($sender->getData()->getSettings()->getSetting(SettingIDS::UI_TYPE) === SettingIDS::UI_TYPE_CHEST){
                Menus::DUEL_REQUEST()->create($sender, ['recipient' => $sender]);
            }else{
                $sender->sendForm(Forms::DUEL_REQUEST()->create($sender, $sender));
            }
            return;
        }

        $sender->sendMessage('§r§cThis command can only be ran in-game!');
    }

    protected function prepare(): void
    {
        $this->setPermission('oqex');
    }
}