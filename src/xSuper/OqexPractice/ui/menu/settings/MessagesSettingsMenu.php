<?php

namespace xSuper\OqexPractice\ui\menu\settings;

use muqsit\customsizedinvmenu\libs\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\player\settings\Settings;
use xSuper\OqexPractice\ui\menu\CustomInventory;

class MessagesSettingsMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(27);
    }

    public function getTitle(Player $player): string
    {
        return 'Messages';
    }

    public function handle(DeterministicInvMenuTransaction $transaction): void
    {
        $slot = $transaction->getAction()->getSlot();
        $player = $transaction->getPlayer();
        if(!$player instanceof PracticePlayer){
            throw new AssumptionFailedError('$player should be a PracticePlayer');
        }
        $id = match ($slot) {
            11 => SettingIDS::CHAT_MESSAGE,
            12 => SettingIDS::KILL_MESSAGE,
            13 => SettingIDS::ANNOUCEMENTS,
            14 => SettingIDS::PRIVATE_MESSAGE,
            15 => SettingIDS::PROFANITY,
            default => null
        };

        if ($id !== null) {
            ($settings = $player->getData()->getSettings())->setSetting($id, MessagesSettingsMenu::opposite($settings->getSetting($id)));
            $transaction->getAction()->getInventory()->setContents(self::generateContents($settings));
        }
    }

    public function render(Player $player): void
    {
        /** @var PracticePlayer $player */
        $settings = $player->getData()->getSettings();

        $this->getMenu($player)->getInventory()->setContents(self::generateContents($settings));
    }

	/**
	 * @param int<0, 1> $n
	 * @return int<0, 1>
	 * @phpstan-return ($n is 0 ? 1 : 0)
	 */
	public static function opposite(int $n): int
	{
		if ($n === 1) return 0;
		else return 1;
	}

	/** @phpstan-param key-of<Settings::DEFAULTS> $id */
    private static function createItemForSetting(Item $item, Settings $settings, int $id): Item
    {
        $t = $settings->getSetting($id);

        if ($t === 0) {
            $newLore = ['§r§g| §7Enabled', '§r§g| §cDisabled'];
        } else {
            $newLore = ['§r§g| §aEnabled', '§r§g| §7Disabled'];
        }

        $currentLore = $item->getLore();
        $updatedLore = array_merge($currentLore, [''], $newLore, [''], ['§r§bClick to toggle!']);

        $item->setLore($updatedLore);

        return $item;
    }

	/** @return array<int, Item> */
	private static function generateContents(Settings $settings): array{
		return [
			11 => MessagesSettingsMenu::createItemForSetting(VanillaItems::BOOK()->setCustomName('§r§fChat Messages')->setLore(['§r§7Changes if player messages should', '§r§7be visible in chat.']), $settings, SettingIDS::CHAT_MESSAGE),
			12 => MessagesSettingsMenu::createItemForSetting(VanillaItems::PAPER()->setCustomName('§r§fKill Messages')->setLore(['§r§7Changes if kill messages should', '§r§7be visible in chat.']), $settings, SettingIDS::KILL_MESSAGE),
			13 => MessagesSettingsMenu::createItemForSetting(VanillaBlocks::BELL()->asItem()->setCustomName('§r§fAnnoucements')->setLore(['§r§7Changes if server annoucements', '§r§7should be visible in chat.']), $settings, SettingIDS::ANNOUCEMENTS),
			14 => MessagesSettingsMenu::createItemForSetting(VanillaItems::TOTEM()->setCustomName('§r§fPrivate Messages')->setLore(['§r§7Changes if other players can', '§r§7send you messages.']), $settings, SettingIDS::PRIVATE_MESSAGE),
			15 => MessagesSettingsMenu::createItemForSetting(VanillaItems::MAGMA_CREAM()->setCustomName('§r§fProfanity')->setLore(['§r§7Changes if you want profanity', '§r§7to be blocked in chat.', '§r', '§r§l§cEXPERIMENTAL']), $settings, SettingIDS::PROFANITY)
		];
	}
}