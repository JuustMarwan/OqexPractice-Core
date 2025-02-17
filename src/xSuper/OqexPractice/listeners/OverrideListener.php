<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\listeners;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\tile\Chest as TileChest;
use pocketmine\block\WeightedPressurePlate;
use pocketmine\block\WeightedPressurePlateHeavy;
use pocketmine\block\WeightedPressurePlateLight;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\EnderPearl;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\FishingRod;
use pocketmine\item\ItemTypeIds;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\Position;
use pocketmine\world\sound\RedstonePowerOffSound;
use pocketmine\world\sound\RedstonePowerOnSound;
use pocketmine\world\World;
use xSuper\OqexPractice\duel\queue\QueueManager;
use xSuper\OqexPractice\duel\special\SurvivalGamesDuel;
use xSuper\OqexPractice\duel\special\TheBridgeDuel;
use xSuper\OqexPractice\entities\ArrowEntity;
use xSuper\OqexPractice\entities\EnderPearlEntity;
use xSuper\OqexPractice\entities\FishingHookEntity;
use xSuper\OqexPractice\entities\pathfinder\entity\SmartEntity;
use xSuper\OqexPractice\entities\SplashPotionEntity;
use xSuper\OqexPractice\player\PracticePlayer;

final class OverrideListener implements Listener{

    public function __construct(private readonly TaskScheduler $scheduler)
    {
    }

    public function onProjectileLaunch(ProjectileLaunchEvent $event): void{
        $entity = $event->getEntity();
        if ($entity->getOwningEntity() instanceof SmartEntity) return;
        if($entity instanceof SplashPotion){
            $event->getEntity()->flagForDespawn();

            $newEntity = new SplashPotionEntity($entity->getLocation(), $entity->getOwningEntity(), $entity->getPotionType(), $entity->getMotion()->multiply(0.2 / 0.5));
            $newEntity->spawnToAll();
        }elseif($entity instanceof EnderPearl){
            $event->getEntity()->flagForDespawn();

            $newEntity = new EnderPearlEntity($entity->getLocation(), $entity->getOwningEntity());
            $newEntity->setMotion($entity->getMotion()->multiply(2.5 / 1.5));
            $newEntity->spawnToAll();
        }elseif ($entity instanceof Arrow) {
            $event->getEntity()->flagForDespawn();

            $newEntity = new ArrowEntity($entity->getLocation(), $entity->getOwningEntity(), $entity->isCritical());
            $newEntity->setMotion($entity->getMotion());
            $newEntity->spawnToAll();
        }
    }

    public function onPlayerItemUse(PlayerItemUseEvent $event): void
    {

        $player = $event->getPlayer();
        if(!$player instanceof PracticePlayer){
            return;
        }
        $item = $player->getInventory()->getItemInHand();

        if($item instanceof FishingRod){
                if ($player->getFishing() === null) {
                    $motion = $player->getDirectionVector();
                    $motion = $motion->multiply(1.9);

                    $pos = $player->getLocation()->add(0, $player->getEyeHeight(), 0);
                    $hook = new FishingHookEntity(new Location($pos->getX(), $pos->getY(), $pos->getZ(), $player->getWorld(), 0, 0), $player);
                    $hook->setMotion($motion);
                    if (($e = $item->getEnchantment(VanillaEnchantments::FLAME())) !== null) $hook->flame();

                    $hook->spawnToAll();
                } else {
                    $hook = $player->getFishing();
                    if (!$hook->isFlaggedForDespawn() && !$hook->isClosed()) $hook->flagForDespawn();
                    $player->stopFishing();
                }

                $pk = new ActorEventPacket();
                $pk->actorRuntimeId = $player->getId();
                $pk->eventId = 1;

                $player->getWorld()->broadcastPacketToViewers($player->getLocation(), $pk);
                $player->resetItemCooldown($item, 3);
        }elseif($item->getTypeId() === ItemTypeIds::ENDER_PEARL){
                if ($player->canPearl() && $player->getTargetBlock(2) instanceof Air) {
                    $player->pearl();
                } else {
                    $event->cancel();
                }
        }elseif($item->getTypeId() === ItemTypeIds::MUSHROOM_STEW){
                $hp = $player->getHealth();
                if ((int) $hp === $player->getMaxHealth()) {
                    $event->cancel();
                    return;
                }
                $hp = $hp + 10.0;
                if ($hp >= $player->getMaxHealth()) $hp = $player->getMaxHealth();
                $player->setHealth($hp);

                $player->getInventory()->removeItem($item);

                $player->sendSound('random.burp');
                $player->resetItemCooldown($item, 3);
        }elseif($item->getTypeId() === ItemTypeIds::fromBlockTypeId(BlockTypeIds::MOB_HEAD)){
                $hp = $player->getHealth();
                if ((int) $hp === $player->getMaxHealth()) {
                    $event->cancel();
                    return;
                }
                $hp = $hp + 5.0;
                if ($hp >= $player->getMaxHealth()) $hp = $player->getMaxHealth();
                $player->setHealth($hp);

                $item->pop();
                $player->getInventory()->setItemInHand($item);
                $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 5 * 20, 1, false, false));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 5 * 20, 1, false, false));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::FIRE_RESISTANCE(), 10 * 20, 0, false, false));

                $player->sendSound('random.burp');
                $player->resetItemCooldown($item, 3);
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event): void
    {
        if($event->getItem()->getTypeId() === ItemTypeIds::fromBlockTypeId(BlockTypeIds::MOB_HEAD)){
            $event->cancel();
        }
    }

    public function onPlayerInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        if(!$player instanceof PracticePlayer){
            return;
        }

        if (($block = $event->getBlock())->getTypeId() === BlockTypeIds::CHEST) {
            $event->cancel();
            $position = $block->getPosition();
            $chest = $position->getWorld()->getTile($position);
            $item = $player->getInventory()->getItemInHand();
            if ($chest instanceof TileChest){
                if($block->getSide(Facing::UP)->isTransparent() && (($pair = $chest->getPair()) === null || $pair->getBlock()->getSide(Facing::UP)->isTransparent()) && $chest->canOpenWith($item->getCustomName())) {
                    $duel = $player->getDuel();
                    if ($duel instanceof SurvivalGamesDuel) {
                        if (count($chest->getInventory()->getContents()) === 0) {
                            $items = SurvivalGamesDuel::lootPool($chest->getPosition(), $duel);

                            if (count($items) === 1) {
                                $chest->getInventory()->setItem(rand(0, 26), $items[0]);
                                return;
                            }

                            $slots = [];
                            for ($x = 0; $x <= 26; $x++) {
                                if ($chest->getInventory()->getItem($x)->isNull()) $slots[] = $x;
                            }

                            $use = [];

							$keys = array_rand($slots, count($items));
							if(!is_array($keys)){
								throw new AssumptionFailedError('This should return an array');
							}
							foreach ($keys as $key) {
                                $use[] = $slots[$key];
                            }

                            for ($x = 0; $x <= count($items) - 1; $x++) {
                                $chest->getInventory()->setItem($use[$x], $items[$x]);
                            }
                        }
                    }

                    $player->setCurrentWindow($chest->getInventory());
                }
                return;

            } else if ($chest === null) {
                $chest = new TileChest($position->getWorld(), $position);
                $position->getWorld()->addTile($chest);

                if (!$block->getSide(Facing::UP)->isTransparent() || (($pair = $chest->getPair()) !== null && !$pair->getBlock()->getSide(Facing::UP)->isTransparent()) || !$chest->canOpenWith($item->getCustomName())) return;

                $duel = $player->getDuel();
                if ($duel instanceof SurvivalGamesDuel) {
                    if (count($chest->getInventory()->getContents()) === 0) {
                        $items = SurvivalGamesDuel::lootPool($chest->getPosition(), $duel);

                        if (count($items) === 1) {
                            $chest->getInventory()->setItem(rand(0, 26), $items[0]);
                            return;
                        }

                        $slots = [];
                        for ($x = 0; $x <= 26; $x++) {
                            if ($chest->getInventory()->getItem($x)->isNull()) $slots[] = $x;
                        }

                        $use = [];

						$keys = array_rand($slots, count($items));
						if(!is_array($keys)){
							throw new AssumptionFailedError('This should return an array');
						}
						foreach ($keys as $key) {
                            $use[] = $slots[$key];
                        }

                        for ($x = 0; $x <= count($items) - 1; $x++) {
                            $chest->getInventory()->setItem($use[$x], $items[$x]);
                        }
                    }
                }

                $player->setCurrentWindow($chest->getInventory());
            }
        }
    }

    public function onPlayerMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        if(!$player instanceof PracticePlayer){
            return;
        }

        foreach (OverrideListener::getSurroundingBlocks($player->getWorld(), $player->getBoundingBox()) as $block) {
            $position = $block->getPosition();
            if($block instanceof WeightedPressurePlateLight){
                $boundingBox = new AxisAlignedBB(
                    $position->getX() + 0.0625,
                    $position->getY(),
                    $position->getZ() + 0.0625,
                    $position->getX() + 0.9375,
                    $position->getY() + 0.0625,
                    $position->getZ() + 0.9375
                );
                $entities = $position->getWorld()->getNearbyEntities($boundingBox);
                $count = count($entities);

                if ($block->getOutputSignalStrength() === 0) {
                    $position->getWorld()->addSound($position->add(0.5, 0.5, 0.5), new RedstonePowerOnSound());
                }
                $block->setOutputSignalStrength(min((int) (($count + 9) / 10), 15));
                $position->getWorld()->setBlock($position, $block);
                $this->scheduler->scheduleDelayedTask(new ClosureTask(fn() => $this->onWeightedPressurePlateUpdate($position)), 10);
                if ($player->getParkour() !== null) $player->setParkour(-1);
            }elseif($block instanceof WeightedPressurePlateHeavy){
                $boundingBox = new AxisAlignedBB(
                    $position->getX() + 0.0625,
                    $position->getY(),
                    $position->getZ() + 0.0625,
                    $position->getX() + 0.9375,
                    $position->getY() + 0.0625,
                    $position->getZ() + 0.9375
                );
                $entities = $position->getWorld()->getNearbyEntities($boundingBox);
                $count = count($entities);

                if ($block->getOutputSignalStrength() === 0) {
                    $position->getWorld()->addSound($position->add(0.5, 0.5, 0.5), new RedstonePowerOnSound());
                }
                $block->setOutputSignalStrength(min((int) (($count + 9) / 10), 15));
                $position->getWorld()->setBlock($position, $block);
                $this->scheduler->scheduleDelayedTask(new ClosureTask(fn() => $this->onWeightedPressurePlateUpdate($position)), 10);
                if ($player->getParkour() === null && !QueueManager::getInstance()->isInQueue($player) && $position->getX() == -34 && $position->getZ() === -18) {
                    $player->startParkour();
                    $player->setCheckpoint($position);
                    continue;
                }

                $player->setCheckpoint($position);
            }else if ($block->getName() === 'End Portal') {
                $duel = $player->getDuel();
                if ($duel instanceof TheBridgeDuel && !$duel->isEnded()) {
                    $pos = $block->getPosition();

                    $spawn = $duel->getSpawn($player);

                    if ($pos->distance($spawn) <= 20) $duel->resetPlayer($player);
                    else $duel->score($player);
                }
            }
        }
    }

	/** @return list<Block> */
	private static function getSurroundingBlocks(World $world, AxisAlignedBB $bb): array{
		$minX = (int) floor($bb->minX - 1);
		$minY = (int) floor($bb->minY - 1);
		$minZ = (int) floor($bb->minZ - 1);
		$maxX = (int) floor($bb->maxX + 1);
		$maxY = (int) floor($bb->maxY + 1);
		$maxZ = (int) floor($bb->maxZ + 1);

		$collides = [];
		for($z = $minZ; $z <= $maxZ; ++$z){
			for($x = $minX; $x <= $maxX; ++$x){
				for($y = $minY; $y <= $maxY; ++$y){
					$block = $world->getBlockAt($x, $y, $z);
					if($block->collidesWithBB($bb) || ($block instanceof WeightedPressurePlate && (
							new AxisAlignedBB(
								$x + 0.0625,
								$y,
								$z + 0.0625,
								$x + 0.9375,
								$y + 0.0625,
								$z + 0.9375
							))->intersectsWith($bb))){
						$collides[] = $block;
					}
				}
			}
		}
		return $collides;
	}

    private function onWeightedPressurePlateUpdate(Position $pos): void
    {
        $block = $pos->getWorld()->getBlock($pos);
        if(!$block instanceof WeightedPressurePlate){
            return;
        }

        if ($block->getOutputSignalStrength() === 0) return;

        $boundingBox = new AxisAlignedBB(
            $pos->getX() + 0.0625,
            $pos->getY(),
            $pos->getZ() + 0.0625,
            $pos->getX() + 0.9375,
            $pos->getY() + 0.0625,
            $pos->getZ() + 0.9375
        );
        $entities = $pos->getWorld()->getNearbyEntities($boundingBox);
        $count = count($entities);
        if ($count !== 0) {
            $this->scheduler->scheduleDelayedTask(new ClosureTask(fn() => $this->onWeightedPressurePlateUpdate($pos)), 20);
        }

        $power = min((int) (($count + 9) / 10), 15);
        if ($block->getOutputSignalStrength() === $power) return;

        if ($power === 0) {
            $block->getPosition()->getWorld()->addSound($block->getPosition()->add(0.5, 0.5, 0.5), new RedstonePowerOffSound());
        }

        $block->setOutputSignalStrength($power);
        $block->getPosition()->getWorld()->setBlock($block->getPosition(), $block);
    }
}