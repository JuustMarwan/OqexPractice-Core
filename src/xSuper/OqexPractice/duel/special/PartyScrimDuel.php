<?php

namespace xSuper\OqexPractice\duel\special;

use pocketmine\item\SplashPotion;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\Position;
use xSuper\OqexPractice\duel\Duel;
use xSuper\OqexPractice\duel\generator\MapGenerator;
use xSuper\OqexPractice\duel\generator\maps\Map;
use xSuper\OqexPractice\duel\type\Type;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\utils\scoreboard\Scoreboard;
use xSuper\OqexPractice\utils\scoreboard\Scoreboards;

class PartyScrimDuel extends Duel
{
	/** @var list<Player> $alive1 */
    private array $alive1;
	/** @var list<Player> $alive2 */
    private array $alive2;
    private ?int $winningTeam = null;

	/**
	 * @param list<PracticePlayer> $players
	 * @param list<Player> $team1
	 * @param list<Player> $team2
	 */
    public function __construct(private string $party, string $id, Map $map, Type $type, array $players, private array $team1, private array $team2, bool $ranked = false)
    {
        $this->alive1 = $this->team1;
        $this->alive2 = $team2;
        parent::__construct($id, $map, $type, $players, $ranked);
    }

    public function getParty(): ?Party
    {
        return Party::getParty($this->party);
    }

    public function killPlayer(PracticePlayer $player): void
    {
        $pos = $player->getPosition();
        $world = $player->getWorld();
        foreach ($player->getInventory()->getContents(false) as $item) {
            if ($item instanceof SplashPotion) $world->dropItem($pos, $item);
        }
        $player->spectator();
        $team = $this->getTeam($player->getUniqueId()->toString());
        if ($team === 1) unset($this->alive1[array_search($player, $this->alive1, true)]);
        else unset($this->alive2[array_search($player, $this->alive2, true)]);
    }

    public function areTeamed(PracticePlayer $p1, PracticePlayer $p2): bool
    {
        $p1t = 1;
        $p2t = 1;
        foreach ($this->team2 as $p) {
            if ($p->getUniqueId()->toString() === $p1->getUniqueId()->toString()) $p1t = 2;
            if ($p->getUniqueId()->toString() === $p2->getUniqueId()->toString()) $p2t = 2;
        }

        return $p1t === $p2t;
    }

    public function getTeam(string $player): int
    {
        $team = 1;
        foreach ($this->team2 as $p) {
            if ($p->getUniqueId()->toString() === $player) $team = 2;
        }

        return $team;
    }

	/** @return list<Player> */
    public function oppositeTeam(int $team): array
    {
        if ($team === 1) return $this->team2;
        else return $this->team1;
    }

	/** @return list<Player> */
    public function team(int $team): array {
        if ($team === 1) return $this->team1;
        else return $this->team2;
    }

    public function end(PluginBase $plugin): void
    {
        $this->ended = true;

        $winner = $this->winningTeam;

        Party::getParty($this->party)?->removeDuel();

        if ($winner !== null) {
            foreach ($this->team($winner) as $p) {
				if(!$p instanceof PracticePlayer){
					throw new AssumptionFailedError('This should a pratice player instance');
				}
                if ($p->isOnline()) {
                    $p->sendTitle('§r§l§aYou Won!');
                    $p->extinguish();
                    $p->getHungerManager()->setFood($p->getHungerManager()->getMaxFood());
                    $p->setHealth($p->getMaxHealth());
                    $p->getEffects()->clear();
                    $p->setGamemode(GameMode::ADVENTURE());
                    $p->removeCombatTag();
                    $p->setCanBeDamaged(false);
                    $p->setNoClientPredictions(false);
                }
            }

            foreach ($this->oppositeTeam($winner) as $p) {
				if(!$p instanceof PracticePlayer){
					throw new AssumptionFailedError('This should a pratice player instance');
				}
                if ($p->isOnline()) $p->sendTitle('§r§l§cYou Lost!');
                $p->spectator();
                $p->setNoClientPredictions(false);
            }
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

            Scoreboard::updateScoreBoards(Scoreboards::LOBBY());
            self::removeDuel($this);
        }), 6 * 20);
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

            if (count($this->players) === 1) {
                foreach ($this->players as $player) {
                    $this->winningTeam  = $this->getTeam($player->getUniqueId()->toString());
                    $this->end($plugin);
                    throw new CancelTaskException();
                }
            } else if (count($this->players) === 0) {
                $this->end($plugin);
                throw new CancelTaskException();
            }

            if (count($this->alive1) === 0) {
                $this->winningTeam = 1;
                $this->end($plugin);
                throw new CancelTaskException();
            }

            if (count($this->alive2) === 0) {
                $this->winningTeam = 1;
                $this->end($plugin);
                throw new CancelTaskException();
            }

            foreach ($this->getPlayers() as $p) {

                // ScoreboardUtils::duelScoreboard($p);
            }

            $this->time++;
        }), 20);
    }

    public function countdown(PluginBase $plugin): void
    {
        $countDown = 6;
        $plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use (& $countDown, $plugin): void {
            if ($countDown === 0) {
                foreach ($this->getPlayers() as $player) {
                    $player->sendTitle('§r');
                }

                $this->start($plugin);
                throw new CancelTaskException();
            }

            if ($this->ended) {
                throw new CancelTaskException();
            }

            foreach ($this->getPlayers() as $player) {
                if (!$player->isOnline()) {
                    unset($this->players[array_search($player, $this->players, true)]);
                    if (count($this->players) === 1) {
                        foreach ($this->players as $p) {
                            $this->winningTeam = $this->getTeam($p->getUniqueId()->toString());
                            $this->end($plugin);
                            throw new CancelTaskException();
                        }
                    } else if (count($this->players) === 0) {
                        $this->end($plugin);
                        throw new CancelTaskException();
                    }
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
                // ScoreboardUtils::duelScoreboard($player);
            }

            $countDown--;
        }), 20);
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

			$g = 1;
			foreach($this->getPlayers() as $player){
				$player->setDuel($this);
				$player->extinguish();
				$player->getInventory()->clearAll();
				$player->showPlayer($this->opposite($player));
				$player->getArmorInventory()->clearAll();
				$player->getCursorInventory()->clearAll();
				$player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
				$player->setHealth($player->getMaxHealth());
				$player->getEffects()->clear();
				$player->setAbsorption(0);
				$player->setGamemode(GameMode::SURVIVAL());

				if($this->getTeam($player->getUniqueId()->toString()) === 1){
					$cords = $map->getSpawn1();
					$g++;
				}else{
					$cords = $map->getSpawn2();
					$g = 1;
				}

				$pos = new Position($cords->getX(), $cords->getY(), $cords->getZ(), $world);
				$player->preTeleport($pos);
			}

			$this->countdown($plugin);

		}, $plugin->getDataFolder() . 'maps/' . $map->getRealName());
    }
}