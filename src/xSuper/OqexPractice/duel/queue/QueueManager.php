<?php

namespace xSuper\OqexPractice\duel\queue;

use Generator;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerUIIds;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use WeakMap;
use xSuper\OqexPractice\duel\Duel;
use xSuper\OqexPractice\duel\type\Type;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\utils\scoreboard\Scoreboard;
use xSuper\OqexPractice\utils\scoreboard\Scoreboards;

final class QueueManager{
    use SingletonTrait;

    private array $queues = [];

    public function addQueue(PracticePlayer $queuePlayer, Type $type, bool $ranked = false): bool
    {
        if (isset($this->queues[$queuePlayer->getUniqueId()->toString()])) {
            return false;
        }

        $queue = new Queue($type, $queuePlayer->getUniqueId()->toString(), $ranked);
        $this->queues[$queuePlayer->getUniqueId()->toString()] = $queue;

        $match = null;
		$matchPlayer = null;
        $queueManager = QueueManager::getInstance();
        /** @var Queue $queuedMatch */
        foreach ($queueManager->getQueuesByType($type, $ranked) as $queuedMatch) {
            if ($queuedMatch->player === $queuePlayer->getUniqueId()->toString()) {
                continue;
            }

            if ($queuedMatch->getPlayer() === null || !$queuedMatch->getPlayer()->isOnline() || !$queuedMatch->getPlayer()->isLoaded()) {
                continue;
            }
            $match = $queue;
			$matchPlayer = $queuedMatch->player;
            break;
        }


        if ($match === null || $matchPlayer === null) {
            Scoreboard::updateScoreBoards(Scoreboards::LOBBY());
            return false;
        }
        $queueManager->removeQueue($queuePlayer);
        $queueManager->removeQueue($matchPlayer);

        Duel::createDuel(OqexPractice::getInstance(), $match->type, [$queuePlayer, $matchPlayer], $ranked);
        Scoreboard::updateScoreBoards(Scoreboards::LOBBY());
        return true;
    }

    public function removeQueue(Player|string $player): void
    {
        if (!is_string($player)) $player = $player->getUniqueId()->toString();
        if (!isset($this->queues[$player])) {
            return;
        }

        Scoreboard::updateScoreBoards(Scoreboards::LOBBY());

        unset($this->queues[$player]);
    }

    public function getQueues(): array
    {
        return $this->queues;
    }

    /**
     * @param Type $type
     * @param bool $ranked
     * @return array
     */
    public function getQueuesByType(Type $type, bool $ranked = false): array
    {
        $queues = [];
        foreach ($this->queues as $queue) {
            if ($queue->type->getName() === $type->getName() && $queue->ranked === $ranked){
                $queues[] = $queue;
            }
        }

        return $queues;
    }

    public function isInQueue(Player $player): bool{
        return isset($this->queues[$player->getUniqueId()->toString()]);
    }
}