<?php

namespace xSuper\OqexPractice\ui\form\party;

use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use WeakMap;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\BaseForm;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\MenuOption;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\form\MenuForm;

class PartyMembersForm extends MenuForm {
	/** @var WeakMap<Player, list<string>> */
	private WeakMap $saved;

	public function create(Player $player): BaseForm
	{
		$this->saved = new WeakMap();
		return $this->createForm($player);
	}

    public function getTitle(Player $player): string
    {
        return 'Party Members';
    }

    public function getBody(Player $player): string
    {
        return '';
    }

    public function getOptions(Player $player): array
    {
		if(!$player instanceof PracticePlayer){
			throw new AssumptionFailedError('$player should be a PracticePlayer');
		}
        $party = $player->getParty();
        if ($party === null) return [];

        $party = Party::getParty($party);
        if ($party === null) return [];

        $members = $party->getActualMembers();

        $ar = [];
        $buttons = [];
        foreach ($members as $p) {
            if ($p->isOnline()) {
                $ar[] = $p->getUniqueId()->toString();
                $name = $player->getName();

                $buttons[] = new MenuOption($name);
            }
        }
		/** @var list<string> $ar */
        $this->saved[$player] = $ar;
        return $buttons;
    }

    public function handle(Player $player, int $selected): void
    {
        $saved = $this->saved[$player] ?? null;

        if ($saved === null) return;

        unset($this->saved[$player]);

        $id = $saved[$selected] ?? null;

        if ($id === null) return;

        // TODO: Stats Form/Kick
    }
}