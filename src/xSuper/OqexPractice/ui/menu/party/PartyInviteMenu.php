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

class PartyInviteMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(54);
    }

    public function getTitle(Player $player): string
    {
        return 'Invite Players';
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

        $players = Server::getInstance()->getOnlinePlayers();

        $slot = $transaction->getAction()->getSlot();
        if ($slot === 42 && count($players) > $page * 24) {
            $page++;
            $transaction->getAction()->getInventory()->setContents(self::generateContents($page));
        } else if ($slot === 41 && $page > 1) {
            $page--;
            $transaction->getAction()->getInventory()->setContents(self::generateContents($page));
        }

        $targetItem = $transaction->getItemClicked();
        if (($uuid = $targetItem->getNamedTag()->getString('playerUUID', '-1')) !== '-1') {
            $player->removeCurrentWindow();

            $i = Server::getInstance()->getPlayerByUUID(Uuid::fromString($uuid));

            if ($i === null) {
                $player->sendMessage('§r§cThat player is no longer online!');
                return;
            }
            if(!$i instanceof PracticePlayer){
                throw new AssumptionFailedError('$i should be a PracticePlayer');
            }

            $party->invite($i, $player);
        }
    }

    public function render(Player $player): void
    {
        $this->getMenu($player)->getInventory()->setContents(self::generateContents(1));
    }

    private static function createItemFromPlayer(PracticePlayer $player): Item
    {

        $i = VanillaItems::PAPER()->setCustomName('§r§l§d' . $player->getName())->setLore([
            '§r§7Click to invite this player to your party!',
            '§r',
            '§r§8 - §7Device: §d' . $player->getData()->getInfo()->getDeviceOS(),
            '§r§8 - §7Version: §d' . $player->getData()->getInfo()->getVersion(),
            '§r§8 - §7Ping: §d' . $player->getNetworkSession()->getPing()
        ]);
        $i->getNamedTag()->setString('playerUUID', $player->getUniqueId()->toString());
        return $i;
    }

	/** @return array<int, Item> */
	private static function generateContents(int $page): array{
		/** @var list<PracticePlayer> $players */
		$players = array_filter(array_values(Server::getInstance()->getOnlinePlayers()), fn(Player $player) => $player instanceof PracticePlayer && $player->isLoaded());

		if($page === 1) $iIndex = 0;
		else $iIndex = 21 * ($page - 1);

		$contents = [];
		for($cIndex = 10; $cIndex <= 16; $cIndex++){
			if($iIndex <= count($players) - 1){
				$player = $players[$iIndex] ?? null;
				if($player !== null){
					$item = self::createItemFromPlayer($player);
					$contents[$cIndex] = $item;
					$iIndex++;
				}
			}
		}

		for($cIndex = 10; $cIndex <= 16; $cIndex++){
			if($iIndex <= count($players) - 1){
				$player = $players[$iIndex] ?? null;
				if($player !== null){
					$item = self::createItemFromPlayer($player);
					$contents[$cIndex] = $item;
					$iIndex++;
				}
			}
		}

		for($cIndex = 10; $cIndex <= 16; $cIndex++){
			if($iIndex <= count($players) - 1){
				$player = $players[$iIndex] ?? null;
				if($player !== null){
					$item = self::createItemFromPlayer($player);
					$contents[$cIndex] = $item;
					$iIndex++;
				}
			}
		}

		if(count($players) > $page * 23) $contents[42] = VanillaItems::ARROW()->setCustomName('§r§aNext Page');
		if($page > 1) $contents[41] = VanillaItems::ARROW()->setCustomName('§r§aPrevious Page');
		return $contents;
	}
}