<?php

namespace xSuper\OqexPractice\events;

use pocketmine\player\GameMode;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\Position;
use xSuper\OqexPractice\duel\generator\MapGenerator;
use xSuper\OqexPractice\duel\type\Type;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;

class LastManStandingEvent extends Event
{
    public const MAX_PLAYERS = 25;
    public const MIN_PLAYERS = 2;

    private bool $open = false;

    private bool $hasStarted = false;

    /**
     * @var PracticePlayer[]
     * @phpstan-var array<int, PracticePlayer>
     */
    private array $players = [];
	/** @var array<int, PracticePlayer> */
    private array $alive = [];

    public function join(PracticePlayer $player): void
    {
        if (!$this->open) {
            $player->sendMessage('§r§l§6EVENT §r§8» §7That event is no longer open!');
            return;
        }
        if (count($this->getRealPlayers()) >= self::MAX_PLAYERS) {
            $player->sendMessage('§r§l§6EVENT §r§8» §7That event is currently full!');
            return;
        }

        $this->resetPlayer($player);
        if ($this->hasStarted) {
            $player->sendMessage('§r§l§6EVENT §r§8» §7You joined after the event started, and will be treated as a spectator!');
            $player->spectator();
        } else $this->alive[$player->getId()] = $player;

        $this->players[$player->getId()] = $player;
        $player->setEvent($this);
    }

    public function spectator(PracticePlayer $player): void
    {
        //TODO
    }

    public function getWorldName(): string
    {
        return 'event_current';
    }

    public function init(): void
    {
        $plugin = OqexPractice::getInstance();
        MapGenerator::genMap($plugin, $plugin->getServer()->getDataPath(), $this->getWorldName(), $plugin->getServer()->getDataPath() . '/worlds/' . $this->getWorldName(), function() use ($plugin): void{
			if(!$plugin->getServer()->getWorldManager()->loadWorld($this->getWorldName())){
				$this->end();
				return;
			}

			$this->open = true;

			$countdown = 15;
			OqexPractice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() use (&$countdown): void{
				if(count($this->players) >= self::MIN_PLAYERS){
					$countdown--;
				}else $countdown = 15;

				if($this->ended){
					throw new CancelTaskException();
				}

				if($countdown < 15){
					if($countdown !== 0){
						foreach($this->getRealPlayers() as $p){
							$p->sendTitle('§r§l§aStarting in ' . $countdown . 's...');
						}
					}else{
						foreach($this->getRealPlayers() as $p){
							$p->sendTitle('§r');
						}

						$this->start();
						throw new CancelTaskException();
					}
				}else{
					foreach($this->getRealPlayers() as $p){
						$p->sendTitle('§r');
					}
				}
			}), 20);
		}, $plugin->getDataFolder() . 'maps/' . $this->map->getRealName());
    }

    /** @return PracticePlayer[] */
    public function getRealPlayers(): array
    {
        $p = [];
        foreach ($this->players as $player) {
            if (!$player->isOnline()) $this->leave($player->getName());
            else $p[] = $player;
        }

        return $p;
    }

    public function getRandomSpawn(): Position
    {
        $data = $this->map->getData();

        $pos = $data[rand(0, 3)]; //TODO: How many?
        return new Position($pos[0], $pos[1], $pos[2], Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName()));
    }

    public function getType(): string
    {
        return 'LastManStanding';
    }

    public function start(): void
    {
        $this->hasStarted = true;

        foreach ($this->players as $player){
            $player->getInventory()->setContents([
                //TODO
            ]);
            $player->getArmorInventory()->setContents([
                //TODO
            ]);
        }
    }

    public function getMap(): string
    {
        return 'LastManStandingEvent';
    }

    public function resetPlayer(PracticePlayer $p): void
    {
        $p->extinguish();
        $p->getInventory()->clearAll();
        $p->getArmorInventory()->clearAll();
        $p->getCursorInventory()->clearAll();
        $p->getHungerManager()->setFood($p->getHungerManager()->getMaxFood());
        $p->setHealth($p->getMaxHealth());
        $p->getEffects()->clear();
        $p->setAbsorption(0);
        $p->setGamemode(GameMode::ADVENTURE());
        $p->removeCombatTag();

        $p->setCanBeDamaged(false);
        $p->teleport($this->getRandomSpawn());
    }

    public function getAttackCoolDown(): int
    {
		$cooldown = Type::$config->getNested('LastManStanding.cooldown', 10);
		if(!is_int($cooldown)){
			throw new \TypeError('Expected int, got ' . gettype($cooldown));
		}
		return $cooldown;
    }

	/** @return array{'yKb': float, 'xzKb': float, 'maxHeight': int<0, max>, 'revert': bool} */
    public function getKB(): array
    {
		$yKb = Type::$config->getNested('LastManStanding.kb.y', 0.4); //TODO: Default for now
		if(!is_float($yKb)){
			throw new \TypeError('Expected float, got ' . gettype($yKb));
		}
		$xzKb = Type::$config->getNested('LastManStanding.kb.xz', 0.4); //TODO: Default for now
		if(!is_float($xzKb)){
			throw new \TypeError('Expected float, got ' . gettype($xzKb));
		}
		$maxHeight = Type::$config->getNested('LastManStanding.kb.height', 3);
		if(!is_int($maxHeight)){
			throw new \TypeError('Expected int, got ' . gettype($maxHeight));
		}
		if($maxHeight <= 0){
			throw new \TypeError('Expected positive int, got ' . $maxHeight);
		}
		$revert = Type::$config->getNested('LastManStanding.kb.revert', false);
		if(!is_bool($revert)){
			throw new \TypeError('Expected an integer, got ' . gettype($revert));
		}
		return [
			'yKb' => $yKb,
			'xzKb' => $xzKb,
			'maxHeight' => $maxHeight,
			'revert' => $revert,
		];
    }

    public function disqualify(PracticePlayer|string $player): void
    {
        if (!is_string($player)){
            $p = $player->getName();
            $playerInstance = $player;
        }
        else{
            $p = $player;
            $playerInstance = Server::getInstance()->getPlayerExact($player);
			if(!$playerInstance instanceof PracticePlayer){
				throw new AssumptionFailedError('$playerInstance should be a PracticePlayer');
			}
        }

        foreach ($this->getRealPlayers() as $pl) {
            $pl->sendMessage('§r§l§6EVENT §r§8» §b' . $p . ' §7has been disqualified!');
        }
        unset($this->alive[$playerInstance->getId()]);
        $playerInstance->spectator();
        if (count($this->alive) <= 1) {
            $this->end();
        }
    }

    public function leave(string $player): void
    {
		$p = Server::getInstance()->getPlayerExact($player);
        if (!$p instanceof PracticePlayer){
			throw new AssumptionFailedError('Player should not be offline at this point');
		}
		$p->setEvent(null);
        unset($this->players[$p->getId()]);
        $this->disqualify($player);
    }

    public function end(): void
    {
        $winner = $this->alive[0];
        foreach ($this->getRealPlayers() as $player) {
            if ($player->isOnline()) {
                $player->sendMessage('§r§l§6EVENT §r§8» §b'  . $winner->getName() . ' §r§7has won the event!');
            }
        }
        foreach ($this->getRealPlayers() as $player) {
            if ($player->isOnline()) {
                $player->sendTitle('§r');
            }
        }

		$world = Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName()) ?? throw new AssumptionFailedError('Last man standing world should not be unloaded');
		foreach ($world->getPlayers() as $player) {
            /** @var PracticePlayer $player */
            $player->reset(OqexPractice::getInstance());
        }

        MapGenerator::deleteMap($this->getWorldName());
        // TODO: Delete event from event manager
    }
}