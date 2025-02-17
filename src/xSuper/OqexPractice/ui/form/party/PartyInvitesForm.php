<?php

namespace xSuper\OqexPractice\ui\form\party;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use Ramsey\Uuid\Uuid;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\BaseForm;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\MenuOption;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\form\MenuForm;

class PartyInvitesForm extends MenuForm {
	/** @var array<string, list<string>> */
    private static array $saved = [];

	public function create(Player $player): BaseForm{
		return $this->createForm($player);
	}
    public function getTitle(Player $player): string
    {
        return 'Invites Menu';
    }

    public function getBody(Player $player): string
    {
		if(!$player instanceof PracticePlayer){
			throw new AssumptionFailedError('$player should be a PracticePlayer');
		}
        $invites = array_keys($player->getPartyInvites());
        if (count($invites) === 0) return '§r§7You have no party invites :(';
        else return '';
    }

    public function getOptions(Player $player): array
    {
		if(!$player instanceof PracticePlayer){
			throw new AssumptionFailedError('$player should be a PracticePlayer');
		}
        $invites = array_keys($player->getPartyInvites());

        $buttons = [];
        if (count($invites) > 0) {
            $ar = [];
            foreach ($invites as $i) {
                $ar[] = $i;
                $party = Party::getParty($i);
                if ($party !== null) {

                    $p = Server::getInstance()->getPlayerByUUID(Uuid::fromString($party->getOwner()));

                    if ($p === null || !$p->isOnline()) $name = 'Unknown';
                    else $name = $p->getName();

                    $buttons[] = new MenuOption($name);
                }
            }

            self::$saved[$player->getUniqueId()->toString()] = $ar;

            return $buttons;
        }

        return [];
    }

    public function handle(Player $player, int $selected): void
    {
        $saved = self::$saved[$player->getUniqueId()->toString()] ?? null;

        if ($saved === null) return;

        unset(self::$saved[$player->getUniqueId()->toString()]);

        $id = $saved[$selected] ?? null;

        if ($id === null) return;

		if(!$player instanceof PracticePlayer){
			throw new AssumptionFailedError('$player should be a PracticePlayer');
		}
        if (!$player->hasPartyInviteById($id)) {
            $player->sendMessage( "§r§cThat invite has either expired or it's party has been disbanded!");
            return;
        }

        $player->acceptPartyInviteById($id);
    }
}