<?php

namespace xSuper\OqexPractice\ui\form;

use pocketmine\player\Player;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\BaseForm;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\MenuOption;

abstract class MenuForm
{
    protected function createForm(Player $player): BaseForm
    {
        return new \xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\MenuForm(
            $this->getTitle($player),
            $this->getBody($player),
            $this->getOptions($player),
            function (Player $player, int $selected): void {
                $this->handle($player, $selected);
            },
            function (Player $player): void {
                $this->close($player);
            }
        );
    }

    
    public function close(Player $player): void
    {
        
    }

    abstract public function getTitle(Player $player): string;
    abstract public function getBody(Player $player): string;
    /** @return list<MenuOption> */
    abstract public function getOptions(Player $player): array;
    abstract public function handle(Player $player, int $selected): void;
}