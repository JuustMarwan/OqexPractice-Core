<?php

namespace xSuper\OqexPractice\duel\special;

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

class PartyDuel extends Duel
{
	/** @var list<Player> */
    private array $alive;

	/** @param list<PracticePlayer> $players */
    public function __construct(private string $party, string $id, Map $map, Type $type, array $players, bool $ranked = false)
    {
        $this->alive = $players;
        parent::__construct($id, $map, $type, $players, $ranked);
    }

    public function getParty(): ?Party
    {
        return Party::getParty($this->party);
    }

    public function killPlayer(PracticePlayer $player): void
    {
        $player->spectator();
        unset($this->alive[array_search($player, $this->alive, true)]);
    }

    public function end(PluginBase $plugin): void
    {
        $this->ended = true;

        $winner = $this->winner;

        Party::getParty($this->party)?->removeDuel();

        if ($winner !== null) {
            if ($winner->isOnline()) $winner->sendTitle('§r§l§aYou Won!');

            foreach ($this->players as $p) {
                if ($p->getUniqueId()->toString() !== $winner->getUniqueId()->toString() && $p->isOnline()) $p->sendTitle('§r§l§cYou Lost!');
                $p->spectator();
                $p->setNoClientPredictions(false);
            }

            $winner->extinguish();
            $winner->getHungerManager()->setFood($winner->getHungerManager()->getMaxFood());
            $winner->setHealth($winner->getMaxHealth());
            $winner->getEffects()->clear();
            $winner->setGamemode(GameMode::ADVENTURE());
            $winner->removeCombatTag();
            $winner->setCanBeDamaged(false);
            $winner->setNoClientPredictions(false);
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
                    $this->setWinner($player);
                    $this->end($plugin);
                    throw new CancelTaskException();
                }
            } else if (count($this->players) === 0) {
                $this->end($plugin);
                throw new CancelTaskException();
            }

            if (count($this->alive) === 1) {
                foreach ($this->alive as $player) {
					if(!$player instanceof PracticePlayer){
						throw new AssumptionFailedError('This should a pratice player instance');
					}
                    $this->setWinner($player);
                    $this->end($plugin);
                    throw new CancelTaskException();
                }
            } else if (count($this->alive) === 0) {
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
                            $this->setWinner($p);
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

				if($g === 1){
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