<?php

namespace xSuper\OqexPractice\commands\party;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use xSuper\OqexPractice\commands\arguments\PlayerArgument;
use xSuper\OqexPractice\player\PracticePlayer;

class PartyJoinSubCommand extends BaseSubCommand
{
	/** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof PracticePlayer) {
            $player = $args['player'] ?? null;

            if ($player === null) {
                $sender->sendMessage('§r§cYou need to specify a player!');
                return;
            }

            if (!$player instanceof PracticePlayer || !$player->isOnline()) {
                $sender->sendMessage('§r§cThat player is not online!');
                return;
            }

            if (!$sender->hasPartyInvite($player)) {
                $sender->sendMessage('§r§cYou do not have an invite from that party!');
                return;
            }

            if ($player->getParty() === null) {
                $sender->sendMessage('§r§cThat player is not in a party!');
                return;
            }

            if ($sender->getParty() !== null) {
                $sender->sendMessage('§r§cYou are already in a party!');
                return;
            }

            $sender->acceptPartyInvite($player);
            return;
        }

        $sender->sendMessage('§r§cThis command can only be ran in-game!');
    }

    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new PlayerArgument('player', true));
    }
}