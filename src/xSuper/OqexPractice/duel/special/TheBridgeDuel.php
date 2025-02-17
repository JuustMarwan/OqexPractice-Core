<?php

namespace xSuper\OqexPractice\duel\special;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\object\ItemEntity;
use pocketmine\player\GameMode;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\particle\FloatingTextParticle;
use pocketmine\world\Position;
use xSuper\OqexPractice\duel\Duel;
use xSuper\OqexPractice\duel\generator\MapGenerator;
use xSuper\OqexPractice\duel\generator\maps\BridgeMap;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\utils\scoreboard\Scoreboards;

class TheBridgeDuel extends Duel
{
    private array $scores = [];
    private bool $reset = false;

    // TODO: Colors + Center goal text
    public function init(PluginBase $plugin): void
    {
        /** @var BridgeMap $map */
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

			if(1 === $map->getRed()){
				$posR = $map->getPortal1()->add(0, 3, 0);
				$posB = $map->getPortal2()->add(0, 3, 0);
			}else{
				$posR = $map->getPortal2()->add(0, 3, 0);
				$posB = $map->getPortal1()->add(0, 3, 0);
			}

			$red = new FloatingTextParticle("§r§l§cRed Goal\n§r§7Enter to score");

			$blue = new FloatingTextParticle("§r§l§1Blue Goal\n§r§7Enter to score");

			foreach($this->getPlayers() as $i => $player){
                $this->scores[$player->getUniqueId()->toString()] = 0;
				$player->setDuel($this);
				$player->extinguish();
				$player->getInventory()->clearAll();
				$player->showPlayer($this->opposite($player));
                Scoreboards::BRIDGE()->send($player);
				$player->getArmorInventory()->clearAll();
				$player->getCursorInventory()->clearAll();
				$player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
				$player->setHealth($player->getMaxHealth());
				$player->getEffects()->clear();
				$player->setAbsorption(0);
				$player->setGamemode(GameMode::ADVENTURE());
				$player->setNoClientPredictions();
				if($this->ranked) $player->subtractRankedGame();

				if($i === 0) $cords = $map->getSpawn1();
				else $cords = $map->getSpawn2();

				$pos = new Position($cords->getX(), $cords->getY(), $cords->getZ(), $world);
				$player->preTeleport($pos);
			}

			$this->countdown($plugin);

		}, $plugin->getDataFolder() . 'maps/' . $map->getRealName());
    }

    public function score(PracticePlayer $scorer): void
    {
        if ($this->reset) return;

        $world = Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName()) ??
            throw new AssumptionFailedError('This should never happen');
        foreach ($world->getEntities() as $e) if ($e instanceof ItemEntity) $e->flagForDespawn();
        $this->scores[$scorer->getUniqueId()->toString()]++;
        $scorer->sendMessage('§r§a' . $scorer->getName() . ' §7scored!');
        $this->opposite($scorer)->sendMessage('§r§c' . $scorer->getName() . ' §7scored!');

        $this->reset = true;

        OqexPractice::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
            $this->reset = false;
        }), 40);

        foreach ($this->getPlayers() as $player) $this->resetPlayer($player, true);


        foreach ($this->placed as $cord) {
            $cord->getWorld()->setBlock($cord, VanillaBlocks::AIR());
        }
    }

    public function getScore(PracticePlayer $player): int {
        return $this->scores[$player->getUniqueId()->toString()] ?? 0;
    }

    public function getSpawn(PracticePlayer $player): Position
    {
        $world = Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName());
        /** @var BridgeMap $map */
        $map = $this->map;
        foreach ($this->getPlayers() as $i => $p) {
            if ($i === 0) $cords = $map->getSpawn1();
            else $cords =$map->getSpawn2();
            if ($player->getName() === $p->getName()) {
                return new Position($cords->getX(), $cords->getY(), $cords->getZ(), $world);
            }
        }

		throw new AssumptionFailedError('Unreachable');
    }

    public function resetPlayer(PracticePlayer $player, bool $scored = false): void
    {
        $player->extinguish();
        $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
        $player->setHealth($player->getMaxHealth());
        $player->getEffects()->clear();
        $player->setAbsorption(0);
        $player->giveKit($this->type->getKit());

        if ($scored) {
            $player->setNoClientPredictions();
            $player->setCanPlace(false);
            $countDown = 6;
            $plugin = OqexPractice::getInstance();
            OqexPractice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use (& $countDown, $plugin): void {
                if ($countDown === 0) {
                    foreach ($this->getPlayers() as $player) {
                        $player->sendTitle('§r');
                        $player->setNoClientPredictions(false);
                        $player->setCanPlace(true);
                    }

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

        $player->teleport($this->getSpawn($player));
    }

    public function start(PluginBase $plugin): void
    {
        $this->started = true;

        foreach ($this->getPlayers() as $player) {
            $player->giveKit($this->type->getKit());
            $player->setCanBeDamaged(true);
            $player->setNoClientPredictions(false);
            $player->setCanPlace(true);
            $player->setGamemode(GameMode::SURVIVAL());
        }

        /** @var BridgeMap $map */
        $map = $this->map;
        $min = $map->getSpawn1()->getY() - 10;

        $plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($plugin, $min): void {
            if ($this->time >= self::MAX_TIME) {
                $this->end($plugin);
                throw new CancelTaskException();
            }

            if ($this->ended) {
                throw new CancelTaskException();
            }

            foreach ($this->getPlayers() as $p) {
                if ($p->getPosition()->getY() <= $min) {
                    $this->resetPlayer($p);
                }

                Scoreboards::BRIDGE()->send($p);
            }

            $e = false;

            foreach ($this->getPlayers() as $player) {
                if (!$player->isOnline() && $this->winner === null) {
                    $e = true;
                    $oP = $this->opposite($player);
                    if ($oP->isOnline()) $this->winner = $oP;
                }

                if ($this->scores[$player->getUniqueId()->toString()] >= 3) {
                    $this->winner = $player;
                    $e = true;
                }
            }

            if ($e) {
                $this->end($plugin);
                throw new CancelTaskException();
            }

            $this->time++;
        }), 20);
    }

    public function end(PluginBase $plugin): void
    {
        $world = $plugin->getServer()->getWorldManager()->getWorldByName($this->getWorldName());
		if($world === null){
			return;
		}
        parent::end($plugin);
    }
}