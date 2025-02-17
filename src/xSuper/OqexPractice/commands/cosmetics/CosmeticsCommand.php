<?php

namespace xSuper\OqexPractice\commands\cosmetics;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use xSuper\OqexPractice\commands\cosmetics\subcommands\CosmeticsAllSubCommand;
use xSuper\OqexPractice\commands\cosmetics\subcommands\CosmeticsListSubCommand;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\ui\form\Forms;
use xSuper\OqexPractice\ui\menu\cosmetic\CosmeticListMenu;

class CosmeticsCommand extends BaseCommand
{
	/** @param array<string, string> $values */
    public function __construct(private array $values, PluginBase $plugin, string $name, string $description = "", array $aliases = [])
    {
        parent::__construct($plugin, $name, $description, $aliases);
    }

	/** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof PracticePlayer) {
            if($sender->getData()->getSettings()->getSetting(SettingIDS::UI_TYPE) === SettingIDS::UI_TYPE_CHEST){
				CosmeticListMenu::create(CosmeticListMenu::PERSONAL)->send($sender);
            }else{
                $sender->sendForm(Forms::COSMETIC_LIST()->create($sender, CosmeticListMenu::PERSONAL));
            }
        }
    }


    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerSubCommand(new CosmeticsAllSubCommand($this->values, 'all', 'Give a player every cosmetic'));
        $this->registerSubCommand(new CosmeticsListSubCommand('list', 'View a list of all cosmetics'));
    }
}