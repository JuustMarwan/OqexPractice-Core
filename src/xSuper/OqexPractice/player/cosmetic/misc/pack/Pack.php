<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\player\cosmetic\misc\pack;

use pocketmine\item\Item;

class Pack
{
    public string $name;
	/** @var list<PackReward> */
    public array $drops;
    private Item $item;

	/** @param list<PackReward> $drops */
    public function __construct(string $name, array $drops, Item $item)
    {
        $this->name = $name;
        $this->drops = $drops;
        $item->getNamedTag()->setString('pack', $name);
        $this->item = $item;
    }

    public function getName(): string
    {
        return $this->name;
    }

	/** @return list<PackReward> */
    public function getDrops(): array
    {
        return $this->drops;
    }

	/** @return list<PackReward> */
    public function getDrop(int $amount): array
    {
        $dropTable = [];
        foreach ($this->drops as $drop) {
            for ($i = 0; $i < $drop->getChance(); $i++) {
                $dropTable[] = $drop;
            }
        }

        $keys = array_rand($dropTable, $amount);
        if (!is_array($keys)) $keys = [$keys];
        return array_map(function ($key) use ($dropTable) {
            return $dropTable[$key];
        }, $keys);
    }

    public function getItem(): Item
    {
        return $this->item;
    }
}