<?php

namespace xSuper\OqexPractice\duel\history;

use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\Inventory;
use xSuper\OqexPractice\player\PracticePlayer;

class DrawDuelHistory extends DuelHistory
{
    public function __construct(PracticePlayer $opponent, int $hits, int $oHits, protected ?float $health, protected ?float $oHealth, ?Inventory $inventory, ?ArmorInventory $armorInventory, ?Inventory $oInventory, ?ArmorInventory $oArmorInventory)
    {
        parent::__construct(false, $opponent, $hits, $oHits, 0, $inventory, $armorInventory, $oInventory, $oArmorInventory);
    }

    public function getOpponentHealth(): ?float
    {
        return $this->oHealth;
    }

    public function getHealth(): ?float
    {
        return $this->health;
    }
}