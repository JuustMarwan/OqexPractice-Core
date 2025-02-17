<?php

namespace xSuper\OqexPractice\ui\form\duel;

use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\duel\generator\maps\Map;
use xSuper\OqexPractice\duel\type\Type;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\BaseForm;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\FormIcon;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\MenuOption;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\form\MenuForm;

class MapSelectionForm extends MenuForm
{
	private null|PracticePlayer|string $requester;
	private Type $type;

	public function create(Player $player, null|PracticePlayer|string $requester, Type $type): BaseForm{
		$this->requester = $requester;
		$this->type = $type;
		return parent::createForm($player);
	}

	public function getTitle(Player $player): string
    {
        return 'Select a map';
    }

    public function getBody(Player $player): string
    {
        return '';
    }

    public function getOptions(Player $player): array
    {
        $maps = Map::getMapsByType(Map::translateType($this->type));

        $buttons = [
            new MenuOption('Random Map', new FormIcon('texture/items/compass_item.png', FormIcon::IMAGE_TYPE_PATH))
        ];

        foreach ($maps as $map) {
            $buttons[] = new MenuOption($map->getName(), new FormIcon('texture/items/map_empty.png', FormIcon::IMAGE_TYPE_PATH));
        }

        return $buttons;
    }

    public function handle(Player $player, int $selected): void
    {
        $maps = Map::getMapsByType(Map::translateType($this->type));

		if($selected === 0){
			$map = $maps[array_rand($maps)];
		}else{
			$map = $maps[$selected - 1];
		}
        if ($this->requester instanceof PracticePlayer){
			$this->requester->addRequest($player, $this->type, $map);
			return;
		}
		if(!$player instanceof PracticePlayer){
			throw new AssumptionFailedError('$player should be a PracticePlayer');
		}
		if(($partyId = $player->getParty()) === null){
			return;
		}
		$party = Party::getParty($partyId);
		if ($party === null){
			return;
		}

		if($this->requester === null){
			$party->createDuel($this->type, $map);
			return;
		}
		$oParty = Party::getParty($this->requester);
		if ($oParty === null){
			return;
		}
		$oParty->addScrimRequest($party, $this->type, $map);
	}
}