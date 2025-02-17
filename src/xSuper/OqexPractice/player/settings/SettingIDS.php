<?php

namespace xSuper\OqexPractice\player\settings;

interface SettingIDS
{
    const CHAT_MESSAGE = 0;
    const KILL_MESSAGE = 1;
    const ANNOUCEMENTS = 2;
    const PRIVATE_MESSAGE = 4;
    const PROFANITY = 5;

    const HIDE_PLAYERS_AT_SPAWN = 6;
    const HIDE_PLAYERS_AT_EVENT = 7;
    const HIDE_PLAYERS_AT_FFA = 8;
    const INTERRUPTING = 9;

    const UI_TYPE = 10;
    const SCOREBOARD = 11;
    const DUEL_REQUESTS = 12;
    const PARTY_INVITES = 13;
    const ANIMATE_PACKS = 14;
    const SHOP_ALERT = 15;
    const STAT_RESET_ALERT = 16;
    const FFA_RESPAWN = 17;

    const UI_TYPE_CHEST = 0;
    const UI_TYPE_FORM = 1;
    const UI_TYPE_RECOMMENDED = 2;
}