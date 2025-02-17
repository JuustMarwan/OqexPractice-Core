<?php

namespace xSuper\OqexPractice\commands\staff;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use xSuper\OqexPractice\commands\arguments\PlayerArgument;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\PracticePlayer;

class TeleportCommand extends BaseCommand
{
	/** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof PracticePlayer) {
            if (!$sender->isLoaded()) return;
            if ($sender->getData()->getRankPermission() < RankMap::permissionMap('helper')) {
                $sender->sendMessage('§r§cYou do not have permission to run this command!');
                return;
            }

            $p = $args['to'] ?? null;

            if ($p === null) {
                $sender->sendMessage('§r§cYou need to specify a player!');
                return;
            }

            if (!$p instanceof PracticePlayer || !$p->isOnline()) {
                $sender->sendMessage('§r§cThat player is not online!');
                return;
            }

            $from = $args['from'] ?? $sender;

            if (!$from instanceof PracticePlayer || !$from->isOnline()) {
                $sender->sendMessage('§r§cThat player is not online!');
                return;
            }

			$from->preTeleport($p->getPosition());

            return;
        }

        $sender->sendMessage('§r§cThis command is only available in-game!');
    }

    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new PlayerArgument('to', true));
        $this->registerArgument(1, new PlayerArgument('from', true));
    }
}