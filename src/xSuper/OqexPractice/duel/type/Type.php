<?php

namespace xSuper\OqexPractice\duel\type;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\utils\Config;
use xSuper\OqexPractice\OqexPractice;

abstract class Type
{
    /** @var self[] */
    private static array $types = [];

    public static Config $config;

    public static function init(): void
    {
        self::$config = new Config(OqexPractice::getInstance()->getDataFolder() . 'types.yml');

        self::$types['NoDebuff'] = Types::NO_DEBUFF();
        self::$types['Debuff'] = Types::DEBUFF();
        self::$types['Gapple'] = Types::GAPPLE();
        self::$types['BuildUHC'] = Types::BUILD_UHC();
        self::$types['Combo'] = Types::COMBO();
        self::$types['Sumo'] = Types::SUMO();
        self::$types['Vanilla'] = Types::VANILLA();
        self::$types['Archer'] = Types::ARCHER();
        self::$types['Survival Games'] = Types::SURVIVAL_GAMES();
        self::$types['Soup'] = Types::SOUP();
        self::$types['Bridge'] = Types::BRIDGE();
        self::$types['Bot'] = Types::BOT();
    }
    
    public static function set(string $gamemode, int $cooldown, float $y, float $xz, float $maxHeight, float $revert): void
    {
        self::$config->setNested("$gamemode.kb.y", $y);
        self::$config->setNested("$gamemode.kb.xz", $xz);
        self::$config->setNested("$gamemode.kb.maxHeight", $maxHeight);
        self::$config->setNested("$gamemode.kb.revert", $revert);
        self::$config->setNested("$gamemode.cooldown", $cooldown);
        
        self::$config->save();
        self::reload();
    }

    public static function reload(): void
    {
        self::$config = new Config(OqexPractice::getInstance()->getDataFolder() . 'types.yml');
    }

    public static function getType(string $type): ?Type
    {
        return self::$types[$type] ?? null;
    }

    abstract public function getMenuItem(bool $ranked = false): Item;
    abstract public function getFormImage(): ?string;
    abstract public function getKit(): string;
    abstract public function getName(): string;

    abstract public function fallDamage(): bool;
	/** @return array{yKb: float, xzKb: float, maxHeight: float|int, revert: bool} */
    public function getKB(): array
    {
		$yKb = Type::$config->getNested($this->getName() . '.kb.y', 0.394);
		if(!is_float($yKb)){
			throw new \RuntimeException('yKb must be a float');
		}
		$xzKb = Type::$config->getNested($this->getName() . '.kb.xz', 0.394);
		if(!is_float($xzKb)){
			throw new \RuntimeException('xzKb must be a float');
		}
		$maxHeight = Type::$config->getNested($this->getName() . '.kb.height', 3);
		if(!is_float($maxHeight) && !is_int($maxHeight)){
			throw new \RuntimeException('maxHeight must be a float or an integer');
		}
		$revert = Type::$config->getNested($this->getName() . '.kb.revert', 0.75);
        return [
            'yKb' => $yKb,
            'xzKb' => $xzKb,
            'maxHeight' => $maxHeight,
            'revert' => $revert,
        ];
    }
    public function getAttackCoolDown(): int
    {
		$cooldown = self::$config->getNested($this->getName() . '.cooldown', 10);
		if(!is_int($cooldown)){
			throw new \RuntimeException('cooldown must be an integer');
		}
        return $cooldown;
    }

    /** @return Block[] */
    abstract public function getBreakableBlocks(): array;
    /** @return Block[] */
    abstract public function getPlaceableBlocks(): array;
}