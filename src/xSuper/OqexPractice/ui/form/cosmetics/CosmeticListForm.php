<?php

namespace xSuper\OqexPractice\ui\form\cosmetics;

use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\BaseForm;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\MenuOption;
use xSuper\OqexPractice\player\cosmetic\CosmeticManager;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\form\Forms;
use xSuper\OqexPractice\ui\form\MenuForm;
use xSuper\OqexPractice\ui\menu\cosmetic\CosmeticMenu;

class CosmeticListForm extends MenuForm
{
	private int $context;

	public function create(Player $player, int $context): BaseForm{
		$this->context = $context;
		return $this->createForm($player);
	}

    public function getTitle(Player $player): string
    {
        if ($this->context === 0) return 'Cosmetics List';
        return 'Your Cosmetics';
    }

    public function getOptions(Player $player): array
    {
        return [
            new MenuOption('Capes'),
            new MenuOption('Costumes'),
            new MenuOption('Projectile Trails'),
            new MenuOption('Chat Colors')
        ];
    }

    public function getBody(Player $player): string
    {
        return '';
    }

    public function handle(Player $player, int $selected): void
    {
		if(!$player instanceof PracticePlayer){
			throw new AssumptionFailedError('$player should be a PracticePlayer');
		}
		$cosmetics = $player->getData()->getCosmetics();
		switch ($selected) {
            case 0:
                //TODO: Form
				CosmeticMenu::create($this->context, 'Capes', CosmeticManager::CAPE, $cosmetics)->send($player);
                break;
            case 1:
                //TODO: Form
                CosmeticMenu::create($this->context, 'Costumes', CosmeticManager::ARTIFACT, $cosmetics)->send($player);
                break;
            case 2:
                //TODO: Form
                CosmeticMenu::create($this->context, 'Projectile Trails', CosmeticManager::PROJECTILE, $cosmetics)->send($player);
                break;
            case 3:
                $player->sendForm(Forms::CHAT_COLOR()->create($player));
                break;
        }
    }
}