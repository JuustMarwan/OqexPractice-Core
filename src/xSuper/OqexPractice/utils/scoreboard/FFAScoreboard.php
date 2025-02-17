<?php

namespace xSuper\OqexPractice\utils\scoreboard;

use xSuper\OqexPractice\portal\Portal;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;

class FFAScoreboard extends Scoreboard
{
	/** @return list<string> */
    protected function getLines(PracticePlayer $player): array
    {
        $date = '§r§7  ' . substr(date('l'), 0, 3) . '. ' . substr(date('F'), 0, 3) . '. ' . date('d');
        $ffa = $player->getFFA();

        if ($ffa === null) return [];

        $world = $ffa->getSpawn()->getWorld();

        $delay = round($world->getTickRateTime(), 2);

        $s = [
            $date . ' ',
            ' ',
            '   §r§l§6' . $ffa->getName(),
            ' §r§fPlayers: §r§d' . $ffa->getPlayers(),
            '   '
        ];

        if ($player->getTagger() !== null && $player->getTagger()->isOnline()) return array_merge($s, [
                '   §r§l§6Combat §r§f(' . $player->getCombat() . 's)',
                ' §r§fYour Ping: ' . self::colorizePing($player->getNetworkSession()->getPing() ?? 'Unknown'),
                ' §r§fTheir Ping: ' . self::colorizePing($player->getTagger()->getNetworkSession()->getPing() ?? 'Unknown'),
                '     ',
                '§r§6' . OqexPractice::IP
            ]);
        else return array_merge($s, ['§r§6' . OqexPractice::IP]);
    }

    protected function getName(): string
    {
        return 'ffa';
    }
}