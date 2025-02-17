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

class StaffChatCommand extends BaseCommand
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
        if ($sender->getData()->getRankPermission() < RankMap::permissionMap('helper')) {
            $sender->sendMessage('§r§cYou do not have permission to run this command!');
            return;
        }
        if ($sender->getStaffChat()) {
            $sender->setStaffChat(false);
            $sender->sendMessage('§r§l§eiSPY §r§8» §e Staff chat has been disabled!');
        }
        else {
            $sender->setStaffChat(true);
            $sender->sendMessage('§r§l§eiSPY §r§8» §e Staff chat has been enabled!');
        }

    }

    protected function prepare(): void
    {
        $this->setPermission('oqex');
    }
}