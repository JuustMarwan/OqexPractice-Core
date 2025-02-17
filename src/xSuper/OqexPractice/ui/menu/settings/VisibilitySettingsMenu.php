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

class VisibilitySettingsMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(27);
    }

    public function getTitle(Player $player): string
    {
        return 'Visibility/Interrupting';
    }

    public function handle(DeterministicInvMenuTransaction $transaction): void
    {
        $slot = $transaction->getAction()->getSlot();
        $player = $transaction->getPlayer();
        if(!$player instanceof PracticePlayer){
            throw new AssumptionFailedError('$player should be a PracticePlayer');
        }
        $id = match ($slot) {
            11 => SettingIDS::HIDE_PLAYERS_AT_SPAWN,
            12 => SettingIDS::HIDE_PLAYERS_AT_EVENT,
            13 => SettingIDS::HIDE_PLAYERS_AT_FFA,
            14 => SettingIDS::INTERRUPTING,
            15 => SettingIDS::FFA_RESPAWN,
            default => null
        };

        if ($id !== null) {
            $settings = $player->getData()->getSettings();
            $settings->setSetting($id, VisibilitySettingsMenu::opposite($settings->getSetting($id)));
            $transaction->getAction()->getInventory()->setContents(self::generateContents($settings));
        }
    }

    public function render(Player $player): void
    {
        /** @var PracticePlayer $player */
        $settings = $player->getData()->getSettings();

        $this->getMenu($player)->getInventory()->setContents(self::generateContents($settings));
    }

	/** @return array<int, Item> */
	private static function generateContents(Settings $settings): array{
		return [
			11 => VisibilitySettingsMenu::createItemForSetting(VanillaItems::ENDER_PEARL()->setCustomName('§r§fHide at Spawn')->setLore(['§r§7Changes if players should be invisible', '§r§7at spawn (might boost FPS).']), $settings, SettingIDS::HIDE_PLAYERS_AT_SPAWN),
			12 => VisibilitySettingsMenu::createItemForSetting(VanillaBlocks::BELL()->asItem()->setCustomName('§r§fHide at Events')->setLore(['§r§7Changes if spectating players should', '§r§7be invisible in events.']), $settings, SettingIDS::HIDE_PLAYERS_AT_EVENT),
			13 => VisibilitySettingsMenu::createItemForSetting(VanillaItems::CLOWNFISH()->setCustomName('§r§fHide at FFA')->setLore(['§r§7Changes if players should be invisible', '§r§7while in combat at FFA.']), $settings, SettingIDS::HIDE_PLAYERS_AT_FFA),
			14 => VisibilitySettingsMenu::createItemForSetting(VanillaItems::GLISTERING_MELON()->setCustomName('§r§fInterrupting')->setLore(['§r§7Changes if you can fight more than', '§r§7one player at a time in FFA.', '§r', '§r§cRequires both players to have interrupting on']), $settings, SettingIDS::INTERRUPTING),
            15 => VisibilitySettingsMenu::createItemForSetting(VanillaItems::TOTEM()->setCustomName('§r§fFFA Respawn')->setLore(['§r§7Makes you automatically respawn again in', '§r§7FFA after dying.']), $settings, SettingIDS::FFA_RESPAWN),
		];
	}

	/**
	 * @phpstan-param int<0, 1> $n
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
}