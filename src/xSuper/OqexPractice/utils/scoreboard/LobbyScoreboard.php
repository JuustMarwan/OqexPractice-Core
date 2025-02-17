<?php

namespace xSuper\OqexPractice\utils\scoreboard;

use xSuper\OqexPractice\duel\Duel;
use xSuper\OqexPractice\duel\queue\QueueManager;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;

class LobbyScoreboard extends Scoreboard
{
	/** @return list<string> */
    protected function getLines(PracticePlayer $player): array
    {
        $date = '§r§7  ' . substr(date('l'), 0, 3) . '. ' . substr(date('F'), 0, 3) . '. ' . date('d');
        $data = $player->getData();

        return [
            $date . ' ',
            ' ',
            '   §r§l§6Personal',
            ' §r§fKills: §a' . $data->getKills('lifetime'),
            ' §r§fDeaths: §c' . $data->getDeaths('lifetime'),
            ' §r§fCoins: §e' . number_format($data->getCoins()),
            '   ',
            '   §r§l§6Statistics',
            ' §r§fAvg. Elo: §r§d' . $data->getAverageElo(),
            ' §r§fIn Queue: §r§b' . count(QueueManager::getInstance()->getQueues()),
            ' §r§fIn Duels: §r§b' . count(Duel::getDuels()),
            '     ',
            '§r§6' . OqexPractice::IP
        ];
    }

    protected function getName(): string
    {
        return 'lobby';
    }
}