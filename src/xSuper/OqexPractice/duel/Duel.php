<?php

namespace xSuper\OqexPractice\duel;

use pocketmine\player\GameMode;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\world\Position;
use Ramsey\Uuid\Uuid;
use xSuper\OqexPractice\bot\BotType;
use xSuper\OqexPractice\duel\generator\MapGenerator;
use xSuper\OqexPractice\duel\generator\maps\Map;
use xSuper\OqexPractice\duel\special\BotDuel;
use xSuper\OqexPractice\duel\special\PartyDuel;
use xSuper\OqexPractice\duel\special\PartyScrimDuel;
use xSuper\OqexPractice\duel\special\SumoBotDuel;
use xSuper\OqexPractice\duel\special\SumoDuel;
use xSuper\OqexPractice\duel\special\SurvivalGamesDuel;
use xSuper\OqexPractice\duel\special\TheBridgeDuel;
use xSuper\OqexPractice\duel\type\SumoType;
use xSuper\OqexPractice\duel\type\SurvivalGamesType;
use xSuper\OqexPractice\duel\type\TheBridgeType;
use xSuper\OqexPractice\duel\type\Type;
use xSuper\OqexPractice\duel\utils\Elo;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\utils\scoreboard\Scoreboard;
use xSuper\OqexPractice\utils\scoreboard\Scoreboards;

class Duel
{
    protected const MAX_TIME = 10 * 60;

    /** @var self[] */
    private static array $duels = [];

	/** @param list<PracticePlayer> $players */
    public static function createDuel(PluginBase $plugin, Type $type, array $players, bool $ranked, ?Map $map = null): void
    {
        $id = Uuid::uuid4()->toString();
        while (isset(self::$duels[$id])) $id = Uuid::uuid4()->toString();

        if ($map === null) {
            $maps = Map::getMapsByType(Map::translateType($type));
            $map = $maps[rand(0, count($maps) - 1)];
        }

        if ($type instanceof SumoType) $duel = new SumoDuel($id, $map, $type, $players, $ranked);
        else if ($type instanceof SurvivalGamesType) $duel = new SurvivalGamesDuel($id, $map, $type, $players, $ranked);
        else if ($type instanceof TheBridgeType) $duel = new TheBridgeDuel($id, $map, $type, $players, $ranked);
        else $duel = new self($id, $map, $type, $players, $ranked);
        self::$duels[$id] = $duel;

        $duel->init($plugin);
    }

    public static function createBotDuel(PluginBase $plugin, PracticePlayer $player, Type $type, BotType $botType): void
    {
        $id = Uuid::uuid4()->toString();
        while (isset(self::$duels[$id])) $id = Uuid::uuid4()->toString();

        $maps = Map::getMapsByType(Map::translateType($type));
        $map = $maps[rand(0, count($maps) - 1)];

        if ($type instanceof SumoType) $duel = new SumoBotDuel($id, $map, $player, $type, $botType);
        else $duel = new BotDuel($id, $map, $player, $type, $botType);

        self::$duels[$id] = $duel;

        $duel->init($plugin);
    }

	/** @param list<PracticePlayer> $players */
    public static function createPartyDuel(PluginBase $plugin, Party $party, Type $type, array $players, ?Map $map): PartyDuel
    {
        $id = Uuid::uuid4()->toString();
        while (isset(self::$duels[$id])) $id = Uuid::uuid4()->toString();

        if ($map === null) {
            $maps = Map::getMapsByType(Map::translateType($type));
            $map = $maps[rand(0, count($maps) - 1)];
        }

        $duel = new PartyDuel($party->getId(), $id, $map, $type, $players);
        self::$duels[$id] = $duel;

        $duel->init($plugin);

        return $duel;
    }

	/**
	 * @param list<PracticePlayer> $team1
	 * @param list<PracticePlayer> $team2
	 */
    public static function createScrim(PluginBase $plugin, Party $party, Type $type, array $team1, array $team2, ?Map $map): PartyScrimDuel
    {
        $id = Uuid::uuid4()->toString();
        while (isset(self::$duels[$id])) $id = Uuid::uuid4()->toString();

        $players = array_merge($team1, $team2);

        if ($map === null) {
            $maps = Map::getMapsByType(Map::translateType($type));
            $map = $maps[rand(0, count($maps) - 1)];
        }

        $duel = new PartyScrimDuel($party->getId(), $id, $map, $type, $players, $team1, $team2);
        self::$duels[$id] = $duel;

        $duel->init($plugin);

        return $duel;
    }

    public static function removeDuel(self $duel): void
    {
        MapGenerator::deleteMap($duel->getWorldName());
        unset(self::$duels[$duel->getId()]);
    }

    public static function getDuel(string $id): ?Duel
    {
        return self::$duels[$id] ?? null;
    }

	/** @return array<string, Duel> */
    public static function getDuels(): array
    {
        return self::$duels;
    }

	/** @return list<Duel> */
    public static function getDuelsByType(Type $type, bool $ranked = false): array
    {
        $array = [];
        foreach (self::$duels as $duel) {
            if ($duel->getType()->getName() === $type->getName()) {
                if ($ranked) {
                    if ($duel->isRanked()) $array[] = $duel;
                } else if (!$duel->isRanked()) $array[] = $duel;
            }
        }

        return $array;
    }


    protected bool $ended = false;
    protected bool $started = false;
    protected ?PracticePlayer $winner = null;
    protected int $time = 0;

    /** @var list<Position> */
    protected array $placed = [];

    /** @var list<PracticePlayer> */
    protected array $players = [];

	/** @param list<PracticePlayer> $players */
    public function __construct(protected string $id, protected Map $map, protected Type $type, array $players, protected bool $ranked = false)
    {
        $this->players = $players;
    }

    public function addPlaced(Position $pos): void
    {
        $this->placed[$pos->x . $pos->y . $pos->z] = $pos;
    }

    public function getTime(): string
    {
        return gmdate('i:s', $this->time);
    }

    public function removePlaced(Position $pos): void
    {
        unset($this->placed[$pos->x . $pos->y . $pos->z]);
    }

    public function isPlaced(Position $pos): bool
    {
        return isset($this->placed[$pos->x . $pos->y . $pos->z]);
    }

    public function init(PluginBase $plugin): void
    {
        $map = $this->map;

        MapGenerator::genMap($plugin, $plugin->getServer()->getDataPath(), $this->getWorldName(), $plugin->getServer()->getDataPath() . '/worlds/' . $this->getWorldName(), function() use ($plugin, $map): void{
			if(!$plugin->getServer()->getWorldManager()->loadWorld($this->getWorldName())){
				foreach($this->getPlayers() as $player){
					$player->sendMessage('§r§cYour duel failed to generate a map, if you believe this is an error please contact a staff member!');
				}

				$this->end($plugin);
				return;
			}

			$world = $plugin->getServer()->getWorldManager()->getWorldByName($this->getWorldName());
            $world->setTime(6000);
            $world->stopTime();

			foreach($this->getRealPlayers() as $i => $player){
				$player->setDuel($this);
				$player->extinguish();
				$player->showPlayer($this->opposite($player));
				$player->getInventory()->clearAll();
				$player->getArmorInventory()->clearAll();
				$player->getCursorInventory()->clearAll();
				$player->getOffHandInventory()->clear(0);
				$player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
				$player->setHealth($player->getMaxHealth());
				$player->getEffects()->clear();
				$player->setAbsorption(0);
				$player->setGamemode(GameMode::SURVIVAL());
				Scoreboards::DUEL()->send($player);
				if($this->ranked) $player->subtractRankedGame();

				if($i === 0) $cords = $map->getSpawn1();
				else $cords = $map->getSpawn2();

				$pos = new Position($cords->getX(), $cords->getY(), $cords->getZ(), $world);
				$player->preTeleport($pos);
			}

			$this->countdown($plugin);

		}, $plugin->getDataFolder() . 'maps/' . $map->getRealName());
    }

    public function getRealPlayers(): array
    {
        $ps = [];
        foreach ($this->players as $i => $n) {
            if (is_string($n)) {
                $p = Server::getInstance()->getPlayerExact($n);
                if (!$p instanceof PracticePlayer) $p = Server::getInstance()->getPlayerByUUID(Uuid::fromString($n));


                if ($p instanceof PracticePlayer) {
                    $this->players[$i] = $p;
                }
            } else $p = $n;

            if ($p instanceof PracticePlayer && $p->isLoaded() && $p->isOnline()) $ps[] = $p;
        }

        return $ps;
    }

    public function countdown(PluginBase $plugin): void
    {
        $countDown = 6;
        $plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use (& $countDown, $plugin): void {
            if ($countDown === 0) {
                foreach ($this->getPlayers() as $player) {
                    if ($player->isOnline()) $player->sendTitle('§r');
                }

                $this->start($plugin);
                throw new CancelTaskException();
            }

            if ($this->ended) {
                throw new CancelTaskException();
            }

            foreach ($this->getPlayers() as $player) {
                if (!$player->isOnline()) {
                    $this->end($plugin);
                    throw new CancelTaskException();
                }

                $color = match ($countDown) {
                    1 => '§r§l§4',
                    2 => '§r§l§c',
                    3 => '§r§l§6',
                    4 => '§r§l§e',
                    default => '§r§l§a'
                };

                $player->sendTitle($color . $countDown);
                $player->sendSound('random.click');
            }

            $countDown--;
        }), 20);
    }

    public function start(PluginBase $plugin): void
    {
        $this->started = true;

        foreach ($this->getPlayers() as $player) {
            $player->giveKit($this->type->getKit());
            $player->setCanBeDamaged(true);
        }

        $plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($plugin): void {
            if ($this->time >= self::MAX_TIME) {
                $this->end($plugin);
                throw new CancelTaskException();
            }

            if ($this->ended) {
                throw new CancelTaskException();
            }

            $e = false;

            foreach ($this->getPlayers() as $p) {
                if (!$p->isOnline() && $this->winner === null) {
                    $e = true;
                    $oP = $this->opposite($p);
                    if ($oP->isOnline()) $this->winner = $oP;
                } else {
                    Scoreboards::DUEL()->send($p);
                }
            }


            if ($e) {
                $this->end($plugin);
                throw new CancelTaskException();
            }

            $this->time++;
        }), 20);
    }

    public function isEnded(): bool
    {
        return $this->ended;
    }

    public function end(PluginBase $plugin): void
    {
        $this->ended = true;

        $winner = $this->winner;

        if ($winner !== null) {
            $winner->addKill();
            if ($winner->isOnline()) $winner->sendTitle('§r§l§aYou Won!');
            $looser = $this->opposite($winner);
            $looser->addDeath();
            if ($looser->isOnline()) $looser->sendTitle('§r§l§cYou Lost!');

            if ($this->ranked) {
                    [$wE, $wG] = $winner->getData()->getEloAndGames($this->type->getName());
                    [$lE, $lG] = $looser->getData()->getEloAndGames($this->type->getName());

                    $elo = new Elo($wE, $lE, 1, 0, $wG, $lG);

                    $elo = $elo->getNewRatings();
                    $newW = (int) round($elo['a'], 0, PHP_ROUND_HALF_UP);
                    $won = $newW - $wE;
                    $newL = (int) round($elo['b'], 0, PHP_ROUND_HALF_DOWN);
                    $lost = $lE - $newL;
                    $winner->setElo($this->type, $newW);
                    $looser->setElo($this->type, $newL);

                    if ($winner->isOnline()) $winner->sendMessage('§r§7You§a won §7the ranked match against §b' . $looser->getName() .  '§7 and gained §a' . $won . ' §7elo!');
                    if ($looser->isOnline()) $looser->sendMessage('§r§7You§c lost §7the ranked match against §b' . $winner->getName() . '§7 and lost §c' . $lost . ' §7elo!');
            } else {
                if ($winner->isOnline()) $winner->sendMessage('§r§7You§a won §7the unranked match against §b' . $looser->getName() . '§7!');
                if ($looser->isOnline()) $looser->sendMessage('§r§7You§c lost §7the unranked match against §b' . $winner->getName() . '§7!');
            }

            $winner->extinguish();
            $winner->getHungerManager()->setFood($winner->getHungerManager()->getMaxFood());
            $winner->setHealth($winner->getMaxHealth());
            $winner->getEffects()->clear();
            $winner->setGamemode(GameMode::ADVENTURE());
            $winner->removeCombatTag();
            $winner->setCanBeDamaged(false);
            $winner->setNoClientPredictions(false);
            $looser->spectator();
            $looser->setNoClientPredictions(false);
        } else {
            foreach ($this->getPlayers() as $player) {
                if ($player->isOnline()) {
                    $player->spectator();
                    $player->setNoClientPredictions(false);
                }
            }
        }

        $plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($plugin): void {
            foreach ($this->getPlayers() as $player) {
                if ($player->isOnline()) {
                    $player->sendTitle('§l');
                }
            }

            foreach ((Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName())?->getPlayers() ?? []) as $player) {
                /** @var PracticePlayer $player */
                $player->reset($plugin);
            }

            self::removeDuel($this);
            Scoreboard::updateScoreBoards(Scoreboards::LOBBY());
        }), 6 * 20);
    }

    public function setWinner(PracticePlayer $player): void
    {
        $this->winner = $player;
    }

    public function opposite(PracticePlayer $player): PracticePlayer
    {
        foreach ($this->getRealPlayers() as $p) {
            if ($player->getName() !== $p->getName()) return $p;
        }

        return $player;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    protected function getWorldName(): string
    {
        return 'game_' . $this->id;
    }

    public function isStarted(): bool
    {
        return $this->started;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /** @return list<PracticePlayer> */
    public function getPlayers(): array
    {
        return $this->getRealPlayers();
    }

    public function isRanked(): bool
    {
        return $this->ranked;
    }

    public function getMap(): Map
    {
        return $this->map;
    }
}