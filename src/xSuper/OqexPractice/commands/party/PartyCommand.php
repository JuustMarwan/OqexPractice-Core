<?php

namespace xSuper\OqexPractice\commands\party;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;

class PartyCommand extends BaseCommand
{
	/** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
    }


    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerSubCommand(new PartyCreateSubCommand('create'));
        $this->registerSubCommand(new PartyInviteSubCommand('invite'));
        $this->registerSubCommand(new PartyKickSubCommand('kick'));
        $this->registerSubCommand(new PartyJoinSubCommand('join'));
        $this->registerSubCommand(new PartyDuelSubCommand('duel'));
        $this->registerSubCommand(new PartyScrimCommand('scrim'));
        $this->registerSubCommand(new PartyLeaveSubCommand('leave'));
    }
}