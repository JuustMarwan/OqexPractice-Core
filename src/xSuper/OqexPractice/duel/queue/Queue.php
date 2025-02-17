<?php

namespace xSuper\OqexPractice\duel\queue;

use pocketmine\item\Pufferfish;
use pocketmine\Server;
use Ramsey\Uuid\Uuid;
use xSuper\OqexPractice\duel\type\Type;
use xSuper\OqexPractice\player\PracticePlayer;

class Queue
{
    public function __construct(public readonly Type $type, public readonly string $player, public readonly bool $ranked)
    {

    }

    public function getPlayer(): ?PracticePlayer
    {
        /** @var ?PracticePlayer $p */
        $p = Server::getInstance()->getPlayerByUUID(Uuid::fromString($this->player));
        return $p;
    }
}