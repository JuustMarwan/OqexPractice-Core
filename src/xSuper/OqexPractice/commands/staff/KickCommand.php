<?php

namespace xSuper\OqexPractice\commands\staff;

use pocketmine\Server;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\RawStringArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\TextArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use xSuper\OqexPractice\commands\arguments\PlayerArgument;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\PracticePlayer;

class KickCommand extends BaseCommand
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
        }

        $p = $args['player'] ?? null;

        if ($p === null) {
            $sender->sendMessage('§r§cYou need to specify a player!');
            return;
        }
        if (!$p instanceof PracticePlayer || !$p->isOnline()) {
            $sender->sendMessage('§r§cThat player is not online!');
            return;
        }


        $r = $args['reason'] ?? 'Unknown Reason';
        if ($sender instanceof PracticePlayer) $s = $sender->getName();
        else $s = 'Console';

        $p->kick('§r§cYou were kicked by ' . $s . "\n  §fReason: " . $r);
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $player->sendMessage('§r§l§5Versai §r§8» §b' . $sender->getName() . '§f has kicked §b' . $p->getName() . '§f from the server for - ' . $r);
        }
    }

    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new PlayerArgument('player', true));
        $this->registerArgument(1, new TextArgument('reason', true));
    }
}