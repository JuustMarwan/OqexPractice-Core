<?php

namespace xSuper\OqexPractice\duel\utils;

class LeaderboardCache implements LeaderboardIds
{
	/** @var list<array{string, numeric|string}> */
    private array $data;

    public function __construct()
    {
    }

	/** @param list<array{string, numeric|string}> $data */
    public function update(array $data): void
    {
        $this->data = $data;
    }

	/** @return list<array{string, numeric|string}> */
    public function getData(): array
    {
        return $this->data;
    }

}