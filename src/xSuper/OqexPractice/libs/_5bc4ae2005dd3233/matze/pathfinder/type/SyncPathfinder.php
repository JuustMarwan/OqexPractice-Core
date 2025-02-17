<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\libs\_5bc4ae2005dd3233\matze\pathfinder\type;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\matze\pathfinder\BasePathfinder;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\matze\pathfinder\IPathfinder;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\matze\pathfinder\setting\Settings;
use pocketmine\block\Block;
use pocketmine\world\World;

class SyncPathfinder extends BasePathfinder implements IPathfinder {
    public function __construct(
        Settings $settings,
        protected World $world
    ){
        parent::__construct($settings);
    }

    protected function getBlockAt(int $x, int $y, int $z): Block{
        return $this->world->getBlockAt($x, $y, $z);
    }
}