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
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\ui\form\Forms;
use xSuper\OqexPractice\ui\menu\CustomInventory;
use xSuper\OqexPractice\ui\menu\Menus;

class PartyScrimMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(54);
    }

    public function getTitle(Player $player): string
    {
        return 'Party Scrim';
    }

    public function handle(DeterministicInvMenuTransaction $transaction): void
    {
        static $page = 1;
        $slot = $transaction->getAction()->getSlot();
        $player = $transaction->getPlayer();
        if(!$player instanceof PracticePlayer){
            throw new AssumptionFailedError('$player should be a PracticePlayer');
        }
        $party = $player->getParty();
        if ($party === null) return;

        $party = Party::getParty($party);
        if ($party === null) return;

        $players = Server::getInstance()->getOnlinePlayers();

        if ($slot === 42 && count($players) > $page * 24) {
            $page++;
            $transaction->getAction()->getInventory()->setContents(self::generateContents($page, array_values(Party::getParties())));
        } else if ($slot === 41 && $page > 1) {
            $page--;
            $transaction->getAction()->getInventory()->setContents(self::generateContents($page, array_values(Party::getParties())));
        }

        $targetItem = $transaction->getItemClicked();
        if (($id = $targetItem->getNamedTag()->getString('partyId', '-1')) !== '-1') {
            if ($player->getParty() === null) {
                $player->removeCurrentWindow();
                $player->sendMessage('§r§cYou are not in a party!');
                return;
            }

            $party = Party::getParty($player->getParty()) ?? throw new AssumptionFailedError('Party should exist');

            if ($party->getOwner() !== $player->getUniqueId()->toString()) {
                $player->removeCurrentWindow();
                $player->sendMessage('§r§cYou are not the party owner!');
                return;
            }

            if ($id === $party->getId()) {
                $player->sendMessage('§r§cYou can not send a scrim invite to your party!');
                return;
            }

            if ($party->hasScrimRequestById($id)) {
                $party->acceptScrimRequestById($id);
                return;
            }

            if($player->getData()->getSettings()->getSetting(SettingIDS::UI_TYPE) === SettingIDS::UI_TYPE_CHEST) {
                Menus::DUEL_REQUEST()->create($player, ['recipient' => $id]);
            } else {
                $player->sendForm(Forms::DUEL_REQUEST()->create($player, $id));
            }
        }
    }

    public function render(Player $player): void
    {
        $parties = array_values(Party::getParties());

        $this->getMenu($player)->getInventory()->setContents(self::generateContents(1, $parties));
    }

	/**
	 * @param list<Party> $parties
	 * @return array<int, Item>
	 */
	private static function generateContents(int $page, array $parties): array{
		if($page === 1) $iIndex = 0;
		else $iIndex = 21 * ($page - 1);

		$contents = [];
		for($cIndex = 10; $cIndex <= 16; $cIndex++){
			if($iIndex <= count($parties) - 1){
				$party = $parties[$iIndex] ?? null;
				if($party !== null){
					$item = self::createItemFromParty($party);
					$contents[$cIndex] = $item;
					$iIndex++;
				}
			}
		}

		for($cIndex = 10; $cIndex <= 16; $cIndex++){
			if($iIndex <= count($parties) - 1){
				$party = $parties[$iIndex] ?? null;
				if($party !== null){
					$item = self::createItemFromParty($party);
					$contents[$cIndex] = $item;
					$iIndex++;
				}
			}
		}

		for($cIndex = 10; $cIndex <= 16; $cIndex++){
			if($iIndex <= count($parties) - 1){
				$party = $parties[$iIndex] ?? null;
				if($party !== null){
					$item = self::createItemFromParty($party);
					$contents[$cIndex] = $item;
					$iIndex++;
				}
			}
		}

		if(count($parties) > $page * 23) $contents[42] = VanillaItems::ARROW()->setCustomName('§r§aNext Page');
		if($page > 1) $contents[41] = VanillaItems::ARROW()->setCustomName('§r§aPrevious Page');
		return $contents;
	}

	private static function createItemFromParty(Party $party): Item
	{
		$owner = Server::getInstance()->getPlayerByUUID(Uuid::fromString($party->getOwner()));
		if ($owner === null || !$owner->isOnline()) $owner = 'Unknown';
		else $owner = $owner->getName();

		$members = count($party->getActualMembers());

		$i = VanillaItems::PAPER()->setCustomName('§r§l§d' . $owner . "'s Party")->setLore([
			'§r§7Click to invite this party to scrim your party!',
			'§r',
			'§r§8 - §7Members: §d' . $members,
		]);
		$i->getNamedTag()->setString('partyId', $party->getId());
		return $i;
	}
}