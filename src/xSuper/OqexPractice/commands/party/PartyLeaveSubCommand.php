<?php

namespace xSuper\OqexPractice\commands\party;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\PracticePlayer;

class PartyLeaveSubCommand extends BaseSubCommand
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

            $party->kick($sender);
            return;
        }

        $sender->sendMessage('§r§cThis command can only be ran in-game!');
    }

    protected function prepare(): void
    {
        $this->setPermission('oqex');
    }
}