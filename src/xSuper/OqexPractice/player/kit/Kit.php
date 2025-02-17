<?php

namespace xSuper\OqexPractice\player\kit;

use pocketmine\item\Item;

abstract class Kit
{
    /** @var self[] */
    private static array $kits;

    public static function init(): void
    {
        self::$kits['NoDebuff'] = new NoDebuffKit('NoDebuff');
        self::$kits['Debuff'] = new DebuffKit('Debuff');
        self::$kits['Gapple'] = new GappleKit('Gapple');
        self::$kits['BuildUHC'] = new BuildUHCKit('BuildUHC');
        self::$kits['Combo'] = new ComboKit('Combo');
        self::$kits['Sumo'] = new SumoKit('Sumo');
        self::$kits['Vanilla'] = new VanillaKit('Vanilla');
        self::$kits['Archer'] = new ArcherKit('Archer');
        self::$kits['SurvivalGames'] = new SurvivalGamesKit('SurvivalGames');
        self::$kits['Soup'] = new SoupKit('Soup');
        self::$kits['TheBridge'] = new TheBridgeKit('TheBridge');
    }

	/** @return array<string, self> */
    public static function getKits(): array
    {
        return self::$kits;
    }

    public static function getKit(string $name): ?self
    {
        return self::$kits[$name] ?? null;
    }

    public function __construct(protected string $name)
    {

    }

    public function getName(): string
    {
        return $this->name;
    }

	/** @return array<int, Item> */
    abstract public function getContents(): array;

	/** @return array<int, Item> */
    abstract public function getArmor(): array;
    abstract public function getEffects(): array;
}