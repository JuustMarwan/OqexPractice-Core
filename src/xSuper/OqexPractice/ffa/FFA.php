<?php

namespace xSuper\OqexPractice\ffa;

use pocketmine\block\Air;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use pocketmine\world\World;
use xSuper\OqexPractice\duel\type\Type;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\utils\scoreboard\Scoreboards;

abstract class FFA
{
    /** @var self[] */
    private static array $arenas = [];

    public static function init(): void
    {
        self::register(Arenas::SUMO());
        self::register(Arenas::NO_DEBUFF());
        //self::register(Arenas::BUILD());
        self::register(Arenas::OITC());
        self::register(Arenas::BUHC());

        self::tick();
    }

    public static function register(self $ffa): void
    {
        self::$arenas[$ffa->getName()] = $ffa;
        if (Server::getInstance()->getWorldManager()->loadWorld($ffa->getMap())) {
            $w = Server::getInstance()->getWorldManager()->getWorldByName($ffa->getMap());
            $w->setTime(6000);
            $w->stopTime();
        }

        if ($ffa instanceof BUHCFFA) {
            OqexPractice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
                self::getArena('BuildUHC')->resetMap();
            }), 20 * 60 * 10 * 3);
        }
    }

    public static function getArena(string $name): ?self
    {
        return self::$arenas[$name] ?? null;
    }

    public static function tick(): void
    {
        OqexPractice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            foreach (self::$arenas as $arena) {
                $arena->update();
            }
        }), 20);
    }

    protected int $players = 0;
	/** @var list<string> */
    protected array $dead = [];

    public function getPlayers(): int
    {
        return $this->players;
    }

    public function update(): void
    {
        if ($this instanceof BUHCFFA && !$this->open) return;
		$world = Server::getInstance()->getWorldManager()->getWorldByName($this->getMap()) ?? throw new AssumptionFailedError("Map {$this->getMap()} should be loaded");
        foreach ($world->getPlayers() as $p) {
            /** @var PracticePlayer $p */
            Scoreboards::FFA()->send($p);
        }
    }

    public function reset(?PracticePlayer $killer, PracticePlayer $player): void
    {
        $this->doLeave($killer, $player);
        $player->removeCombatTag();
        $player->rmPearl();
        $player->rmArrow();

        if ($killer !== null) {
            $killer->removeCombatTag();
            $killer->rmPearl();
            $killer->rmArrow();
        }

        $player->teleport($this->getSpawn());
        $this->doJoin($player);
    }

    public function join(PracticePlayer $player): void
    {
        if ($this instanceof BUHCFFA && !$this->open) {
            $player->sendMessage('§r§cFFA teleport failed, try again?');
            return;
        }

        $spawn = $this->getSpawn();
        $x_f = (int) floor($spawn->x);
        $z_f = (int) floor($spawn->z);
        $spawn->getWorld()->orderChunkPopulation($x_f >> Chunk::COORD_BIT_SIZE, $z_f >> Chunk::COORD_BIT_SIZE, null)->onCompletion(function() use($spawn, $player) : void{
            $safe = $spawn->getWorld()->getSafeSpawn($spawn);
            $b = $safe->getWorld()->getBlock($safe);
            if ($b->getSide(Facing::DOWN) instanceof Air) {
                $this->join($player);
                return;
            }

            if ($b->hasEntityCollision() || $b->getSide(Facing::UP)->hasEntityCollision()) {
                $this->join($player);
                return;
            }

            $this->players++;

            $player->setFFA($this);
            $player->preTeleport($spawn->getWorld()->getSafeSpawn($spawn));
            Scoreboards::FFA()->send($player);

            foreach ($spawn->getWorld()->getPlayers() as $p) {
                $p->showPlayer($p);
                $player->showPlayer($p);
            }

            $this->doJoin($player);
        }, function () use ($player): void {
            $player->sendMessage('§r§cFFA teleport failed, try again?');
        });
    }

	/** @return array{'yKb': float, 'xzKb': float, 'maxHeight': int<0, max>, 'revert': bool} */
    public function getKB(): array
    {
		$yKb = Type::$config->getNested($this->getName() . '.kb.y', 0.394);
		if(!is_float($yKb)){
			throw new \TypeError('Expected float, got ' . gettype($yKb));
		}
		$xzKb = Type::$config->getNested($this->getName() . '.kb.xz', 0.394);
		if(!is_float($xzKb)){
			throw new \TypeError('Expected float, got ' . gettype($xzKb));
		}
		$maxHeight = Type::$config->getNested($this->getName() . '.kb.height', 3);
		if(!is_int($maxHeight)){
			throw new \TypeError('Expected int, got ' . gettype($maxHeight));
		}
		if($maxHeight <= 0){
			throw new \TypeError('Expected positive int, got ' . $maxHeight);
		}
		$revert = Type::$config->getNested($this->getName() . '.kb.revert', 0.75);
		return [
			'yKb' => $yKb,
			'xzKb' => $xzKb,
			'maxHeight' => $maxHeight,
			'revert' => $revert,
		];
    }

    public function getAttackCoolDown(): int
    {
		$cooldown = Type::$config->getNested($this->getName() . '.cooldown', 10);
		if(!is_int($cooldown)){
			throw new \TypeError('Expected int, got ' . gettype($cooldown));
		}
		return $cooldown;
    }

    public function leave(?PracticePlayer $killer, PracticePlayer $player): void
    {
        if (!in_array($player->getName(), $this->dead, true)) $this->players--;

        $this->doLeave($killer, $player);
    }

    public function subtractPlayer(): void
    {
        $this->players--;
    }

    protected static function getBestSpawn(World $world, array $positions): Vector3 {
        shuffle($positions);
        $ar = [];

        foreach ($positions as $k => $pos) {
            /** @var Vector3 $pos */
            $ar[$k] = count($world->getNearbyEntities(new AxisAlignedBB($pos[0] - 7, $pos[1] - 7, $pos[2] - 7, $pos[0] + 7, $pos[1] + 7, $pos[2] + 7)));
        }

        shuffle($ar);

        $lowest = 0;
        $prev = 100;
        foreach ($ar as $key => $count) {
            if ($count < $prev) {
                $prev = $count;
                $lowest = $key;
            }
        }

        $p = $positions[$lowest] ?? [0, 0, 0];

        return new Vector3($p[0], $p[1], $p[2]);
    }

    abstract public function getName(): string;
    abstract public function getHorizontalKnockBack(): float;
    abstract public function getVerticalKnockBack(): float;
    abstract public function getMenuItem(): Item;
    abstract public function getMap(): string;
    abstract public function fallDamage(): bool;
    abstract public function getSpawn(): Position;
    abstract public function doJoin(PracticePlayer $player): void;
    abstract public function doLeave(?PracticePlayer $killer, PracticePlayer $player): void;
}