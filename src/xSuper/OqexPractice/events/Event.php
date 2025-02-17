<?php

namespace xSuper\OqexPractice\events;

use xSuper\OqexPractice\duel\generator\maps\EventMap;
use xSuper\OqexPractice\duel\generator\maps\Map;
use xSuper\OqexPractice\player\PracticePlayer;

abstract class Event
{
    /** @var EventMap */
    protected Map $map;

    protected bool $ended = false;

    private int $start;
	/** @var list<PracticePlayer> */
    protected array $awaitingJoin = [];

    protected bool $autoStart = true;

    final public function __construct(private string $creator)
    {
        $this->start = time();

        $map = Map::getMapByName($this->getMap());
		if(!$map instanceof EventMap){
			throw new \LogicException("Map {$this->getMap()} is not an event map");
		}
		$this->map = $map;
        $this->init();
    }

    public function getCreator(): string
    {
        return $this->creator;
    }

    public function toggleAutoStart(): void
    {
        if ($this->autoStart) $this->autoStart = false;
        else $this->autoStart = true;
    }

    public function getTime(): int
    {
        return time() - $this->start;
    }

    /** Used for players creating an event, to make them join after the map has been generated */
    public function attemptJoin(PracticePlayer $player): void
    {
        $this->awaitingJoin[] = $player;
    }

    public function isEnded(): bool
    {
        return $this->ended;
    }

    abstract public function getType(): string;
    abstract public function getMap(): string;
    abstract public function init(): void;
    abstract public function join(PracticePlayer $player): void;
	/** @return array{'yKb': float, 'xzKb': float, 'maxHeight': int<0, max>, 'revert': bool} */
    abstract public function getKB(): array;
    abstract public function getAttackCoolDown(): int;
    abstract public function disqualify(PracticePlayer|string $player): void;
    abstract public function leave(string $uuid): void;
}