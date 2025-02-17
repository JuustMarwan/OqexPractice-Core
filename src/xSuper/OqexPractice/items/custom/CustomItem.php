<?php

namespace xSuper\OqexPractice\items\custom;

use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\block\Material;
use customiesdevs\customies\block\Model;
use customiesdevs\customies\item\CreativeInventoryInfo;
use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use xSuper\OqexPractice\player\PracticePlayer;

abstract class CustomItem
{
    /** @var self[] */
    private static array $items = [];

    public static function init(): void
    {
        self::register(InteractiveItems::DUEL_SWORD());
        self::register(InteractiveItems::RANKED_DUEL_SWORD());
        self::register(InteractiveItems::LEAVE_QUEUE());
        self::register(InteractiveItems::VANISH());
        self::register(InteractiveItems::FREEZE());
        self::register(InteractiveItems::FFA());
        self::register(InteractiveItems::EVENT());
        self::register(InteractiveItems::PARTY());
        self::register(InteractiveItems::PROFILE());
        self::register(InteractiveItems::CHECKPOINT());
        self::register(InteractiveItems::LEAVE_PARKOUR());
        self::register(InteractiveItems::LEAVE_EVENT());

        self::registerBlocks();
    }

    private static function registerBlocks(): void
    {
        foreach (['dummy', 'easy', 'normal', 'hard', 'godly'] as $type) {
            $n = $type . '_head';
            $material = new Material(Material::TARGET_ALL, $n, Material::RENDER_METHOD_BLEND);
            $model = new Model([$material], "geometry.microblock1", new Vector3(-4, 0, -4), new Vector3(8, 8, 8));
            CustomiesBlockFactory::getInstance()->registerBlock(fn() => new Block(new BlockIdentifier(BlockTypeIds::newId()), $n, new BlockTypeInfo(new BlockBreakInfo(1))), "oqex:" . $n, $model, new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_ITEMS, CreativeInventoryInfo::GROUP_SKULL));
        }

        $n = 'heart_glasses';
        $material = new Material(Material::TARGET_ALL, $n, Material::RENDER_METHOD_ALPHA_TEST);
        $model = new Model([$material], "geometry.heart_glasses", new Vector3(-4, 0, -4), new Vector3(8, 8, 8));
        CustomiesBlockFactory::getInstance()->registerBlock(fn() => new Block(new BlockIdentifier(BlockTypeIds::newId()), $n, new BlockTypeInfo(new BlockBreakInfo(1))), "practice:" . $n, $model, new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_EQUIPMENT, CreativeInventoryInfo::GROUP_HELMET));
    }

    public static function getHead(string $name): Block
    {
        return CustomiesBlockFactory::getInstance()->get('oqex:' . $name . '_head');
    }

    public static function register(self $item): void
    {
        self::$items[$item->getName()] = $item;
    }

    public static function getItem(string $name): ?self
    {
        return self::$items[$name] ?? null;
    }


    public function __construct(protected string $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function interact(PracticePlayer $p): void;
    abstract public function getActualItem(PracticePlayer $player): Item;
}