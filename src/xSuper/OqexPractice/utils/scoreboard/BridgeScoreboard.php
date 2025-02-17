<?php

namespace xSuper\OqexPractice\utils\scoreboard;

use xSuper\OqexPractice\portal\Portal;
use xSuper\OqexPractice\duel\special\TheBridgeDuel;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;

class BridgeScoreboard extends Scoreboard
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

        if (!$duel instanceof TheBridgeDuel) return [];

        $s1 = $duel->getScore($player);

        $scoreT = str_repeat('', $s1);

        if ($p2->isOnline()) {
            $s2 = $duel->getScore($p2);

            $scoreT .= str_repeat('', $s2);

            if ($s1 + $s2 < 5) {
                $scoreT .= str_repeat('', 5 - ($s1 + $s2));
        }
        } else $scoreT = '';

        return [
            $date . ' ',
            ' ',
            '   §r§l§6Info',
            ' §r§fScore: ' . $scoreT,
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
        return 'bridge';
    }
}