<?php

namespace xSuper\OqexPractice\ui\form\cosmetics;



use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Utils;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\CustomFormResponse;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\element\Dropdown;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\element\Label;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\form\CustomForm;

class ChatColorForm extends CustomForm
{
    const COLOR_MAP = [
        'Black' => '§0',
        'Dark Blue' => '§1',
        'Dark Green' => '§2',
        'Dark Aqua' => '§3',
        'Dark Red' => '§4',
        'Dark Purple' => '§5',
        'Gold' => '§6',
        'Gray' => '§7',
        'Dark Gray' => '§8',
        'Blue' => '§9',
        'Green' => '§a',
        'Aqua' => '§b',
        'Red' => '§c',
        'Pink' => '§d',
        'Yellow' => '§e',
        'White' => '§f'
    ];

    public function getTitle(Player $player): string
    {
        return 'Choose your chat color';
    }

    public function getCustomElements(Player $player): array
    {
        if (!$player instanceof PracticePlayer) return [];
        if ($player->getData()->getRankPermission() > RankMap::permissionMap('ultra')) {

            $currentColor = $player->getData()->getCosmetics()->getChatColor();

            $default = Utils::assumeNotFalse(array_search($currentColor, array_values(self::COLOR_MAP), true));

            return [
                new Dropdown('color', 'Chat Color', array_keys(self::COLOR_MAP), $default)
            ];
        }

        $link = OqexPractice::STORE_LINK;
        return [
            new Label('text', "§r§7Chat colors are only available for §9Donators§l§5\n
                §r§cPurchase at §6$link\n
                §r§cto unlock all chat colors!")
        ];
    }

    public function handleCustom(Player $player, CustomFormResponse $response): void
    {
		if(!$player instanceof PracticePlayer){
			throw new AssumptionFailedError('$player should be a PracticePlayer');
		}
        if ($player->getData()->getRankPermission() > RankMap::permissionMap('ultra')) {
            $name = $response->getString('color');

            $player->getData()->getCosmetics()->setChatColor(self::COLOR_MAP[$name] ?? '§f');
        }
    }
}