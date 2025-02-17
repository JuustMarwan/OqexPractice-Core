<?php

namespace xSuper\OqexPractice\utils\scoreboard;

use xSuper\OqexPractice\portal\Portal;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;

class DuelScoreboard extends Scoreboard
{
	/** @return list<string> */
    protected function getLines(PracticePlayer $player): array
    {
        $date = '§r§7  ' . substr(date('l'), 0, 3) . '. ' . substr(date('F'), 0, 3) . '. ' . date('d');

        $duel = $player->getDuel();
        if ($duel === null) return [];
        $p2 = $duel->opposite($player);

        if (!$p2->isOnline()) $p2P = '§cDisconnected';
        else $p2P = $p2->getNetworkSession()->getPing();

        $map = $duel->getMap();

        return [
            $date . ' ',
            ' ',
            '   §r§l§6Info',
            ' §r§fTime: §a' . $duel->getTime(),
            '   ',
            '   §r§l§6Players',
            ' §r§fYour Ping: §a' . self::colorizePing($player->getNetworkSession()->getPing() ?? 'Unknown'),
            ' §r§fTheir Ping: §a' . self::colorizePing($p2P ?? 'Unknown'),
            '     ',
            '§r§6' . OqexPractice::IP
        ];
    }

    protected function getName(): string
    {
        return 'duel';
    }
}