<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\ui\form\cosmetics;

use pocketmine\player\Player;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\FormIcon;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\MenuOption;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\ui\form\MenuForm;

class ShopForm extends MenuForm{

    public function getTitle(Player $player): string
    {
        return OqexPractice::NAME . ' Shop';
    }

    public function getBody(Player $player): string
    {
        return '';
    }

    public function getOptions(Player $player): array
    {
        return [
            new MenuOption('§r§l§5Ranked Matches §r§7(10/ea)', new FormIcon('texture/items/diamond.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('§r§l§e' . OqexPractice::NAME . ' Pack §r§7(100/ea)', new FormIcon('texture/blocks/beacon.png', FormIcon::IMAGE_TYPE_PATH))
        ];
    }

    public function handle(Player $player, int $selected): void
    {
        // TODO: Implement handle() method.
    }
}