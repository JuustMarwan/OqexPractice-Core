<?php

namespace xSuper\OqexPractice\commands\duel;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\world\Position;
use xSuper\OqexPractice\commands\arguments\PlayerArgument;
use xSuper\OqexPractice\player\PracticePlayer;

class SpectateCommand extends BaseCommand
{
	/** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof PracticePlayer) {
            $player = $args['player'] ?? null;

            if ($sender->getDuel() !== null){
                $sender->sendMessage('§r§cYou can not run this command while in a duel!');
                return;
            }

            if ($player === null) {
                $sender->sendMessage('§r§cYou need to specify a player!');
                return;
            }

            if (!$player instanceof PracticePlayer || !$player->isOnline()) {
                $sender->sendMessage('§r§cThat player is not online!');
                return;
            }

            $duel = $player->getDuel();

            if ($duel === null) {
                $sender->sendMessage('§r§cThat player is not in a duel!');
                return;
            }
            $vec = $player->getPosition()->add(0, 5, 0);
            $pos = new Position($vec->getX(), $vec->getY(), $vec->getZ(), $player->getWorld());

            $sender->spectator(true);
            $sender->preTeleport($pos);
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