<?php

namespace xSuper\OqexPractice\entities\firework;

use pocketmine\utils\SingletonTrait;

final class FireworkRocketTypeIdMap{
    use SingletonTrait;

    /**
     * @var FireworkRocketType[]
     * @phpstan-var array<int, FireworkRocketType>
     */
    private array $idToEnum = [];

    /**
     * @var int[]
     * @phpstan-var array<int, int>
     */
    private array $enumToId = [];

    private function __construct(){
        $this->register(FireworkRocketTypeIds::SMALL_BALL, FireworkRocketType::SMALL_BALL);
        $this->register(FireworkRocketTypeIds::LARGE_BALL, FireworkRocketType::LARGE_BALL);
        $this->register(FireworkRocketTypeIds::STAR, FireworkRocketType::STAR);
        $this->register(FireworkRocketTypeIds::CREEPER, FireworkRocketType::CREEPER);
        $this->register(FireworkRocketTypeIds::BURST, FireworkRocketType::BURST);
    }

    private function register(int $id, FireworkRocketType $type) : void{
        $this->idToEnum[$id] = $type;
        $this->enumToId[$type->id()] = $id;
    }

    public function fromId(int $id) : ?FireworkRocketType{
        return $this->idToEnum[$id] ?? null;
    }

    public function toId(FireworkRocketType $type) : int{
        if(!isset($this->enumToId[$type->id()])){
            throw new \InvalidArgumentException("Type does not have a mapped ID");
        }
        return $this->enumToId[$type->id()];
    }
}