<?php

namespace xSuper\OqexPractice\commands\cosmetics\subcommands;


use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\ui\form\Forms;
use xSuper\OqexPractice\ui\menu\cosmetic\CosmeticListMenu;

class CosmeticsListSubCommand extends BaseSubCommand
{
	/** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        return;

        if ($sender instanceof PracticePlayer) {
            if (!$sender->isLoaded()) return;
            if($sender->getData()->getSettings()->getSetting(SettingIDS::UI_TYPE) === SettingIDS::UI_TYPE_CHEST){
				CosmeticListMenu::create(CosmeticListMenu::LIST)->send($sender);
            }else{
                $sender->sendForm(Forms::COSMETIC_LIST()->create($sender, CosmeticListMenu::LIST));
            }
        }
    }

    protected function prepare(): void
    {
        $this->setPermission('oqex');
    }
}