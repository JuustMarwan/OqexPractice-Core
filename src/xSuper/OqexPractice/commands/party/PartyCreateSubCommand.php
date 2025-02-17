<?php

namespace xSuper\OqexPractice\commands\party;


use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\PracticePlayer;

class PartyCreateSubCommand extends BaseSubCommand
{
	/** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof PracticePlayer) {
            if ($sender->getDuel() !== null){
                $sender->sendMessage('§r§cYou can not run this command while in a duel!');
                return;
            }

            if ($sender->getParty() !== null) {
                $sender->sendMessage('§r§cYou can not run this command while in a party!');
            }

            $sender->sendMessage('§r§l§dPARTY §r§8» §r§7You have created a party, invite players with §d/party invite <player>');
            Party::createParty($sender);
            return;
        }

        $sender->sendMessage('§r§cThis command can only be ran in-game!');
    }

    protected function prepare(): void
    {
        $this->setPermission('oqex');
    }
}