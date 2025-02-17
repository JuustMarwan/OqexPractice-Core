<?php

namespace xSuper\OqexPractice\commands\misc;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\ui\form\Forms;
use xSuper\OqexPractice\ui\menu\event\EventMenu;

class EventCommand extends BaseCommand
{
    protected function prepare(): void
    {
        $this->setPermission('oqex');
    }

	/** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $sender->sendMessage('Coming soon...');
        //if (!$sender instanceof PracticePlayer || !$sender->isLoaded()) return;

        //if($sender->getData()->getSettings()->getSetting(SettingIDS::UI_TYPE) === SettingIDS::UI_TYPE_CHEST){
			//EventMenu::create($sender->getData()->getRankPermission() >= RankMap::permissionMap('owner'))->send($sender);
        //}else{
            //$sender->sendForm(Forms::EVENT()->create($sender));
        //}
    }
}