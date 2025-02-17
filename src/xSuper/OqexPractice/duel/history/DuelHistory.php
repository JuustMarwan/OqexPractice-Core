<?php

namespace xSuper\OqexPractice\duel\history;

use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\Inventory;
use xSuper\OqexPractice\player\PracticePlayer;

class DuelHistory
{
    public function __construct(protected bool $winner, protected PracticePlayer $opponent, protected int $hits, protected int $oHits, protected ?float $wHealth, protected ?Inventory $inventory, protected ?ArmorInventory $armorInventory, protected ?Inventory $oInventory, protected ?ArmorInventory $oArmorInventory)
    {

    }

    public function getOpponent(): PracticePlayer
    {
        return $this->opponent;
    }

    public function getInventory(): ?Inventory
    {
        return $this->inventory;
    }

    public function getOpponentInventory(): ?Inventory
    {
        return $this->oInventory;
    }

    public function getArmorInventory(): ?ArmorInventory
    {
        return $this->armorInventory;
    }

    public function getOpponentArmorInventory(): ?ArmorInventory
    {
        return $this->oArmorInventory;
    }

    public function isWinner(): bool
    {
        return $this->winner;
    }

    public function getWinnerHealth(): ?float
    {
        return $this->wHealth;
    }

    public function getHits(): int
    {
        return $this->hits;
    }

    public function getOpponentHits(): int
    {
        return $this->oHits;
    }
}