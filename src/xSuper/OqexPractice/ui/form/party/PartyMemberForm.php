<?php

namespace xSuper\OqexPractice\ui\form\party;

use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\BaseForm;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\FormIcon;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\MenuOption;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\form\Forms;
use xSuper\OqexPractice\ui\form\MenuForm;

class PartyMemberForm extends MenuForm
{
	public function create(Player $player): BaseForm
	{
		return $this->createForm($player);
	}
    public function getTitle(Player $player): string
    {
        return 'Party Menu';
    }

    public function getBody(Player $player): string
    {
        return '';
    }

    public function getOptions(Player $player): array
    {
        return [
            new MenuOption('Party Members', new FormIcon('texture/items/paper', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('Leave Party', new FormIcon('texture/blocks/barrier', FormIcon::IMAGE_TYPE_PATH))
        ];
    }

    public function handle(Player $player, int $selected): void
    {
        switch ($selected) {
            case 0:
                $player->sendForm(Forms::PARTY_MEMBERS()->create($player));
                break;
            case 1:
				if(!$player instanceof PracticePlayer){
					throw new AssumptionFailedError('$player should be a PracticePlayer');
				}
                if (($partyId = $player->getParty()) === null) {
                    $player->sendMessage('§r§cYou are not in a party!');
                    return;
                }

                $party = Party::getParty($partyId);
                if ($party === null) return;

                $party->kick($player);
                break;
        }
    }
}