<?php

namespace xSuper\OqexPractice\ui\form\duel;

use pocketmine\player\Player;
use xSuper\OqexPractice\duel\queue\QueueManager;
use xSuper\OqexPractice\duel\type\Type;
use xSuper\OqexPractice\items\custom\InteractiveItems;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\BaseForm;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\FormIcon;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\MenuOption;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\form\MenuForm;

class UnrankedDuelForm extends MenuForm
{
	public function create(Player $player): BaseForm
	{
		return $this->createForm($player);
	}
    public function getTitle(Player $player): string
    {
        return 'Unranked Queue';
    }

    public function getBody(Player $player): string
    {
        return '';
    }

    public function getOptions(Player $player): array
    {
        return [
            new MenuOption('NoDebuff', new FormIcon('textures/items/potion_bottle_splash_heal.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('Debuff', new FormIcon('textures/items/potion_bottle_splash_harm.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('Gapple', new FormIcon('textures/items/apple_golden.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('BuildUHC', new FormIcon('textures/items/bucket_lava', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('Combo', new FormIcon('textures/items/fish_raw.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('Sumo', new FormIcon('textures/items/lead.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('Vanilla', new FormIcon('textures/items/diamond_sword.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('Archer', new FormIcon('textures/items/bow_pulling_2.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('Survival Games', new FormIcon('textures/items/flint_and_steel.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('Soup', new FormIcon('textures/items/mushroom_stew.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('Bridge', new FormIcon('textures/items/stick.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('PvP Bots', new FormIcon('textures/items/redstone_dust.png', FormIcon::IMAGE_TYPE_PATH))
        ];
    }

    public function handle(Player $player, int $selected): void
    {
        if (!$player instanceof PracticePlayer) {
            return;
        }
        $type = match ($selected) {
            0 => Type::getType('NoDebuff'),
            1 => Type::getType('Debuff'),
            2 => Type::getType('Gapple'),
            3 => Type::getType('BuildUHC'),
            4 => Type::getType('Combo'),
            5 => Type::getType('Sumo'),
            6 => Type::getType('Vanilla'),
            7 => Type::getType('Archer'),
            8 => Type::getType('Survival Games'),
            9 => Type::getType('Soup'),
            10 => Type::getType('Bridge'),
            default => null
        };

        if ($type === null) {
            return;
        }
        if (QueueManager::getInstance()->isInQueue($player)) {
            $player->sendMessage('§r§cYou are already in a queue!');
            return;
        }
        if (QueueManager::getInstance()->addQueue($player, $type)) {
            return;
        }
        $player->getInventory()->setContents([
            8 => InteractiveItems::LEAVE_QUEUE()->getActualItem($player)
        ]);
    }
}