<?php

namespace xSuper\OqexPractice\ui\form\ffa;

use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\ffa\FFA;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\BaseForm;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\FormIcon;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\MenuOption;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\form\MenuForm;

class FFASelectionForm extends MenuForm
{
	public function create(Player $player): BaseForm
	{
		return $this->createForm($player);
	}
    public function getTitle(Player $player): string
    {
        return 'FFA Arenas';
    }

    public function getBody(Player $player): string
    {
        return '';
    }

    public function getOptions(Player $player): array
    {
        return [
            new MenuOption('NoDebuff', new FormIcon('textures/items/potion_bottle_splash_heal.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('Sumo', new FormIcon('texture/items/lead.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('OITC', new FormIcon('texture/items/bow_pulling_2.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('BuildUHC', new FormIcon('textures/items/bucket_lava', FormIcon::IMAGE_TYPE_PATH))
        ];
    }

    public function handle(Player $player, int $selected): void
    {
		if(!$player instanceof PracticePlayer){
			throw new AssumptionFailedError('$player should be a PracticePlayer');
		}
        $type = match ($selected) {
            0 => FFA::getArena('NoDebuff'),
            1 => FFA::getArena('Sumo'),
            2 => FFA::getArena('OITC'),
            3 => FFA::getArena('BuildUHC'),
            default => null
        };

        $type?->join($player);
    }
}