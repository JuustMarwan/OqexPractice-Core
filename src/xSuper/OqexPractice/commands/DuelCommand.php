<?php

namespace xSuper\OqexPractice\commands;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use xSuper\OqexPractice\commands\arguments\PlayerArgument;
use xSuper\OqexPractice\duel\queue\QueueManager;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\ui\form\Forms;
use xSuper\OqexPractice\ui\menu\duel\DuelRequestMenu;
use xSuper\OqexPractice\ui\menu\duel\UnrankedDuelMenu;
use xSuper\OqexPractice\ui\menu\Menus;

class DuelCommand extends BaseCommand
{
	/** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof PracticePlayer) {
            if (QueueManager::getInstance()->isInQueue($sender)) {
                $sender->sendMessage('§r§cYou can not run this command while in a queue!');
                return;
            }

            $player = $args['player'] ?? null;
            if ($player === null) {
                if($sender->getData()->getSettings()->getSetting(SettingIDS::UI_TYPE) === SettingIDS::UI_TYPE_CHEST){
                    Menus::UNRANKED_DUEL()->create($sender, ['rankedGames' => $sender->getData()->getTotalRankedGames()]);
                }else{
                    $sender->sendForm(Forms::UNRANKED_DUEL()->create($sender));
                }
                return;
            }

            if (!$player instanceof PracticePlayer || !$player->isOnline()) {
                $sender->sendMessage('§r§cThat player is not online!');
                return;
            }

            if ($player->getName() === $sender->getName()) {
                $sender->sendMessage('§r§cYou can not send duel requests to yourself!');
                return;
            }

            if ($sender->getDuel() !== null) {
                $sender->sendMessage('§r§cYou are already in a duel!');
                return;
            }

            if ($sender->hasRequest($player)) {
                $sender->acceptRequest($player);
                return;
            }

            if($sender->getData()->getSettings()->getSetting(SettingIDS::UI_TYPE) === SettingIDS::UI_TYPE_CHEST){
                Menus::DUEL_REQUEST()->create($sender, ['recipient' => $player]);
            }else{
                $sender->sendForm(Forms::DUEL_REQUEST()->create($sender, $player));
            }
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