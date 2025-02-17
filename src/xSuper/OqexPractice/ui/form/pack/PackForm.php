<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\ui\form\pack;

use pocketmine\player\Player;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\ui\form\MenuForm;

class PackForm extends MenuForm{

    public function getTitle(Player $player): string
    {
        return OqexPractice::NAME . ' Packs';
    }

    public function getBody(Player $player): string
    {
        return '';
    }

    public function getOptions(Player $player): array
    {
        return [];
    }

    public function handle(Player $player, int $selected): void
    {
        // TODO: Implement handle() method.
    }
}