<?php

namespace xSuper\OqexPractice\duel\special;

use pocketmine\player\GameMode;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\Position;
use xSuper\OqexPractice\duel\Duel;
use xSuper\OqexPractice\duel\generator\MapGenerator;
use xSuper\OqexPractice\duel\generator\maps\SumoMap;
use xSuper\OqexPractice\utils\scoreboard\Scoreboards;

class SumoDuel extends Duel
{
    public function init(PluginBase $plugin): void
    {
        /** @var SumoMap $map */
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

			foreach($this->getPlayers() as $i => $player){
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
				$player->setGamemode(GameMode::ADVENTURE());
				$player->setNoClientPredictions();
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

    public function start(PluginBase $plugin): void
    {
        $this->started = true;

        $map = $this->map;
        /** @var SumoMap $map */
        $min = $map->getMin();

        foreach ($this->getPlayers() as $player) {
            $player->giveKit($this->type->getKit());
            $player->setCanBeDamaged(true);
            $player->setNoClientPredictions(false);
        }

        $plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($plugin, $min): void {
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
                } else if ($p->getPosition()->getY() <= $min && $this->winner === null) {
                    $e = true;
                    $oP = $this->opposite($p);
                    if ($oP->isOnline()) $this->winner = $oP;
                }

                Scoreboards::DUEL()->send($p);
            }


            if ($e) {
                $this->end($plugin);
                throw new CancelTaskException();
            }

            $this->time++;
        }), 20);
    }
}