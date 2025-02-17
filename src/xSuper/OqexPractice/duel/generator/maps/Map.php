<?php

namespace xSuper\OqexPractice\duel\generator\maps;

use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use xSuper\OqexPractice\duel\type\SumoType;
use xSuper\OqexPractice\duel\type\SurvivalGamesType;
use xSuper\OqexPractice\duel\type\TheBridgeType;
use xSuper\OqexPractice\duel\type\Type;
use xSuper\OqexPractice\OqexPractice;

class Map
{
    public const ALL = 0;
    public const SUMO = 1;
    public const BRIDGE = 2;
    public const SG = 3;
    public const EVENT = 4;

    /** @var self[] */
    protected static array $maps = [];

    public static function translateType(Type $type): int
    {
        if ($type instanceof SurvivalGamesType) return self::SG;
        if ($type instanceof TheBridgeType) return self::BRIDGE;
        if ($type instanceof SumoType) return self::SUMO;
        return self::ALL;
    }

    public static function init(): void
    {
        for ($x = 1; $x <= 18; $x++) {
            self::make('Duel' . $x);
        }

        for ($x = 1; $x <= 13; $x++) {
            if ($x > 3) self::makeSumo('SumoArena' . $x,1);
        }

        for ($x = 4; $x <= 10; $x++) {
            self::makeBridge('TheBridge' . $x);
        }

        for ($x = 1; $x <= 2; $x++) {
            self::makeSG('SG' . $x);
        }

        self::makeEvent('SumoEvent');
        self::makeEvent('JuggernautEvent');
        self::makeEvent('BracketEvent');
    }

    public static function getPosition(string $realName, int $number, ?string $override = null): Vector3
    {
        $conf = new Config(OqexPractice::getInstance()->getDataFolder() . '/maps.yml');

        if ($override === null) $cords = $conf->getNested("maps.{$realName}.spawns.{$number}", []);
        else $cords = $conf->getNested("maps.{$realName}.spawns.{$override}", []);

        return new Vector3($cords[0], $cords[1], $cords[2]);
    }

    public static function make(string $realName): void
    {
        [$name, $author, $season] = self::getMapInfo($realName);

        self::$maps[$realName] = new self($realName, self::getPosition($realName, 1), self::getPosition($realName, 2), self::ALL, $name, $author, (string)$season);
    }

    public static function makeSumo(string $realName, int $min): void
    {
		[$name, $author, $season] = self::getMapInfo($realName);

        $p1 = self::getPosition($realName, 1);
        $min = $p1->getFloorY() - $min;
        self::$maps[$realName] = new SumoMap($realName, $p1, self::getPosition($realName, 2), $min, self::SUMO, $name, $author, (string)$season);
    }

    public static function makeEvent(string $realName): void
    {
        [$name, $author, $season, $conf] = self::getMapInfo($realName);

        $data = $conf->getNested("maps.{$realName}.spawns.event", []);
        if(!is_array($data)){
            throw new \TypeError('Expected array, got ' . gettype($data));
        }

        self::$maps[$realName] = new EventMap($realName, self::getPosition($realName, 1), self::getPosition($realName, 2), $data, self::EVENT, $name, $author, (string)$season);
    }

    public static function makeBridge(string $realName): void
    {
        [$name, $author, $season, $conf] = self::getMapInfo($realName);
        $red = $conf->getNested("maps.{$realName}.spawns.red", 1);
        if(!is_int($red)){
            throw new \TypeError('Expected integer, got ' . gettype($red));
        }

        self::$maps[$realName] = new BridgeMap($realName, self::getPosition($realName, 1), self::getPosition($realName, 2), self::getPosition($realName, 1, 'p1'), self::getPosition($realName, 1, 'p2'), $red, self::BRIDGE, $name, $author, (string) $season);
    }

    public static function makeSG(string $realName): void
    {
        [$name, $author, $season, $conf] = self::getMapInfo($realName);

        $low = $conf->getNested("maps.{$realName}.spawns.low", 1);
        if(!is_int($low)){
            throw new \TypeError('Expected integer, got ' . gettype($low));
        }
        $mid = $conf->getNested("maps.{$realName}.spawns.mid", 1);
        if(!is_int($mid)){
            throw new \TypeError('Expected integer, got ' . gettype($mid));
        }
        $high = $conf->getNested("maps.{$realName}.spawns.high", 1);
        if(!is_int($high)){
            throw new \TypeError('Expected integer, got ' . gettype($high));
        }

        self::$maps[$realName] = new SurvivalGamesMap($realName, self::getPosition($realName, 1), self::getPosition($realName, 2), self::getPosition($realName, 1, 'middle'), $low, $mid, $high, self::SG, $name, $author, (string) $season);
    }

    public static function getMap(string $realName): ?self
    {
        return self::$maps[$realName] ?? null;
    }

    public static function getMapByName(string $name): ?self
    {
        foreach (self::$maps as $map) {
            if ($map->getName() === $name) return $map;
        }

        return null;
    }

    /** @return list<Map> */
    public static function getMapsByType(int $type): array
    {
        $maps = [];

        foreach (self::$maps as $map) {
            if ($map->getType() === $type) $maps[] = $map;
        }

        return $maps;
    }

    public function __construct(protected string $realName, protected Vector3 $spawn1, protected Vector3 $spawn2, protected int $type, protected string $name, protected string $author, protected string $season)
    {
    }

    /** @return array{string, string, int, Config} */
    private static function getMapInfo(string $realName): array
    {
        $conf = new Config(OqexPractice::getInstance()->getDataFolder() . '/maps.yml');

        $name = $conf->getNested("maps.{$realName}.spawns.name", $realName);
        if (!is_string($name)) {
            throw new \TypeError('Expected string, got ' . gettype($name));
        }
        $author = $conf->getNested("maps.{$realName}.spawns.builder", $realName);
        if (!is_string($author)) {
            throw new \TypeError('Expected string, got ' . gettype($name));
        }
        $season = (int) $conf->getNested("maps.{$realName}.spawns.season", 1);
        if (!is_int($season)) {
            throw new \TypeError('Expected integer, got ' . gettype($name));
        }
        return [$name, $author, $season, $conf];
    }

    public function getRealName(): string
    {
        return $this->realName;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getSeason(): string
    {
        return $this->season;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSpawn1(): Vector3
    {
        return $this->spawn1;
    }

    public function getSpawn2(): Vector3
    {
        return $this->spawn2;
    }

    public function getType(): int
    {
        return $this->type;
    }
}