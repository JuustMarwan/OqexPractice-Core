<?php

namespace xSuper\OqexPractice\ui\menu;

use muqsit\customsizedinvmenu\CustomSizedInvMenu;
use muqsit\customsizedinvmenu\libs\muqsit\invmenu\InvMenu;
use muqsit\customsizedinvmenu\libs\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;

class CustomInventory
{
    protected array $menus = [];
    protected array $data = [];

    public function __construct(private int $size) {
    }

    public function getTitle(Player $player): string
    {
        return '';
    }

    public function create(Player $player, ?array $data = null): void
    {
        if ($data !== null) $this->data[$player->getUniqueId()->getBytes()] = $data;

        $menu = CustomSizedInvMenu::create($this->size)->setName($this->getTitle($player));

        $listener = $this->handle(...);
        $menu->setListener(InvMenu::readonly(
            static fn (DeterministicInvMenuTransaction $transaction) => $listener($transaction)
        ));

        $func = function (Player $player): void {
            unset($this->data[$player->getUniqueId()->getBytes()]);
            unset($this->menus[$player->getUniqueId()->getBytes()]);
        };

        $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory) use ($func): void {
            $func($player);
        });

        $this->menus[$player->getUniqueId()->getBytes()] = $menu;

        $this->render($player);
        $menu->send($player);
    }

    public function getMenu(Player $player): InvMenu
    {
        return $this->menus[$player->getUniqueId()->getBytes()];
    }

    public function handle(DeterministicInvMenuTransaction $transaction): void
    {

    }

    public function render(Player $player): void
    {
    }

    public function getData(Player $player): array
    {
        return $this->data[$player->getUniqueId()->getBytes()];
    }
}