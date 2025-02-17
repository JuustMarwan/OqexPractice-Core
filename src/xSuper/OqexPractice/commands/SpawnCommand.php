<?php

namespace xSuper\OqexPractice\commands;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use xSuper\OqexPractice\duel\special\BotDuel;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;

class SpawnCommand extends BaseCommand
{
	/** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof PracticePlayer) {
            if ($sender->getCombat() > 0) {
                $sender->sendMessage('§r§l§c(!) §r§fYou can not run that command while in combat!');
                return;
            }

            if (($duel = $sender->getDuel()) !== null) {
                if (!$duel->isEnded()) {
                    if ($duel instanceof BotDuel) {
                        $duel->end(OqexPractice::getInstance());
                        return;
                    }

                    $winner = null;
                    foreach ($duel->getPlayers() as $player) {
                        if ($player->getName() !== $sender->getName()) $winner = $player;
                    }

                    if ($winner !== null) $duel->setWinner($winner);
                    $duel->end(OqexPractice::getInstance());
                }
            }

            if (($ffa = $sender->getFFA()) !== null) {
                $ffa->subtractPlayer();
            }

            if (($event = $sender->getEvent()) !== null) {
                $event->disqualify($sender);
            }

            $sender->reset(OqexPractice::getInstance());
            return;
        }

        $sender->sendMessage('§r§cThis command can only be ran in-game!');
    }


    protected function prepare(): void
    {
        $this->setPermission('oqex');
    }
}