<?php

namespace xSuper\OqexPractice\ui\menu\cosmetic;

use muqsit\customsizedinvmenu\libs\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use xSuper\OqexPractice\duel\type\Types;
use xSuper\OqexPractice\items\MoreItems;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\menu\CustomInventory;
use xSuper\OqexPractice\ui\menu\Menus;

class MainCosmeticMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(45);
    }

    public function getTitle(Player $player): string
    {
        $list = $this->getData($player)['list'] ?? false;
        return $list ? 'View released cosmetics' : 'Change your cosmetics';
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
        $list = $this->getData($player)['list'] ?? false;
        $this->getMenu($player)->getInventory()->setContents([
            14 => MoreItems::ELYTRA()->setCustomName('§r§l§6Capes'),
            21 => MoreItems::CREEPER_BANNER_PATTERN()->setCustomName('§r§l§bChat Colors'),
            22 => VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_HEALING)->setCustomName('§r§l§dPotion Colors'),
            23 => VanillaItems::BOW()->setCustomName('§r§l§cKill Phrases'),
            24 => VanillaItems::ARROW()->setCustomName('§r§l§5Projectile Trails'),
            25 => MoreItems::NAMETAG()->setCustomName('§r§l§eChat Tags'),
            31 => VanillaItems::LEATHER_CAP()->setCustomName('§r§l§bHats'),
            32 => VanillaItems::LEATHER_TUNIC()->setCustomName('§r§l§bBackpacks'),
            33 => VanillaItems::LEATHER_PANTS()->setCount('§r§l§bBelts')
        ]);
    }
}