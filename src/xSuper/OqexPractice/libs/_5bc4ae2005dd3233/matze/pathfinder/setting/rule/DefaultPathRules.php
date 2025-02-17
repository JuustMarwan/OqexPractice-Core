<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\libs\_5bc4ae2005dd3233\matze\pathfinder\setting\rule;

use pocketmine\block\Block;

class DefaultPathRules extends PathRules{
    public function isBlockSolid(Block $block): bool {
        return $block->isSolid();
    }

    public function isBlockPassable(Block $block): bool {
        return count($block->getCollisionBoxes()) <= 0;
    }

    public function getCostInside(Block $block): int {
        return 0;
    }

    public function getCostStanding(Block $block): int {
        return 0;
    }
}