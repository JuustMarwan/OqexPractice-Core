<?php

namespace xSuper\OqexPractice\commands\staff;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use xSuper\OqexPractice\items\custom\InteractiveItems;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\PracticePlayer;

class StaffCommand extends BaseCommand
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

            $sender->getFFA()?->leave(null, $sender);

            if ($sender->getStaffMode()) {
                $sender->unVanish(true);
                $sender->reset(OqexPractice::getInstance());

                $sender->setStaffMode(false);
            } else {
                $sender->vanish(true);
                $sender->setAllowFlight(true);
                $sender->setFlying(true);

                $sender->setCanBeDamaged(false);

                $sender->setStaffMode(true);

				$freezeItem = InteractiveItems::FREEZE();
				$vanishItem = InteractiveItems::VANISH();
                $sender->getInventory()->setContents([
                    5 => $freezeItem->getActualItem($sender),
                    7 => $vanishItem->getActualItem($sender)
                ]);
            }

            return;
        }

        $sender->sendMessage('§r§cThis command is only available in-game!');
    }

    protected function prepare(): void
    {
        $this->setPermission('oqex');
    }
}