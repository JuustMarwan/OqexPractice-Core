<?php

namespace xSuper\OqexPractice\ui\menu\settings;

use muqsit\customsizedinvmenu\libs\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\menu\CustomInventory;
use xSuper\OqexPractice\ui\menu\Menus;

class MainSettingsMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(27);
    }

    public function getTitle(Player $player): string
    {
        return 'Settings';
    }

    public function handle(DeterministicInvMenuTransaction $transaction): void
    {
        $slot = $transaction->getAction()->getSlot();
        $player = $transaction->getPlayer();
        if(!$player instanceof PracticePlayer){
            throw new AssumptionFailedError('$player should be a PracticePlayer');
        }
        $settings = $player->getData()->getSettings();
        (match ($slot) {
            12 => Menus::MESSAGES_SETTINGS(),
            13 => Menus::VISIBILITY_SETTINGS(),
            14 => Menus::MISC_SETTINGS(),
            default => null
        })?->create($player);
    }

    public function render(Player $player): void
    {
        $this->getMenu($player)->getInventory()->setContents([
            12 => VanillaItems::PAPER()->setCustomName('§r§l§bMessages')->setLore([
                '§r§7Change what messages you would like to receive',
                '§r§7or not receive!',
                '§r',
                '§r§8 - §7Chat Messages',
                '§r§8 - §7Kill Messages',
                '§r§8 - §7Annoucements',
                '§r§8 - §7Private Messages',
                '§r§8 - §7Profanity'
            ]),
            13 => VanillaItems::ENDER_PEARL()->setCustomName('§r§l§aVisibility/Interrupting')->setLore([
                '§r§7Change where you would like players to be visible',
                '§r§7at, and toggle interrupting in FFA!',
                '§r',
                '§r§8 - §7Players Visible In Spawn',
                '§r§8 - §7Players Visible In Events',
                '§r§8 - §7Players Visible In FFA',
                '§r§8 - §7Interrupting'
            ]),
            14 => VanillaItems::PUFFERFISH()->setCustomName('§r§l§cMiscellaneous')->setLore([
                '§r§7Change miscellaneous settings!',
                '§r',
                '§r§8 - §7UI Type',
                '§r§8 - §7Scoreboard Toggle',
                '§r§8 - §7Duel Requests',
                '§r§8 - §7Party Invites',
                '§r§8 - §7Animate Packs',
                '§r§8 - §7Shop Alerts',
                '§r§8 - §7Stat Reset Alerts',
            ])
        ]);
    }
}