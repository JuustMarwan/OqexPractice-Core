<?php

namespace xSuper\OqexPractice\utils\scoreboard;

use pocketmine\Server;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\jackmd\scorefactory\ScoreFactory;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;

abstract class Scoreboard
{
    public function send(PracticePlayer $player): void
    {
        if (!$player->isOnline() || !$player->isLoaded() || !$player->getData()->getSettings()->asBool(SettingIDS::SCOREBOARD)) return;

        if ($player->getScoreboard() !== $this->getName()) {
            ScoreFactory::setObjective($player, '    §r§l§ePractice   ', SetDisplayObjectivePacket::SORT_ORDER_ASCENDING, SetDisplayObjectivePacket::DISPLAY_SLOT_SIDEBAR, 'practice');
            ScoreFactory::sendObjective($player);

            $player->setScoreboard($this->getName());
        }

        foreach ($this->getLines($player) as $p => $m) {
            $l = $p + 1;
            ScoreFactory::setScoreLine($player, $l, $m);
        }

        ScoreFactory::sendLines($player);
    }

	/** @return list<string> */
    abstract protected function getLines(PracticePlayer $player): array;
    abstract protected function getName(): string;

    public static function colorizePing(string|int $ping): string
    {
        if (is_string($ping)) return $ping;
        if ($ping <= 100) {
            return '§a' . $ping;
        } else if ($ping <= 250) {
            return '§e' . $ping;
        }

        return '§c' . $ping;
    }

    public static function colorizeDelay(float $delay): string
    {
        if ($delay <= 5) {
            return '§a' . $delay;
        } else if ($delay <= 20) {
            return '§e' . $delay;
        }

        return '§c' . $delay;
    }

    public static function updateScoreBoards(Scoreboard $scoreboard): void
    {
        /** @var PracticePlayer $player */
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if ($player->getScoreboard() === $scoreboard->getName()) {
                foreach ($scoreboard->getLines($player) as $p => $m) {
                    $l = $p + 1;
                    ScoreFactory::setScoreLine($player, $l, $m);
                }

                ScoreFactory::sendLines($player);
            }
        }
    }
}