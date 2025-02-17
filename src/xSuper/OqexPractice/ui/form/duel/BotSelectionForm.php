<?php

namespace xSuper\OqexPractice\ui\form\duel;

use pocketmine\player\Player;
use xSuper\OqexPractice\bot\BotType;
use xSuper\OqexPractice\duel\Duel;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\FormIcon;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\MenuOption;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\form\MenuForm;

class BotSelectionForm extends MenuForm
{

    public function getTitle(Player $player): string
    {
        return 'PvP Bots';
    }

    public function getBody(Player $player): string
    {
        return '';
    }

    public function getOptions(Player $player): array
    {
        return [
            new MenuOption('Dummy', new FormIcon('textures\items\coal.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('Easy', new FormIcon('textures\items\iron.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('Normal', new FormIcon('textures\items\gold.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('Hard', new FormIcon('textures\items\diamond.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('Godly', new FormIcon('textures\items\emerald.png', FormIcon::IMAGE_TYPE_PATH))
        ];
    }

    public function handle(Player $player, int $selected): void
    {
        if (!$player instanceof PracticePlayer) return;

        $type = match ($selected) {
            0 => BotType::DummyNoDebuff,
            1 => BotType::EasyNoDebuff,
            2 => BotType::NormalNoDebuff,
            3 => BotType::HardNoDebuff,
            4 => BotType::GodlyNoDebuff,
            default => null
        };

        if ($type === null) return;

        Duel::createBotDuel(OqexPractice::getInstance(), $player, $type);
    }
}