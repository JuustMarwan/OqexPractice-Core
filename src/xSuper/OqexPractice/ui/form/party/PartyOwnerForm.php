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

class PartyOwnerForm extends MenuForm {

	public function create(Player $player): BaseForm
	{
		return $this->createForm($player);
	}
    public function getTitle(Player $player): string
    {
        return 'Your Party';
    }

    public function getBody(Player $player): string
    {
        return '';
    }

    public function getOptions(Player $player): array
    {
        return [
            new MenuOption('Party Duels', new FormIcon('texture/items/iron_sword', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('Party Members', new FormIcon('texture/items/paper', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('Invite Players', new FormIcon('texture/items/totem', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('Party Scrims', new FormIcon('texture/items/golden_sword', FormIcon::IMAGE_TYPE_PATH))
        ];
    }

    public function handle(Player $player, int $selected): void
    {
        switch($selected){
            case 0:
				if(!$player instanceof PracticePlayer){
					throw new AssumptionFailedError('$player should be a PracticePlayer');
				}
                if (($partyId = $player->getParty()) === null) {
                    $player->sendMessage('§r§cYou are not in a party!');
                    return;
                }

                $party = Party::getParty($partyId);
                if ($party === null) return;

                if ($party->getOwner() !== $player->getUniqueId()->toString()) {
                    $player->sendMessage('§r§cYou are not the party owner!');
                    return;
                }

                if ($party->getDuel() !== null) {
                    $player->sendMessage('§r§cYour party is already in a duel!');
                    return;
                }

                $player->sendForm(Forms::DUEL_REQUEST()->create($player, null));
                break;
            case 1:
                $player->sendForm(Forms::PARTY_MEMBERS()->create($player));
                break;
            case 2:
                $player->sendForm(Forms::PARTY_INVITE()->create($player));
                break;
            case 3:
                $player->sendForm(Forms::PARTY_SCRIM()->create($player));
        }
    }
}