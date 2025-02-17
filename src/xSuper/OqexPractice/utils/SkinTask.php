<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\utils;

use pocketmine\scheduler\Task;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;

class SkinTask extends Task {

    public function __construct(private PracticePlayer $player){
        OqexPractice::getInstance()->getScheduler()->scheduleDelayedTask($this, 15);
    }

    public function onRun() : void{
        $this->onUpdate(0);
    }

    protected function onUpdate(int $tickDifference) : void{
        if($this->player->isOnline()){
            $this->player->setChangeSkin(true);
        }
    }
}