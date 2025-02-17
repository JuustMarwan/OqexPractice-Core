<?php

namespace xSuper\OqexPractice\ui\form\party;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\CustomFormResponse;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\element\Input;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\form\CustomForm;

class PartyInviteForm extends CustomForm
{

    public function getTitle(Player $player): string
    {
        return 'Invite Players';
    }

    public function getCustomElements(Player $player): array
    {
        return [
            new Input('player', 'Player', $player->getName())
        ];
    }

    public function handleCustom(Player $player, CustomFormResponse $response): void
    {
		if(!$player instanceof PracticePlayer){
			throw new AssumptionFailedError('$player should be a PracticePlayer');
		}
        $party = $player->getParty();
        if ($party === null) return;

        $party = Party::getParty($party);
        if ($party === null) return;

        $i = Server::getInstance()->getPlayerExact($player->getName());

        if (!$i instanceof PracticePlayer || $i->isLoaded()) {
            $player->sendMessage('§r§cThat player is not online!');
            return;
        }

        if ($player->getId() === $i->getId()) {
            $player->sendMessage('§r§cYou can not invite yourself to the party!');
            return;
        }

        $party->invite($i, $player);
    }
}