<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\utils\scoreboard;

use pocketmine\utils\CloningRegistryTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static DuelScoreboard DUEL()
 * @method static EventScoreboard EVENT()
 * @method static FFAScoreboard FFA()
 * @method static LobbyScoreboard LOBBY()
 * @method static BridgeScoreboard BRIDGE()
 */
final class Scoreboards {
    use CloningRegistryTrait;

    private function __construct(){
        //NOOP
    }

    protected static function register(string $name, Scoreboard $form) : void{
        self::_registryRegister($name, $form);
    }

    /**
     * @return Scoreboard[]
     * @phpstan-return array<string, Scoreboard>
     */
    public static function getAll() : array{
        //phpstan doesn't support generic traits yet :(
        /** @var Scoreboard[] $result */
        $result = self::_registryGetAll();
        return $result;
    }

    protected static function setup() : void{
        self::register('duel', new DuelScoreboard());
        self::register('event', new EventScoreboard());
        self::register('ffa', new FFAScoreboard());
        self::register('lobby', new LobbyScoreboard());
        self::register('bridge', new BridgeScoreboard());
    }
}