<?php

namespace xSuper\OqexPractice\commands\misc;

use pocketmine\Server;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\BooleanArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\TextArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use xSuper\OqexPractice\commands\arguments\PlayerArgument;
use xSuper\OqexPractice\items\custom\InteractiveItems;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\portal\Portal;
use xSuper\OqexPractice\utils\scoreboard\Scoreboard;

class MessageCommand extends BaseCommand
{
    /** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof PracticePlayer) {
            if (!$sender->isLoaded()) return;

            $player = $args['player'] ?? null;

            if (!$player instanceof PracticePlayer || !$player->isOnline()) {
                $sender->sendMessage('§r§cThat player is not online!');
                return;
            }

            if (!$player->getData()->getSettings()->getSetting(SettingIDS::PRIVATE_MESSAGE)) {
                $sender->sendMessage('§r§cThat player is not accepting private messages!');
                return;
            }

            $message = $args['message'] ?? 'Hello!';

            $player->sendMessage('§r§b' . $sender->getName() . ' §8» §7' . $message);
            $staff = '§r§l§eiSPY §r§8» §e' . $sender->getName() . ' §7messaged §e' . $player->getName() . " §7'" . $message . "'";
            foreach (Server::getInstance()->getOnlinePlayers() as $p) {
                if ($p instanceof PracticePlayer && $p->isLoaded()) {
                    if ($p->getData()->getRankPermission() > RankMap::permissionMap('helper')) {
                        $p->sendMessage($staff);
                    }
                }
            }
        }
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new PlayerArgument('player', true));
        $this->registerArgument(1, new TextArgument('message', true));
        $this->setPermission('oqex');
    }
}