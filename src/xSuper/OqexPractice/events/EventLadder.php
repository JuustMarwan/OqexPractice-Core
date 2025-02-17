<?php

namespace xSuper\OqexPractice\events;

class EventLadder
{
    private int $place = 0;
	/** @var list<string> */
    private array $players;

	/** @param array<string, string> $players */
    public function __construct(array $players)
    {
        $this->players = array_values($players);
    }

	/** @return list<string> */
    public function getPlayers(): array
    {
        return $this->players;
    }

	/**
	 * @phpstan-impure
	 * @return null|string|array{string, string}
	 */
    public function make(): null|string|array
    {
        $current = $this->players[$this->place] ?? $this->players[$this->place = 0] ?? null;
        if ($current === null) {
            return null;
        }
        if (count($this->players) === 1) {
            return $current;
        }
        $next = $this->players[++$this->place] ?? $this->players[$this->place = 0];
        return [$current, $next];
    }

    public function removePlayer(string $player): void
    {
        $key = array_search($player, $this->players, true);
        if ($key === false) {
            return;
        }
        unset($this->players[$key]);
        $this->players = array_values($this->players);
    }
}