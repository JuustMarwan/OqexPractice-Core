<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\ui\form;

use pocketmine\utils\CloningRegistryTrait;
use xSuper\OqexPractice\ui\form\cosmetics\ChatColorForm;
use xSuper\OqexPractice\ui\form\cosmetics\CosmeticListForm;
use xSuper\OqexPractice\ui\form\duel\BotSelectionForm;
use xSuper\OqexPractice\ui\form\duel\DuelRequestForm;
use xSuper\OqexPractice\ui\form\duel\MapSelectionForm;
use xSuper\OqexPractice\ui\form\duel\RankedDuelForm;
use xSuper\OqexPractice\ui\form\duel\UnrankedDuelForm;
use xSuper\OqexPractice\ui\form\event\EventForm;
use xSuper\OqexPractice\ui\form\ffa\FFASelectionForm;
use xSuper\OqexPractice\ui\form\party\PartyForm;
use xSuper\OqexPractice\ui\form\party\PartyInviteForm;
use xSuper\OqexPractice\ui\form\party\PartyInvitesForm;
use xSuper\OqexPractice\ui\form\party\PartyMemberForm;
use xSuper\OqexPractice\ui\form\party\PartyMembersForm;
use xSuper\OqexPractice\ui\form\party\PartyOwnerForm;
use xSuper\OqexPractice\ui\form\party\PartyScrimForm;
use xSuper\OqexPractice\ui\form\settings\SettingsForm;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static FFASelectionForm FFA_SELECTION()
 * @method static EventForm EVENT()
 * @method static UnrankedDuelForm UNRANKED_DUEL()
 * @method static RankedDuelForm RANKED_DUEL()
 * @method static MapSelectionForm MAP_SELECTION()
 * @method static DuelRequestForm DUEL_REQUEST()
 * @method static BotSelectionForm BOT_SELECTION()
 * @method static CosmeticListForm COSMETIC_LIST()
 * @method static ChatColorForm CHAT_COLOR()
 * @method static PartyForm PARTY()
 * @method static PartyInviteForm PARTY_INVITE()
 * @method static PartyInvitesForm PARTY_INVITES()
 * @method static PartyMemberForm PARTY_MEMBER()
 * @method static PartyMembersForm PARTY_MEMBERS()
 * @method static PartyOwnerForm PARTY_OWNER()
 * @method static PartyScrimForm PARTY_SCRIM()
 * @method static SettingsForm SETTINGS()
 *
 */
final class Forms {
    use CloningRegistryTrait;

    private function __construct(){
        //NOOP
    }

    protected static function register(string $name, MenuForm $form) : void{
        self::_registryRegister($name, $form);
    }

    /**
     * @return MenuForm[]
     * @phpstan-return array<string, MenuForm>
     */
    public static function getAll() : array{
        //phpstan doesn't support generic traits yet :(
        /** @var MenuForm[] $result */
        $result = self::_registryGetAll();
        return $result;
    }

    protected static function setup() : void{
        self::register('ffa_selection', new FFASelectionForm());
        self::register('event', new EventForm());
        self::register('unranked_duel', new UnrankedDuelForm());
        self::register('ranked_duel', new RankedDuelForm());
        self::register('map_selection', new MapSelectionForm());
        self::register('duel_request', new DuelRequestForm());
        self::register('bot_selection', new BotSelectionForm());
        self::register('chat_color', new ChatColorForm());
        self::register('cosmetic_list', new CosmeticListForm());
        self::register('party', new PartyForm());
        self::register('party_invite', new PartyInviteForm());
        self::register('party_invites', new PartyInvitesForm());
        self::register('party_member', new PartyMemberForm());
        self::register('party_members', new PartyMembersForm());
        self::register('party_owner', new PartyOwnerForm());
        self::register('party_scrim', new PartyScrimForm());
        self::register('settings', new SettingsForm());
    }
}