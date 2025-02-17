<?php

namespace xSuper\OqexPractice\commands;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\ui\form\Forms;
use xSuper\OqexPractice\ui\menu\Menus;
use xSuper\OqexPractice\ui\menu\settings\MainSettingsMenu;

class SettingsCommand extends BaseCommand
{
	/** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof PracticePlayer) {
            if ($sender->getData()->getSettings()->getSetting(SettingIDS::UI_TYPE) === SettingIDS::UI_TYPE_CHEST) Menus::MAIN_SETTINGS()->create($sender);
            else $sender->sendForm(Forms::SETTINGS()->create($sender));

            return;
        }

        $sender->sendMessage('§r§cThis command can only be ran in-game!');
    }


    protected function prepare(): void
    {
		$this->setPermission('oqex');
    }
}