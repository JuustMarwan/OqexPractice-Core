<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\ui\menu;

use pocketmine\utils\CloningRegistryTrait;
use xSuper\OqexPractice\ui\menu\duel\BotSelectionMenu;
use xSuper\OqexPractice\ui\menu\duel\DuelRequestMenu;
use xSuper\OqexPractice\ui\menu\duel\EloLeaderboardMenu;
use xSuper\OqexPractice\ui\menu\duel\EloRanksMenu;
use xSuper\OqexPractice\ui\menu\duel\MapSelectionMenu;
use xSuper\OqexPractice\ui\menu\duel\RankedDuelMenu;
use xSuper\OqexPractice\ui\menu\duel\UnrankedDuelMenu;
use xSuper\OqexPractice\ui\menu\ffa\FFASelectionMenu;
use xSuper\OqexPractice\ui\menu\misc\PlayerStatsMenu;
use xSuper\OqexPractice\ui\menu\misc\ProfileMenu;
use xSuper\OqexPractice\ui\menu\party\PartyInviteMenu;
use xSuper\OqexPractice\ui\menu\party\PartyInvitesMenu;
use xSuper\OqexPractice\ui\menu\party\PartyMemberMenu;
use xSuper\OqexPractice\ui\menu\party\PartyMembersMenu;
use xSuper\OqexPractice\ui\menu\party\PartyMenu;
use xSuper\OqexPractice\ui\menu\party\PartyOwnerMenu;
use xSuper\OqexPractice\ui\menu\party\PartyScrimMenu;
use xSuper\OqexPractice\ui\menu\settings\MainSettingsMenu;
use xSuper\OqexPractice\ui\menu\settings\MessagesSettingsMenu;
use xSuper\OqexPractice\ui\menu\settings\MiscSettingsMenu;
use xSuper\OqexPractice\ui\menu\settings\VisibilitySettingsMenu;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static BotSelectionMenu BOT_SELECTION()
 * @method static DuelRequestMenu DUEL_REQUEST()
 * @method static EloLeaderboardMenu ELO_LEADERBOARD()
 * @method static EloRanksMenu ELO_RANKS()
 * @method static MapSelectionMenu MAP_SELECTION()
 * @method static RankedDuelMenu RANKED_DUEL()
 * @method static UnrankedDuelMenu UNRANKED_DUEL()
 * @method static FFASelectionMenu FFA_SELECTION()
 * @method static PartyInviteMenu PARTY_INVITE()
 * @method static PartyInvitesMenu PARTY_INVITES()
 * @method static PartyMemberMenu PARTY_MEMBER()
 * @method static PartyMembersMenu PARTY_MEMBERS()
 * @method static PartyMenu PARTY()
 * @method static PartyOwnerMenu PARTY_OWNER()
 * @method static PartyScrimMenu PARTY_SCRIM()
 * @method static MainSettingsMenu MAIN_SETTINGS()
 * @method static MessagesSettingsMenu MESSAGES_SETTINGS()
 * @method static MiscSettingsMenu MISC_SETTINGS()
 * @method static VisibilitySettingsMenu VISIBILITY_SETTINGS()
 * @method static PlayerStatsMenu PLAYER_STATS()
 * @method static ProfileMenu PROFILE()
 *
 */
final class Menus {
    use CloningRegistryTrait;

    private function __construct(){
        //NOOP
    }

    protected static function register(string $name, CustomInventory $menu) : void{
        self::_registryRegister($name, $menu);
    }

    /**
     * @return CustomInventory[]
     * @phpstan-return array<string, CustomInventory>
     */
    public static function getAll() : array{
        //phpstan doesn't support generic traits yet :(
        /** @var CustomInventory[] $result */
        $result = self::_registryGetAll();
        return $result;
    }

    protected static function setup() : void{
        self::register('bot_selection', new BotSelectionMenu());
        self::register('duel_request', new DuelRequestMenu());
        self::register('elo_leaderboard', new EloLeaderboardMenu());
        self::register('elo_ranks', new EloRanksMenu());
        self::register('map_selection', new MapSelectionMenu());
        self::register('ranked_duel', new RankedDuelMenu());
        self::register('unranked_duel', new UnrankedDuelMenu());
        self::register('ffa_selection', new FFASelectionMenu());
        self::register('party_invite', new PartyInviteMenu());
        self::register('party_invites', new PartyInvitesMenu());
        self::register('party_member', new PartyMemberMenu());
        self::register('party_members', new PartyMembersMenu());
        self::register('party', new PartyMenu());
        self::register('party_owner', new PartyOwnerMenu());
        self::register('party_scrim', new PartyScrimMenu());
        self::register('main_settings', new MainSettingsMenu());
        self::register('messages_settings', new MessagesSettingsMenu());
        self::register('misc_settings', new MiscSettingsMenu());
        self::register('visibility_settings', new VisibilitySettingsMenu());
        self::register('player_stats', new PlayerStatsMenu());
        self::register('profile', new ProfileMenu());
    }
}