<?php

namespace xSuper\OqexPractice\duel\utils;

interface LeaderboardIds
{
    public const LIFETIME = 0;
    public const MONTHLY = 1;
    public const WEEKLY = 2;
    public const DAILY = 3;

    public const AVERAGE_ELO = 0;
    public const NO_DEBUFF_ELO = 1;
    public const DEBUFF_ELO = 2;
    public const GAPPLE_ELO = 3;
    public const BUILD_UHC_ELO = 4;
    public const COMBO_ELO = 5;
    public const SUMO_ELO = 6;
    public const VANILLA_ELO = 7;
    public const ARCHER_ELO = 8;
    public const SOUP_ELO = 9;
    public const BRIDGE_ELO = 10;

    public const KILLS = 11;
    public const KILLS_LIFETIME = 12;
    public const KILLS_MONTHLY = 13;
    public const KILLS_WEEKLY = 14;
    public const KILLS_DAILY = 15;

    public const DEATHS = 16;
    public const DEATHS_LIFETIME = 17;
    public const DEATHS_MONTHLY = 18;
    public const DEATHS_WEEKLY = 19;
    public const DEATHS_DAILY = 20;

    public const KD = 21;
    public const KD_LIFETIME = 21;
    public const KD_MONTHLY = 22;
    public const KD_WEEKLY = 23;
    public const KD_DAILY = 24;

    public const ELO = 25;

    public const PARKOUR = 26;
    public const PARKOUR_LIFETIME = 27;
    public const PARKOUR_MONTHLY = 28;
    public const PARKOUR_WEEKLY = 29;
    public const PARKOUR_DAILY = 30;
}