<?php

namespace xSuper\OqexPractice\ui\menu\duel;

use muqsit\customsizedinvmenu\libs\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\duel\generator\maps\Map;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\menu\CustomInventory;

class MapSelectionMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(54);
    }

    public function getTitle(Player $player): string
    {
        return 'Select a map';
    }

    public function handle(DeterministicInvMenuTransaction $transaction): void
    {
        $player = $transaction->getPlayer();
        $slot = $transaction->getAction()->getSlot();
        if (!$player instanceof PracticePlayer){
            throw new AssumptionFailedError('$player should be a PracticePlayer');
        }

        $data = $this->getData($player);

        $recipient = $data['recipient'];
        $dType = $data['dType'];
        if ($slot === 49) {
            if ($recipient instanceof PracticePlayer) {
                $player->removeCurrentWindow();
                $maps = Map::getMapsByType(Map::translateType($dType));
                $map = $maps[rand(0, count($maps) - 1)];

                $recipient->addRequest($player, $dType, $map);
                return;
            } else if ($recipient === null && ($partyId = $player->getParty()) !== null) {
                $player->removeCurrentWindow();
                $party = Party::getParty($partyId);
                if ($party === null) return;

                $maps = Map::getMapsByType(Map::translateType($dType));
                $map = $maps[rand(0, count($maps) - 1)];
                $party->createDuel($dType, $map);
            } else if (is_string($recipient) && ($partyId = $player->getParty()) !== null) {
                $player->removeCurrentWindow();
                $party = Party::getParty($partyId);
                if ($party === null) return;

                $oParty = Party::getParty($recipient);
                if ($oParty === null) return;

                $maps = Map::getMapsByType(Map::translateType($dType));
                $map = $maps[rand(0, count($maps) - 1)];
                $oParty->addScrimRequest($party, $dType, $map);
            }
            return;
        }

        $map = Map::getMap($transaction->getItemClicked()->getNamedTag()->getString('map', ''));

        $player->removeCurrentWindow();
        if ($recipient instanceof PracticePlayer){
            $recipient->addRequest($player, $dType, $map);

        }elseif($player->getParty() !== null){
            $party = Party::getParty($player->getParty());
            if ($party === null) return;

            if ($recipient === null){
                $party->createDuel($dType, $map);
            }else{
                $oParty = Party::getParty($recipient);
                if ($oParty === null) return;

                $oParty->addScrimRequest($party, $dType, $map);
            }
        }
    }

    public function render(Player $player): void
    {
        $data = $this->getData($player);
        $maps = Map::getMapsByType(Map::translateType($data['dType']));

        $iIndex = 0;
        $contents = [];

        for ($cIndex = 10; $cIndex <= 16; $cIndex++) {
            if ($iIndex <= count($maps) - 1) {
                $map = $maps[$iIndex];

                $item = VanillaItems::PAPER()->setCustomName('§r§l§6' . $map->getName())->setLore([
                    ' §r§8- §7Built By: §b' . $map->getAuthor(),
                    ' §r§8- §7Added in: §bSeason ' . $map->getSeason(),
                    '§r',
                    '§r§l§aClick §r§7to select §b' . $map->getName()
                ]);

                $item->getNamedTag()->setString('map', $map->getRealName());

                $contents[$cIndex] = $item;
                $iIndex++;
            }
        }

        for ($cIndex = 19; $cIndex <= 25; $cIndex++) {
            if ($iIndex <= count($maps) - 1) {
                $map = $maps[$iIndex];

                $item = VanillaItems::PAPER()->setCustomName('§r§l§6' . $map->getName())->setLore([
                    ' §r§8- §7Built By: §b' . $map->getAuthor(),
                    ' §r§8- §7Added in: §bSeason ' . $map->getSeason(),
                    '§r',
                    '§r§l§aClick §r§7to select §b' . $map->getName()
                ]);

                $item->getNamedTag()->setString('map', $map->getRealName());

                $contents[$cIndex] = $item;
                $iIndex++;
            }
        }

        for ($cIndex = 28; $cIndex <= 34; $cIndex++) {
            if ($iIndex <= count($maps) - 1) {
                $map = $maps[$iIndex];

                $item = VanillaItems::PAPER()->setCustomName('§r§l§6' . $map->getName())->setLore([
                    ' §r§8- §7Built By: §b' . $map->getAuthor(),
                    ' §r§8- §7Added in: §bSeason ' . $map->getSeason(),
                    '§r',
                    '§r§l§aClick §r§7to select §b' . $map->getName()
                ]);

                $item->getNamedTag()->setString('map', $map->getRealName());

                $contents[$cIndex] = $item;
                $iIndex++;
            }
        }

        $contents[49] = VanillaItems::COMPASS()->setCustomName('§r§l§6Random map')->setLore([
            '§r§7Choose a random map from this list!',
            '§r',
            '§r§l§aClick §r§7to select §ba random map'
        ]);
        $this->getMenu($player)->getInventory()->setContents($contents);
    }
}