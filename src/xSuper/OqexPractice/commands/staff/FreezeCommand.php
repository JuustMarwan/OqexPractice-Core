<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\commands\staff;

use pocketmine\command\CommandSender;
use xSuper\OqexPractice\commands\arguments\PlayerArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\PracticePlayer;

class FreezeCommand extends BaseCommand {
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof PracticePlayer) {
            if (!$sender->isLoaded()) return;

            $player = $args['player'] ?? null;



            if (!$player instanceof PracticePlayer || !$player->isOnline()) {
                $sender->sendMessage('§r§cThat player is not online!');
                return;
            }
            if (RankMap::permissionMap($sender->getData()->getHighestRank()) < RankMap::permissionMap($player->getData()->getHighestRank())) {
                $sender->sendMessage("§r§cYou can not freeze this person. This action has been reported to adminstration.");
                return;
            }
            if ($sender->getData()->getRankPermission() < RankMap::permissionMap('helper')) {
                $sender->sendMessage('§r§cYou do not have permission to run this command!');
                return;
            }

            if ($player->isFrozen()) {
                $player->unFreeze();
                $staff = '§r§l§eiSPY §r§8» §e' . $sender->getName() . ' §7has unfrozen §e' . $player->getName();
            } else {
                $player->freeze();
                $staff = '§r§l§eiSPY §r§8» §e' . $sender->getName() . ' §7has frozen §e' . $player->getName();
            }
            foreach (OqexPractice::getOnlineStaff() as $s) $s->sendMessage($staff);
        }
    }


    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new PlayerArgument('player', true));
    }
}