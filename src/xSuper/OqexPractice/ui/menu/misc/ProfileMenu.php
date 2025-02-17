<?php

namespace xSuper\OqexPractice\ui\menu\misc;

use muqsit\customsizedinvmenu\CustomSizedInvMenu;
use muqsit\customsizedinvmenu\libs\muqsit\invmenu\InvMenu;
use muqsit\customsizedinvmenu\libs\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\player\PlayerSqlHelper;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\ui\form\Forms;
use xSuper\OqexPractice\ui\menu\CustomInventory;
use xSuper\OqexPractice\ui\menu\Menus;

class ProfileMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(9);
    }

    public function getTitle(Player $player): string
    {
        return "Profile";
    }

    public function handle(DeterministicInvMenuTransaction $transaction): void
    {
        $player = $transaction->getPlayer();
        if (!$player instanceof PracticePlayer) {
            throw new AssumptionFailedError('$player should be a PracticePlayer');
        }

        $slot = $transaction->getAction()->getSlot();

        if ($slot === 0) {
            $player->removeCurrentWindow();

            $player->sendMessage('§r§aThat players stats are loading...');
            PlayerSqlHelper::getStats($player->getUniqueId(), function (string $name, array $stats) use ($player): void {
                if ($player->isOnline()) {
                    $player->sendMessage('§r§aDone loading ' . $name . "'s stats!");
                    Menus::PLAYER_STATS()->create($player, ['target' => $name, 'stats' => $stats]);
                }
            });

            return;
        }

        if ($slot === 4) {
            if ($player->getData()->getSettings()->getSetting(SettingIDS::UI_TYPE) === SettingIDS::UI_TYPE_CHEST) Menus::MAIN_SETTINGS()->create($player);
            else $player->sendForm(Forms::SETTINGS()->create($player));
        }
    }

    public function render(Player $player): void
    {
        $this->getMenu($player)->getInventory()->setContents([
            0 => VanillaItems::EXPERIENCE_BOTTLE()->setCustomName('§r§l§6Statistics')->setLore([
                '§r§7View your statistics.'
            ]),
            2 => VanillaItems::BLEACH()->setCustomName('§r§l§6Locker Room')->setLore([
                '§r§7Equip and Preview cosmetics. §c(Coming Soon)'
            ]),
            4 => VanillaBlocks::REDSTONE_COMPARATOR()->asItem()->setCustomName('§r§l§6Settings')->setLore([
                '§r§7Modify server settings.'
            ]),
        ]);

    }
}