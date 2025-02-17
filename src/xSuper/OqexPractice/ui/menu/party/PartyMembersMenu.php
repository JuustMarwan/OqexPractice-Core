<?php

namespace xSuper\OqexPractice\ui\menu\party;

use muqsit\customsizedinvmenu\libs\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use Ramsey\Uuid\Uuid;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\PlayerSqlHelper;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\menu\CustomInventory;
use xSuper\OqexPractice\ui\menu\Menus;

class PartyMembersMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(54);
    }

    public function getTitle(Player $player): string
    {
        return parent::getTitle($player); // TODO: Change the autogenerated stub
    }

    public function handle(DeterministicInvMenuTransaction $transaction): void
    {
        static $page = 1;
        $player = $transaction->getPlayer();
        if(!$player instanceof PracticePlayer){
            throw new AssumptionFailedError('$player should be a PracticePlayer');
        }
        $party = $player->getParty();
        if ($party === null) return;

        $party = Party::getParty($party);
        if ($party === null) return;

        $members = $party->getActualMembers();
        $owner = $party->getOwner() === $player->getUniqueId()->toString();

        $slot = $transaction->getAction()->getSlot();
        if ($slot === 42 && count($members) > $page * 24) {
            $page++;
            $transaction->getAction()->getInventory()->setContents(self::generateContents($page, $members, $owner));
        } else if ($slot === 41 && $page > 1) {
            $page--;
            $transaction->getAction()->getInventory()->setContents(self::generateContents($page, $members, $owner));
        }

        $targetItem = $transaction->getItemClicked();
        if (($uuid = $targetItem->getNamedTag()->getString('playerUUID', '-1')) !== '-1') {
            $player->removeCurrentWindow();

            $player->sendMessage('§r§aThat players stats are loading...');

            PlayerSqlHelper::getStats(Uuid::fromString($uuid), function (string $name, array $stats) use ($player): void {
                if ($player->isOnline()) {
                    $player->sendMessage('§r§aDone loading ' . $name . "'s stats!");
                    Menus::PLAYER_STATS()->create($player, ['target' => $name, 'stats' => $stats]);
                }
            });
        }
    }

    public function render(Player $player): void
    {
        /** @var PracticePlayer $player */
        $party = $player->getParty();
        if ($party === null) return;

        $party = Party::getParty($party);
        if ($party === null) return;

        $members = $party->getActualMembers();
        $owner = $party->getOwner() === $player->getUniqueId()->toString();

        $this->getMenu($player)->getInventory()->setContents(self::generateContents(1, $members, $owner));
    }

	/**
	 * @param list<PracticePlayer> $members
	 * @return array<int, Item>
	 */
	private static function generateContents(int $page, array $members, bool $owner): array{
		if($page === 1) $iIndex = 0;
		else $iIndex = 21 * ($page - 1);

		$contents = [];
		for($cIndex = 10; $cIndex <= 16; $cIndex++){
			if($iIndex <= count($members) - 1){
				$member = $members[$iIndex] ?? null;
				if($member !== null){
					$item = self::createItemFromMember($member, $owner);
					$contents[$cIndex] = $item;
					$iIndex++;
				}
			}
		}

		for($cIndex = 10; $cIndex <= 16; $cIndex++){
			if($iIndex <= count($members) - 1){
				$member = $members[$iIndex] ?? null;
				if($member !== null){
					$item = self::createItemFromMember($member, $owner);
					$contents[$cIndex] = $item;
					$iIndex++;
				}
			}
		}

		for($cIndex = 10; $cIndex <= 16; $cIndex++){
			if($iIndex <= count($members) - 1){
				$member = $members[$iIndex] ?? null;
				if($member !== null){
					$item = self::createItemFromMember($member, $owner);
					$contents[$cIndex] = $item;
					$iIndex++;
				}
			}
		}

		if(count($members) > $page * 23) $contents[42] = VanillaItems::ARROW()->setCustomName('§r§aNext Page');
		if($page > 1) $contents[41] = VanillaItems::ARROW()->setCustomName('§r§aPrevious Page');
		return $contents;
	}

	private static function createItemFromMember(PracticePlayer $player, bool $owner): Item
	{
		if ($owner) {
			$i = VanillaItems::PAPER()->setCustomName('§r§l§d' . $player->getName())->setLore([
				'§r§7Click this item to view the stats of this player',
				'§r§7or to kick them from the party!',
				'§r',
				'§r§8 - §7Device: §d' . $player->getData()->getInfo()->getDeviceOS(),
				'§r§8 - §7Version: §d' . $player->getData()->getInfo()->getVersion(),
				'§r§8 - §7Ping: §d' . $player->getNetworkSession()->getPing()
			]);
		} else {
			$i = VanillaItems::PAPER()->setCustomName('§r§l§d' . $player->getName())->setLore([
				'§r§7Click this item to view the stats of this player!',
				'§r',
				'§r§8 - §7Device: §d' . $player->getData()->getInfo()->getDeviceOS(),
				'§r§8 - §7Version: §d' . $player->getData()->getInfo()->getVersion(),
				'§r§8 - §7Ping: §d' . $player->getNetworkSession()->getPing()
			]);
		}
		$i->getNamedTag()->setString('playerUUID', $player->getUniqueId()->toString());
		return $i;
	}
}