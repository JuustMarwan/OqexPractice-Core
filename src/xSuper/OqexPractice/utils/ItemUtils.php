<?php

namespace xSuper\OqexPractice\utils;

use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;

class ItemUtils
{
	/**
	 * @param Item $item
	 * @param list<Enchantment> $enchantments
	 * @param list<int> $levels
	 * @return Item
	 */
    public static function enchant(Item $item, array $enchantments, array $levels): Item
    {
        foreach ($enchantments as $i => $enchantment) {
            $item->addEnchantment(new EnchantmentInstance($enchantment, $levels[$i]));
        }
        return $item;
    }

    public static function glow(Item $item): Item
    {
        $item->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(1000)));
        return $item;
    }

    public static function unGlow(Item $item): Item
    {
        $item->removeEnchantment(EnchantmentIdMap::getInstance()->fromId(1000));
        return $item;
    }

    public static function encode(Item $item): string
    {
        $serializer = new LittleEndianNbtSerializer();
        return bin2hex(zlib_encode($serializer->write(new TreeRoot($item->nbtSerialize())), ZLIB_ENCODING_GZIP));
    }

    public static function decode(string $item): Item
    {
        $serializer = new LittleEndianNbtSerializer();
        return Item::nbtDeserialize($serializer->read(zlib_decode(hex2bin($item)))->mustGetCompoundTag());
    }
}