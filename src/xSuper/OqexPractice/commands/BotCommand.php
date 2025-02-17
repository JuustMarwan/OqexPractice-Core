<?php

namespace xSuper\OqexPractice\commands;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use xSuper\OqexPractice\commands\arguments\BotTypeArgument;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\menu\duel\BotSelectionMenu;
use xSuper\OqexPractice\ui\menu\Menus;

class BotCommand extends BaseCommand
{
	/** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {

        if ($sender instanceof PracticePlayer) {
            if (!$sender->isLoaded()) return;

            Menus::BOT_SELECTION()->create($sender, ['stage' => 1]);
            return;
        }

        $sender->sendMessage('§r§cThis command can only be ran in-game!');

    }


    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new BotTypeArgument('type', true));
    }
}