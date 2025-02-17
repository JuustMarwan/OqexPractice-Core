<?php

namespace xSuper\OqexPractice\utils\scoreboard;

use xSuper\OqexPractice\events\BracketEvent;
use xSuper\OqexPractice\events\BracketEventV2;
use xSuper\OqexPractice\events\JuggernautEvent;
use xSuper\OqexPractice\events\LastManStandingEvent;
use xSuper\OqexPractice\events\SumoEvent;
use xSuper\OqexPractice\events\SumoEventV2;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;

class EventScoreboard extends Scoreboard
{
	/** @return list<string> */
    protected function getLines(PracticePlayer $player): array
    {
        $date = '§r§7  ' . substr(date('l'), 0, 3) . '. ' . substr(date('F'), 0, 3) . '. ' . date('d');

        $event = $player->getEvent();

        if (!$event instanceof LastManStandingEvent && !$event instanceof BracketEventV2 && !$event instanceof JuggernautEvent && !$event instanceof SumoEventV2) return [];

        if ($event instanceof JuggernautEvent || $event instanceof SumoEventV2) $players = count($event->getRealPlayers()) . '/' . $event->max();
        else $players = count($event->getRealPlayers());

        $s = [
            $date . ' ',
            ' ',
            '   §r§l§6Event',
            ' §r§fType: §r§d' . $event->getType(),
            ' §r§fTime: §r§b' . gmdate('i:s', $event->getTime()),
            ' §r§fPlayers: §r§e' . $players,
        ];

        if (($event instanceof BracketEventV2 || $event instanceof JuggernautEvent || $event instanceof SumoEventV2) && !$event->hasStarted()) {
            if ($event->getCountdown() !== -1) $v = ' §r§fStarting In: §a' . $event->getCountdown() . 's';
            else $v =' §r§fStarting In: §aN/A';

            $s[] = $v;
        }

        return array_merge($s, [
            '     ',
            '§r§6' . OqexPractice::IP
        ]);
    }

    protected function getName(): string
    {
        return 'event';
    }
}