<?php

namespace xSuper\OqexPractice\listeners;

use pocketmine\block\Barrel;
use pocketmine\block\BaseSign;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Button;
use pocketmine\block\Chest;
use pocketmine\block\CraftingTable;
use pocketmine\block\Door;
use pocketmine\block\FenceGate;
use pocketmine\block\ItemFrame;
use pocketmine\block\Liquid;
use pocketmine\block\Trapdoor;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockMeltEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerToggleSwimEvent;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\particle\BlockBreakParticle;
use xSuper\OqexPractice\duel\generator\maps\BridgeMap;
use xSuper\OqexPractice\duel\special\TheBridgeDuel;
use xSuper\OqexPractice\duel\type\SurvivalGamesType;
use xSuper\OqexPractice\ffa\BUHCFFA;
use xSuper\OqexPractice\ffa\BuildFFA;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;

class WorldProtectionListener implements Listener
{
    public static function canPlace(Player $player, Block $b): bool
    {
        $canPlace = false;
        if ($player instanceof PracticePlayer && $player->isLoaded()) {
            if (($duel = $player->getDuel()) !== null) {
                $blocks = $duel->getType()->getPlaceableBlocks();
                foreach ($blocks as $block) {
                    if ($b->getStateId() === $block->getStateId()) {
                        if ($duel instanceof TheBridgeDuel) {
                            /** @var BridgeMap $map */
                            $map = $duel->getMap();

                            $p1 = $map->getPortal1();
                            $p2 = $map->getPortal2();

                            if ($b->getPosition()->distance($p1) <= 5 || $b->getPosition()->distance($p2) <= 5) $canPlace = false;
                            else if ($player->canPlace()) {
                                $canPlace = true;
                                $duel->addPlaced($b->getPosition());
                            }
                        } else {
                            $canPlace = true;
                            $duel->addPlaced($b->getPosition());
                        }
                    }
                }
            }

            if ($player->getData()->isOP() && $player->getGamemode()->equals(GameMode::CREATIVE())) $canPlace = true;
        }

        return $canPlace;
    }

    public static function canBreak(Player $player, Block $b): bool
    {
        $canBreak = false;
        if ($player instanceof PracticePlayer && $player->isLoaded()) {
            if (($duel = $player->getDuel()) !== null) {
                $blocks = $duel->getType()->getBreakableBlocks();
                foreach ($blocks as $block) {
                    if ($b->getTypeId() === $block->getTypeId() && $duel->isPlaced($b->getPosition())) {
                        $canBreak = true;
                        $duel->removePlaced($b->getPosition());
                        echo "b\n";
                    }
                }
            }

            if ($player->getData()->isOP() && $player->getGamemode()->equals(GameMode::CREATIVE())) $canBreak = true;
        }

        return $canBreak;
    }

    /** @priority HIGH */
    public function onBreak(BlockBreakEvent $ev): void
    {
        $player = $ev->getPlayer();
        $b = $ev->getBlock();
        if ($player instanceof PracticePlayer && $player->isLoaded()) {
            if ($player->getFFA() instanceof BuildFFA) {
                if ($b->getTypeId() === BlockTypeIds::SANDSTONE) {
                    $player->getInventory()->addItem($b->asItem());
                    $ev->setDrops([]);
                    return;
                }
            } else if (($ffa = $player->getFFA()) instanceof BUHCFFA) {
                if ($b->getTypeId() === BlockTypeIds::COBBLESTONE || $b->getTypeId() === BlockTypeIds::OAK_PLANKS) {
                    /** @var BUHCFFA $ffa */
                    if ($ffa->isPlaced($b->getPosition())) {
                        $player->getInventory()->addItem($b->asItem());
                        $ev->setDrops([]);
                        $ffa->removePlaced($b->getPosition());
                        return;
                    }
                }
            }
        }
       if (!self::canBreak($player, $b)) {
           $ev->cancel();
       }
    }

    /** @priority HIGH */
    public function onPlace(BlockPlaceEvent $ev): void
    {
        $player = $ev->getPlayer();
        $bs = $ev->getTransaction()->getBlocks();
        if ($player instanceof PracticePlayer && $player->isLoaded()) {
            foreach ($bs as $b) {
                /** @var Block $b */
                $b = $b[3];
                if ($player->getFFA() instanceof BuildFFA) {
                    if ($b->getTypeId() === BlockTypeIds::SANDSTONE) {
                        $pos = $b->getPosition();
                        OqexPractice::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($b, $player, $pos): void {
                            if (!$pos->getWorld()->getBlock($pos) instanceof $b) return;

                            $pos->getWorld()->addParticle($pos, new BlockBreakParticle($b));
                            $pos->getWorld()->setBlock($pos, VanillaBlocks::AIR());

                            if ($player->isOnline() && $player->getFFA() instanceof BuildFFA) {
                                $c = 0;
                                foreach ($player->getInventory()->getContents(false) as $i) {
                                    if ($i->getTypeId() === BlockTypeIds::SANDSTONE) {
                                        $c += $i->getCount();
                                    }
                                }

                                if ($c >= 64) return;
                                $player->getInventory()->addItem($b->asItem());
                            }
                        }), 20 * 7);

                        return;
                    }
                }

                if (($ffa = $player->getFFA()) instanceof BUHCFFA) {
                    if ($b->getTypeId() === BlockTypeIds::COBBLESTONE || $b->getTypeId() === BlockTypeIds::OAK_PLANKS) {
                        if ($b->getPosition()->getY() <= 90) {
                            /** @var BUHCFFA $ffa */
                            /**$pos = $b->getPosition();
                             * OqexPractice::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($b, $player, $pos): void {
                             * if (!$pos->getWorld()->getBlock($pos) instanceof $b) return;
                             *
                             * $pos->getWorld()->addParticle($pos, new BlockBreakParticle($b));
                             * $pos->getWorld()->setBlock($pos, VanillaBlocks::AIR());
                             *
                             * if ($player->isOnline() && $player->getFFA() instanceof BuildFFA) {
                             * $c = 0;
                             * foreach ($player->getInventory()->getContents(false) as $i) {
                             * if ($i->getTypeId() === BlockTypeIds::SANDSTONE) {
                             * $c += $i->getCount();
                             * }
                             * }
                             *
                             * if ($c >= 64) return;
                             * $player->getInventory()->addItem($b->asItem());
                             * }
                             * }), 20 * 7); */

                            $ffa->addPlaced($b->getPosition());

                            // TODO: Auto break oir msth

                            return;
                        }
                    }
                }

                if (!self::canPlace($player, $b)) $ev->cancel();
            }
        }
    }

    public function onDecay(LeavesDecayEvent $ev): void
    {
        $ev->cancel();
    }

    public function onSwim(PlayerToggleSwimEvent $ev): void
    {
        $ev->cancel();
    }

    public function onInteract(PlayerInteractEvent $ev): void
    {
        $block = $ev->getBlock();
        $player = $ev->getPlayer();
        /** @var PracticePlayer $player */

        if (!$player->isLoaded()) {
            $ev->cancel();
            return;
        }

        if ($player->getDuel() !== null && $player->getDuel()->getType() instanceof SurvivalGamesType) {
            if ($block instanceof CraftingTable || $block instanceof Button || $block instanceof Trapdoor) $ev->cancel();
        } else {
            if ($block instanceof Chest || $block instanceof CraftingTable || $block instanceof Button || $block instanceof Trapdoor || $block instanceof Door || $block instanceof FenceGate || $block instanceof ItemFrame || $block instanceof Barrel || $block instanceof BaseSign) $ev->cancel();
        }
    }

    public function onDrop(PlayerDropItemEvent $ev): void
    {
        $player = $ev->getPlayer();
        if ($player instanceof PracticePlayer) {
            if (!$player->isLoaded()) $ev->cancel();

            $duel = $player->getDuel();
            if ($duel === null) {
                $ev->cancel();
                return;
            }

            if ($duel instanceof TheBridgeDuel) {
                if (!$player->canPlace()) $ev->cancel(); // In respawn stage of The Bridge
            }
        }
    }

    public function onMelt(BlockMeltEvent $ev): void
    {
        $ev->cancel();
    }

    public function onSpread(BlockSpreadEvent $ev): void
    {
        if (in_array($ev->getBlock()->getTypeId(), [BlockTypeIds::LAVA, BlockTypeIds::WATER])) return;
        $ev->cancel();
    }

    public function onUpdate(BlockUpdateEvent $ev): void
    {
        if (in_array($ev->getBlock()->getTypeId(), [BlockTypeIds::LAVA, BlockTypeIds::WATER])) return;
        $ev->cancel();
    }
}