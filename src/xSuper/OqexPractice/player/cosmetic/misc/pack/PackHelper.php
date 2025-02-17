<?php

namespace xSuper\OqexPractice\player\cosmetic\misc\pack;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Location;
use pocketmine\item\VanillaItems;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\Position;
use xSuper\OqexPractice\entities\PackItemEntity;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\cosmetic\CosmeticManager;
use xSuper\OqexPractice\player\PracticePlayer;

class PackHelper
{
	/** @var array<string, Pack> */
    private static array $packs;

    public static function init(): void
    {
    }

    public static function getPack(string $name): ?Pack
    {
        if (!isset(self::$packs[$name])) return null;
        else return self::$packs[$name];
    }

    public static function animatePack(string $name, PracticePlayer $player): void
    {
        $player->removePack($name);

        $pack = self::getPack($name) ?? throw new \InvalidArgumentException("Pack $name does not exist");
        $items = $pack->getDrops();
        $player->setOpeningPack(true);
        $position = new Position(-186.5, 56, -0.5, $player->getWorld());
        $i = 0;
        $iEntities = [];
        for ($theta = 0; $theta <= 360; $theta += 36) {
            $pos = $position->add(2.5 * sin($theta), 0.5, 2.5 * cos($theta));
            if (!isset($items[$i])) $item = VanillaItems::AIR();
            else $item = $items[$i]->getItem();

            if (!$item->isNull()) {
                $loc = new Location($pos->getX(), $pos->getY(), $pos->getZ(), $player->getWorld(), 0, 0);
                $itemEntity = new PackItemEntity($loc, $item);
				$itemEntity->spawnTo($player);

                $itemEntity->setNameTagAlwaysVisible(true);
                $itemEntity->setNameTag($item->getName());
                $iEntities[$i] = $itemEntity;
            }

            $i++;
        }

        $x = 0;
        $left = $items;
        $save = $pack->getDrop(1)[0];
        OqexPractice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($player, $iEntities, $items, &$x, &$left, $save): void {
            if (!$player->isOnline()) return; // TODO: Offline
            if (count($left) === 1) {
                /** @var PackItemEntity $entity */
                $entity = $iEntities[array_search($save, $items, true)];
                $player->sendSound('firework.launch');

                OqexPractice::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
                    $player->sendSound('firework.large_blast');
                }), 20);

                OqexPractice::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($entity, $player, $save): void {
                    if (!$entity->isFlaggedForDespawn() && !$entity->isClosed()) $entity->flagForDespawn();
                    $player->setOpeningPack(false);
                    $player->getData()->getCosmetics()->addCosmetic($player->getUniqueId(), $save->getType(), $save->getCID());
                }), 60);

                throw new CancelTaskException();
            }

            $item = $items[$x];
            if ($save->getCID() === $item->getCID() && $save->getType() === $item->getType()) $x++;
            /** @var PackItemEntity $entity */
            $entity = $iEntities[$x];
           if (!$entity->isFlaggedForDespawn() && !$entity->isClosed()) $entity->flagForDespawn();
            $pitch = match ($x) {
                default => 0.2,
                1 => 0.3,
                2 => 0.4,
                3 => 0.5,
                4 => 0.6,
                5 => 0.7,
                6 => 0.8,
                7 => 0.9,
                8 => 1,
                9 => 1.1,
                10 => 1.2,
                11 => 1.3,
            };
            $player->sendSound('vr.stutterturn', $pitch);
            unset($left[array_search($item, $left, true)]);
            $x++;

        }), 20);
    }
}