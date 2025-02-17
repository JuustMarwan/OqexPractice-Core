<?php

namespace xSuper\OqexPractice\entities\firework;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\world\sound\Sound;

class FireworkLaunchSound implements Sound{

    public function encode(Vector3 $pos) : array{
        return [LevelSoundEventPacket::nonActorSound(LevelSoundEvent::LAUNCH, $pos, false)];
    }
}