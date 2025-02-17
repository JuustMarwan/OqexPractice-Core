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

class PartyForm extends MenuForm
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
            new MenuOption('Create Party', new FormIcon('texture/items/nether_star', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('Party Invites', new FormIcon('texture/blocks/barrel_top', FormIcon::IMAGE_TYPE_PATH))
        ];
    }

    public function handle(Player $player, int $selected): void
    {
        switch ($selected) {
            case 0:
				if(!$player instanceof PracticePlayer){
					throw new AssumptionFailedError('$player should be a PracticePlayer');
				}
                if ($player->getParty() !== null) {
                    $player->sendMessage('§r§cYou are already in a party!');
                }

                $player->sendMessage('§r§l§dPARTY §r§8» §r§7You have created a party, invite players with §d/party invite <player>');
                Party::createParty($player);
                break;
            case 1:
                $player->sendForm(Forms::PARTY_INVITES()->create($player));
                break;
        }
    }
}