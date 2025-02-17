<?php

namespace xSuper\OqexPractice\ui\menu\duel;

use muqsit\customsizedinvmenu\libs\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\player\Player;
use xSuper\OqexPractice\duel\type\Types;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\menu\CustomInventory;
use xSuper\OqexPractice\ui\menu\Menus;

class DuelRequestMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(45);
    }

    public function getTitle(Player $player): string
    {
        $recipient = $this->getData($player)['recipient'] ?? null;
        return $recipient instanceof PracticePlayer ? 'Send a duel request' : 'Select a duel type';
    }

    public function handle(DeterministicInvMenuTransaction $transaction): void
    {
        $slot = $transaction->getAction()->getSlot();
        $recipient = $this->getData($transaction->getPlayer())['recipient'] ?? null;
        $type = $recipient instanceof PracticePlayer ? match ($slot) {
            19 => Types::NO_DEBUFF(),
            20 => Types::DEBUFF(),
            21 => Types::GAPPLE(),
            22 => Types::BUILD_UHC(),
            23 => Types::COMBO(),
            24 => Types::SUMO(),
            25 => Types::VANILLA(),
            28 => Types::ARCHER(),
            29 => Types::SURVIVAL_GAMES(),
            30 => Types::SOUP(),
            31 => Types::BRIDGE(),
            default => null
        } : match ($slot) {
            19 => Types::NO_DEBUFF(),
            20 => Types::DEBUFF(),
            21 => Types::GAPPLE(),
            22 => Types::BUILD_UHC(),
            23 => Types::COMBO(),
            24 => Types::VANILLA(),
            25 => Types::ARCHER(),
            28 => Types::SOUP(),
            default => null
        };

        if ($type !== null) {
            Menus::MAP_SELECTION()->create($transaction->getPlayer(), ['dType' => $type, 'recipient' => $recipient]);
        }
    }

    public function render(Player $player): void
    {
        $recipient = $this->getData($player)['recipient'] ?? null;
        $this->getMenu($player)->getInventory()->setContents($recipient !== null ?
            [
                19 => Types::NO_DEBUFF()->getMenuItem(),
                20 => Types::DEBUFF()->getMenuItem(),
                21 => Types::GAPPLE()->getMenuItem(),
                22 => Types::BUILD_UHC()->getMenuItem(),
                23 => Types::COMBO()->getMenuItem(),
                24 => Types::SUMO()->getMenuItem(),
                25 => Types::VANILLA()->getMenuItem(),
                28 => Types::ARCHER()->getMenuItem(),
                29 => Types::SURVIVAL_GAMES()->getMenuItem(),
                30 => Types::SOUP()->getMenuItem(),
                31 => Types::BRIDGE()->getMenuItem()
            ] :
            [
                19 => Types::NO_DEBUFF()->getMenuItem(),
                20 => Types::DEBUFF()->getMenuItem(),
                21 => Types::GAPPLE()->getMenuItem(),
                22 => Types::BUILD_UHC()->getMenuItem(),
                23 => Types::COMBO()->getMenuItem(),
                24 => Types::VANILLA()->getMenuItem(),
                25 => Types::ARCHER()->getMenuItem(),
                28 => Types::SOUP()->getMenuItem()
            ]
        );
    }
}