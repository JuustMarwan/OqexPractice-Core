<?php

namespace xSuper\OqexPractice\commands\misc;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\BooleanArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use xSuper\OqexPractice\commands\arguments\PlayerArgument;
use xSuper\OqexPractice\items\custom\InteractiveItems;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\portal\Portal;
use xSuper\OqexPractice\utils\scoreboard\Scoreboard;

class PingCommand extends BaseCommand
{
    /** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof PracticePlayer) {
            if (!$sender->isLoaded()) return;

            $player = $args['player'] ?? $sender;

            if (!$player instanceof PracticePlayer || !$player->isOnline()) {
                $sender->sendMessage('§r§cThat player is not online!');
                return;
            }

            $ping = $player->getNetworkSession()->getPing();

            $name = $player->getName();

            $sender->sendMessage("§r§6§l $name's Ping Report\n§r§8- §fYour Ping: " . Scoreboard::colorizePing($ping) . "\n ");
        }
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new PlayerArgument('player', true));
        $this->setPermission('oqex');
    }
}