<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\items;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\utils\CloningRegistryTrait;

/**
 * @method static Item NAMETAG()
 * @method static Item ENDER_EYE()
 * @method static Item FIREWORK()
 * @method static Item LEAD()
 * @method static Item AXOLOTL_SPAWN_EGG()
 * @method static Item SKELETON_SPAWN_EGG()
 * @method static Item ZOMBIE_SPAWN_EGG()
 * @method static Item ARMOR_STAND()
 * @method static Item ELYTRA()
 * @method static Item CREEPER_BANNER_PATTERN()
 */
final class MoreItems{
    use CloningRegistryTrait;


    private function __construct(){
        //NOOP
    }

    protected static function register(string $name, ?Item $item) : void{
        if ($item === null) $item = VanillaBlocks::BARRIER()->asItem();
        self::_registryRegister($name, $item);
    }

    protected static function setup(): void
    {
        self::register('nametag', StringToItemParser::getInstance()->parse('name_tag'));
        self::register('ender_eye', StringToItemParser::getInstance()->parse('ender_eye'));
        self::register('firework', StringToItemParser::getInstance()->parse('firework_rocket'));
        self::register('lead', StringToItemParser::getInstance()->parse('lead'));
        self::register('axolotl_spawn_egg', StringToItemParser::getInstance()->parse('axolotl_spawn_egg'));
        self::register('skeleton_spawn_egg', StringToItemParser::getInstance()->parse('skeleton_spawn_egg'));
        self::register('zombie_spawn_egg', StringToItemParser::getInstance()->parse('zombie_spawn_egg'));
        self::register('armor_stand', StringToItemParser::getInstance()->parse('armor_stand'));
        self::register('elytra', StringToItemParser::getInstance()->parse('elyra'));
        self::register('creeper_banner_pattern', StringToItemParser::getInstance()->parse('creeper_banner_pattern'));
    }
}