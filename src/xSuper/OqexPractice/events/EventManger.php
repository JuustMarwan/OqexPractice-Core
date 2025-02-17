<?php

namespace xSuper\OqexPractice\events;

use Closure;

class EventManger
{
    public const SUMO = 0;
    public const JUGGERNAUT = 1;
    public const BRACKET = 2;

    private ?Event $current = null;

    /**
     * @var Closure[]
     * @phpstan-var array<int, (Closure(string) : Event)>
     */
    private array $events = [];

    public function __construct(){
        $this->events[self::SUMO] = static fn(string $creator) => new SumoEventV2($creator);
        $this->events[self::JUGGERNAUT] = static fn(string $creator) => new JuggernautEvent($creator);
        $this->events[self::BRACKET] = static fn(string $creator) => new BracketEventV2($creator);
    }

    public function createEvent(int $type, string $creator): ?Event
    {
        $closure = $this->events[$type] ?? null;
        if($closure === null){
            return null;
        }
        $this->current = $closure($creator);
        return $this->current;
    }

    public function removeEvent(): void
    {
        $this->current = null;
    }

    public function getCurrent(): ?Event
    {
        return $this->current;
    }
}