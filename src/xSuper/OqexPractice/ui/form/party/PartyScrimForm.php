<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\ui\form\party;

use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use WeakMap;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\BaseForm;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\MenuOption;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\form\Forms;
use xSuper\OqexPractice\ui\form\MenuForm;

class PartyScrimForm extends MenuForm{
    /**
     * @var WeakMap<Player, Party[]>
     */
    private WeakMap $partiesPerPlayer;

	public function create(Player $player): BaseForm
	{
        $this->partiesPerPlayer = new WeakMap();
		return $this->createForm($player);
    }

    public function getTitle(Player $player): string{
        return 'Party Scrim';
    }

    public function getBody(Player $player): string
    {
        return '';
    }

    public function getOptions(Player $player): array
    {
        $menus = [];
        $parties = [];
        foreach (Party::getParties() as $party) {
            $menus[] = new MenuOption('§r§l§d' . $party->getOwner() . "'s Party - " . '§r§8 - §7Members: §d' . count($party->getActualMembers()));
            $parties[] = $party;
        }
        $this->partiesPerPlayer[$player] = $parties;
        return $menus;
    }

    public function handle(Player $player, int $selected): void
    {
        $parties = $this->partiesPerPlayer[$player] ?? null;
        if($parties === null){
            return;
        }
        unset($this->partiesPerPlayer[$player]);

        $selectedParty = $parties[$selected];
        $id = $selectedParty->getId();
        if(Party::getParty($id) === null){
            $player->sendMessage('§r§cThe party you selected has already ended!');
            return;
        }

		if(!$player instanceof PracticePlayer){
			throw new AssumptionFailedError('$player should be a PracticePlayer');
		}
        if (($partyId = $player->getParty()) === null) {
            $player->sendMessage('§r§cYou are not in a party!');
            return;
        }

        $party = Party::getParty($partyId);
        if ($party === null) return;

        if ($id === $partyId) {
            $player->sendMessage('§r§cYou can not send a scrim invite to your party!');
            return;
        }

        if ($party->getOwner() !== $player->getUniqueId()->toString()) {
            $player->sendMessage('§r§cYou are not the party owner!');
            return;
        }

        if ($party->hasScrimRequestById($id)) {
            $party->acceptScrimRequestById($id);
            return;
        }

        $player->sendForm(Forms::DUEL_REQUEST()->create($player, $id));
    }
}