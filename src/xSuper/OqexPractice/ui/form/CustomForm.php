<?php

namespace xSuper\OqexPractice\ui\form;

use pocketmine\player\Player;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\BaseForm;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\CustomFormResponse;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\element\CustomFormElement;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\MenuOption;

abstract class CustomForm extends MenuForm
{
    public function create(Player $player): BaseForm
    {
        return new \xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\CustomForm(
            $this->getTitle($player),
            $this->getCustomElements($player),
            function(Player $player, CustomFormResponse $response): void{
                $this->handleCustom($player, $response);
            },
            function (Player $player): void {
                $this->close($player);
            }
        );
    }


    public function close(Player $player): void
    {

    }

    public function handle(Player $player, int $selected): void
    {
    }

    public function getBody(Player $player): string
    {
        return '';
    }

	/** @return list<MenuOption> */
	public function getOptions(Player $player): array{
		return [];
	}

	abstract public function getTitle(Player $player): string;
    /** @return CustomFormElement[] */
    abstract public function getCustomElements(Player $player): array;
    abstract public function handleCustom(Player $player, CustomFormResponse $response): void;
}