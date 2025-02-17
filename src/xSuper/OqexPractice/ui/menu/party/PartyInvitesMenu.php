<?php

namespace xSuper\OqexPractice\ui\menu\party;

use muqsit\customsizedinvmenu\libs\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use Ramsey\Uuid\Uuid;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\menu\CustomInventory;

class PartyInvitesMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(54);
    }

    public function getTitle(Player $player): string
    {
        return 'Party Invites';
    }

    public function handle(DeterministicInvMenuTransaction $transaction): void
    {
        static $page = 1;
        $player = $transaction->getPlayer();
        if(!$player instanceof PracticePlayer){
            throw new AssumptionFailedError('$player should be a PracticePlayer');
        }

        $invites = array_filter(array_map(Party::getParty(...), array_keys($player->getPartyInvites())), fn(?Party $party) => $party !== null);

        $slot = $transaction->getAction()->getSlot();
        if ($slot === 42 && count($invites) > $page * 24) {
            $page++;
            $transaction->getAction()->getInventory()->setContents(self::generateContents($page, $invites));
        } else if ($slot === 41 && $page > 1) {
            $page--;
            $transaction->getAction()->getInventory()->setContents(self::generateContents($page, $invites));
        }

        $targetItem = $transaction->getItemClicked();
        if (($id = $targetItem->getNamedTag()->getString('partyId', '-1')) !== '-1') {
            $player->removeCurrentWindow();

            if (!$player->hasPartyInviteById($id)) {
                $player->sendMessage( "§r§cThat invite has either expired or it's party has been disbanded!");
                return;
            }

            $player->acceptPartyInviteById($id);
        }
    }

    public function render(Player $player): void
    {
        /** @var PracticePlayer $player */
        $this->getMenu($player)->getInventory()->setContents(self::generateContents(1, array_filter(array_map(Party::getParty(...), array_keys($player->getPartyInvites())), fn(?Party $party) => $party !== null)));
    }

	/**
	 * @param list<Party> $invites
	 * @return array<int, Item>
	 */
	private static function generateContents(int $page, array $invites): array{
		if($page === 1) $iIndex = 0;
		else $iIndex = 21 * ($page - 1);

		$contents = [];
		for($cIndex = 10; $cIndex <= 16; $cIndex++){
			if($iIndex <= count($invites) - 1){
				$invite = $invites[$iIndex] ?? null;
				if($invite !== null){
					$item = self::createItemFromInvite($invite);
					$contents[$cIndex] = $item;
					$iIndex++;
				}
			}
		}

		for($cIndex = 10; $cIndex <= 16; $cIndex++){
			if($iIndex <= count($invites) - 1){
				$invite = $invites[$iIndex] ?? null;
				if($invite !== null){
					$item = self::createItemFromInvite($invite);
					$contents[$cIndex] = $item;
					$iIndex++;
				}
			}
		}

		for($cIndex = 10; $cIndex <= 16; $cIndex++){
			if($iIndex <= count($invites) - 1){
				$invite = $invites[$iIndex] ?? null;
				if($invite !== null){
					$item = self::createItemFromInvite($invite);
					$contents[$cIndex] = $item;
					$iIndex++;
				}
			}
		}

		if(count($invites) > $page * 23) $contents[42] = VanillaItems::ARROW()->setCustomName('§r§aNext Page');
		if($page > 1) $contents[41] = VanillaItems::ARROW()->setCustomName('§r§aPrevious Page');
		return $contents;
	}

    private static function createItemFromInvite(Party $party): Item
    {
        $player = Server::getInstance()->getPlayerByUUID(Uuid::fromString($party->getOwner()));

        if ($player === null) $name = 'Unknown';
        else $name = $player->getName();

        $i = VanillaItems::PAPER()->setCustomName("§r§l§fINVITE FROM §b{$name}§f")->setLore([
            '§r§7Click this invite to join the party!',
            '§r',
            '§r§8 - §7Current Members: §b' . count($party->getActualMembers())
        ]);
        $i->getNamedTag()->setString('partyId', $party->getId());
        return $i;
    }
}