<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\ui\form\settings;

use pocketmine\player\Player;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\CustomFormResponse;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\element\Dropdown;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\element\Toggle;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\player\settings\Settings;
use xSuper\OqexPractice\ui\form\CustomForm;

class SettingsForm extends CustomForm {
    public function getTitle(Player $player): string
    {
        return 'Settings';
    }

    public function getCustomElements(Player $player): array
    {

        return [
            new Toggle('chat_message', 'Chat Messages', $this->getDef($player, SettingIDS::CHAT_MESSAGE)),
            new Toggle('kill_message', 'Kill Messages', $this->getDef($player, SettingIDS::KILL_MESSAGE)),
            new Toggle('annoucements', 'Annoucements', $this->getDef($player, SettingIDS::ANNOUCEMENTS)),
            new Toggle('private_message', 'Private Messages', $this->getDef($player, SettingIDS::PRIVATE_MESSAGE)),
            new Toggle('profanity', 'Profanity', $this->getDef($player, SettingIDS::PROFANITY)),
            new Toggle('spawn', 'Hide players at Spawn', $this->getDef($player, SettingIDS::HIDE_PLAYERS_AT_SPAWN)),
            new Toggle('event', 'Hide players in Events', $this->getDef($player, SettingIDS::HIDE_PLAYERS_AT_EVENT)),
            new Toggle('ffa', 'Hide players in FFA', $this->getDef($player, SettingIDS::HIDE_PLAYERS_AT_FFA)),
            new Toggle('ffa_respawn', 'FFA Respawn', $this->getDef($player, SettingIDS::FFA_RESPAWN)),
            new Toggle('interrupting', 'Interrupting', $this->getDef($player, SettingIDS::INTERRUPTING)),
            new Dropdown('ui_type', 'UI Type', [
                'Java',
                'Bedrock',
                'Recommend'
            ], $this->getUI($player)),
            new Toggle('scoreboard', 'Scoreboard', $this->getDef($player, SettingIDS::SCOREBOARD)),
            new Toggle('duel', 'Duel Requests', $this->getDef($player, SettingIDS::DUEL_REQUESTS)),
            new Toggle('party', 'Party Invites', $this->getDef($player, SettingIDS::PARTY_INVITES)),
            new Toggle('packs', 'Animate Packs', $this->getDef($player, SettingIDS::ANIMATE_PACKS)),
            new Toggle('shop', 'Shop Alerts', $this->getDef($player, SettingIDS::SHOP_ALERT)),
            new Toggle('stat', 'Stat Reset Alerts', $this->getDef($player, SettingIDS::STAT_RESET_ALERT)),
        ];
    }

	/** @phpstan-return int<0, 2> */
    public function getUI(Player $player): int
    {
        /** @var PracticePlayer $player */
        $s = $player->getData()->getSettings()->getRawSetting(SettingIDS::UI_TYPE);
        return match($s) {
            SettingIDS::UI_TYPE_RECOMMENDED => 0,
            SettingIDS::UI_TYPE_CHEST => 1,
            SettingIDS::UI_TYPE_FORM => 2
        };
    }

	/** @phpstan-param key-of<Settings::DEFAULTS> $id */
    public function getDef(Player $player, int $id): bool
    {
        /** @var PracticePlayer $player */
        $s = $player->getData()->getSettings()->getSetting($id);
        return (bool) $s;
    }

    public function handleCustom(Player $player, CustomFormResponse $response): void
    {
        /** @var PracticePlayer $player */
        $idMap = [
            'chat_message' => SettingIDS::CHAT_MESSAGE,
            'kill_message' => SettingIDS::KILL_MESSAGE,
            'annoucements' => SettingIDS::ANNOUCEMENTS,
            'private_message' => SettingIDS::PRIVATE_MESSAGE,
            'profanity' => SettingIDS::PROFANITY,
            'spawn' => SettingIDS::HIDE_PLAYERS_AT_SPAWN,
            'event' => SettingIDS::HIDE_PLAYERS_AT_EVENT,
            'ffa' => SettingIDS::HIDE_PLAYERS_AT_FFA,
            'ff_respawn' => SettingIDS::FFA_RESPAWN,
            'interrupting' => SettingIDS::INTERRUPTING,
            'ui_type' => SettingIDS::UI_TYPE,
            'scoreboard' => SettingIDS::SCOREBOARD,
            'duel' => SettingIDS::DUEL_REQUESTS,
            'party' => SettingIDS::PARTY_INVITES,
            'packs' => SettingIDS::ANIMATE_PACKS,
            'shop' => SettingIDS::SHOP_ALERT,
            'stat' => SettingIDS::STAT_RESET_ALERT
        ];

		/** @var array{
		 *     'chat_message': bool,
		 *     'kill_message': bool,
		 *     'annoucements': bool,
		 *     'private_message': bool,
		 *     'profanity': bool,
		 *     'spawn': bool,
		 *     'event': bool,
		 *     'ffa': bool,
         *     'ffa_respawn': bool,
		 *     'interrupting': bool,
		 *     'ui_type': int<0, 2>,
		 *     'scoreboard': bool,
		 *     'duel': bool,
		 *     'party': bool,
		 *     'packs': bool,
		 *     'shop': bool,
		 *     'stat': bool
		 * } $responseData */
		$responseData = $response->getAll();
		foreach ($responseData as $name => $v) {
            $id = $idMap[$name] ?? null;

            if ($id !== null) $player->getData()->getSettings()->setSetting($id, (int) $v);
        }
    }
}