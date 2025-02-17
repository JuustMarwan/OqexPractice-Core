<?php

namespace xSuper\OqexPractice\ui\menu\ffa;

use muqsit\customsizedinvmenu\libs\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\ffa\Arenas;
use xSuper\OqexPractice\ffa\FFA;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\menu\CustomInventory;

class FFASelectionMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(27);
    }

    public function getTitle(Player $player): string
    {
        return 'FFA Arenas';
    }

    public function handle(DeterministicInvMenuTransaction $transaction): void
    {
        $player = $transaction->getPlayer();
        if(!$player instanceof PracticePlayer){
            throw new AssumptionFailedError('$player should be a PracticePlayer');
        }
        (match ($transaction->getAction()->getSlot()) {
            11 => FFA::getArena('NoDebuff'),
            12 => FFA::getArena('Sumo'),
            13 => FFA::getArena('OITC'),
            14 => FFA::getArena('BuildUHC'),
            default => null
        })?->join($player);
    }

    public function render(Player $player): void
    {
        $this->getMenu($player)->getInventory()->setContents([
            11 => Arenas::NO_DEBUFF()->getMenuItem(),
            12 => Arenas::SUMO()->getMenuItem(),
            13 => Arenas::OITC()->getMenuItem(),
            14 => Arenas::BUHC()->getMenuItem(),
        ]);
    }
}