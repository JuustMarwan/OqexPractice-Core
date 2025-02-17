<?php

namespace xSuper\OqexPractice\entities\firework;

use pocketmine\entity\animation\Animation;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\types\ActorEvent;

final class FireworkParticlesAnimation implements Animation{

    public function __construct(
        private FireworkRocket $entity
    ){}

    public function encode() : array{
        return [
            ActorEventPacket::create($this->entity->getId(), ActorEvent::FIREWORK_PARTICLES, 0)
        ];
    }
}