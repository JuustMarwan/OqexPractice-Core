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

class MiscSettingsMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(27);
    }

    public function getTitle(Player $player): string
    {
        return 'Miscellaneous';
    }

    public function handle(DeterministicInvMenuTransaction $transaction): void
    {
        $slot = $transaction->getAction()->getSlot();
        $player = $transaction->getPlayer();
        if(!$player instanceof PracticePlayer){
            throw new AssumptionFailedError('$player should be a PracticePlayer');
        }
        $id = match ($slot) {
            10 => SettingIDS::UI_TYPE,
            11 => SettingIDS::SCOREBOARD,
            12 => SettingIDS::DUEL_REQUESTS,
            13 => SettingIDS::PARTY_INVITES,
            14 => SettingIDS::ANIMATE_PACKS,
            15 => SettingIDS::SHOP_ALERT,
            16 => SettingIDS::STAT_RESET_ALERT,
            default => null
        };

        if ($id !== null) {
            $settings = $player->getData()->getSettings();
            if ($id !== SettingIDS::UI_TYPE) $settings->setSetting($id, MiscSettingsMenu::opposite($settings->getSetting($id)));
            else $settings->setSetting($id, MiscSettingsMenu::next($settings->getRawSetting($id)));
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
	 * @phpstan-param int<0, 1>|SettingIDS::UI_TYPE_* $n
	 * @phpstan-return int<0, 1>|SettingIDS::UI_TYPE_*
	 */
	public static function next(int $n): int
	{
		$n++;
		if ($n > SettingIDS::UI_TYPE_RECOMMENDED) $n = 0;
		return $n;
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
        $t = $settings->getRawSetting($id);

        if ($id !== SettingIDS::UI_TYPE) {
            if ($t === 0) {
                $newLore = ['§r§g| §7Enabled', '§r§g| §cDisabled'];
            } else {
                $newLore = ['§r§g| §aEnabled', '§r§g| §7Disabled'];
            }
        } else {
            $newLore = match($t) {
                SettingIDS::UI_TYPE_RECOMMENDED => ['§r§g| §l§b(RECOMMENDED)', '§r§g| §l§7(JAVA)', '§r§g| §l§7(BEDROCK)'],
                SettingIDS::UI_TYPE_CHEST => ['§r§g| §l§7(RECOMMENDED)', '§r§g| §l§b(JAVA)', '§r§g| §l§7(BEDROCK)'],
                SettingIDS::UI_TYPE_FORM => ['§r§g| §l§7(RECOMMENDED)', '§r§g| §l§7(JAVA)', '§r§g| §l§b(BEDROCK)']
            };
        }

        $currentLore = $item->getLore();
        $updatedLore = array_merge($currentLore, [''], $newLore, [''], ['§r§bClick to toggle!']);

        $item->setLore($updatedLore);

        return $item;
    }

	/** @return array<int, Item> */
	private static function generateContents(Settings $settings): array{
		return [
			10 => MiscSettingsMenu::createItemForSetting(VanillaBlocks::CHEST()->asItem()->setCustomName('§r§fUI Type')->setLore(['§r§7Changes your UI type, defaults', '§r§7to the recommend one on join.']), $settings, SettingIDS::UI_TYPE),
			11 => MiscSettingsMenu::createItemForSetting(VanillaItems::PAPER()->setCustomName('§r§fScoreboard')->setLore(['§r§7Changes if the scoreboard', '§r§7should be visible.']), $settings, SettingIDS::SCOREBOARD),
			12 => MiscSettingsMenu::createItemForSetting(VanillaItems::BOOK()->setCustomName('§r§fDuel Requests')->setLore(['§r§7Changes if players can send', '§r§7you duel requests.']), $settings, SettingIDS::DUEL_REQUESTS),
			13 => MiscSettingsMenu::createItemForSetting(VanillaItems::TOTEM()->setCustomName('§r§fParty Invites')->setLore(['§r§7Changes if players can send', '§r§7you party invites.']), $settings, SettingIDS::PARTY_INVITES),
			14 => MiscSettingsMenu::createItemForSetting(VanillaBlocks::BEACON()->asItem()->setCustomName('§r§fAnimate Packs')->setLore(['§r§7Changes if you want packs', '§r§7to be animated. ']), $settings, SettingIDS::ANIMATE_PACKS),
			15 => MiscSettingsMenu::createItemForSetting(VanillaItems::GOLD_NUGGET()->setCustomName('§r§fShop Alert')->setLore(['§r§7Changes if you want to be alerted', '§r§7when the shop resets.']), $settings, SettingIDS::SHOP_ALERT),
			16 => MiscSettingsMenu::createItemForSetting(VanillaItems::CLOCK()->setCustomName('§r§fStat Reset Alert')->setLore(['§r§7Changes if you want to be alerted', '§r§7when stats reset.']), $settings, SettingIDS::STAT_RESET_ALERT),
		];
	}

}