<?php

namespace xSuper\OqexPractice\ffa;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\world\Position;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;

class SumoFFA extends FFA
{
    public function getName(): string
    {
        return 'Sumo';
    }

    public function getHorizontalKnockBack(): float
    {
        return $this->getKB()['xzKb'];
    }

    public function getVerticalKnockBack(): float
    {
        return $this->getKB()['yKb'];
    }

    public function getMenuItem(): Item
    {
        return VanillaItems::SUSPICIOUS_STEW()->setCustomName($this->parseName([
            '§r§7You spawn on a small platform with another player',
            '§r§7where you have to knock them off the platform to',
            '§r§7win the fight.',
            '§r',
            ' §r§8- §r§7Playing: §e' . FFA::getArena('Sumo')->getPlayers(),
            ' §r',
            '§r§l§aClick §r§7to join.'
        ]));
    }

	/** @param list<string> $data */
    public function parseName(array $data): string
    {
        $s = '§r§l§6Sumo';
        foreach ($data as $line) {
            $s .= "\n" . $line;
        }
        return $s;
    }

    public function getMap(): string
    {
        return 'SumoFFA';
    }

    public function fallDamage(): bool
    {
        return false;
    }

    public function getSpawn(): Position
    {
        $spawns = [[235, 63, 268], [228, 63, 245]];

        $world = Server::getInstance()->getWorldManager()->getWorldByName($this->getMap());

        $vec = self::getBestSpawn($world, $spawns);


        return new Position($vec->x, $vec->y, $vec->z, Server::getInstance()->getWorldManager()->getWorldByName($this->getMap()));
    }

    public function doJoin(PracticePlayer $player): void
    {
        $player->extinguish();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getOffHandInventory()->clear(0);
        $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
        $player->setHealth($player->getMaxHealth());
        $player->getEffects()->clear();
        $player->setAbsorption(0);
        $player->setGamemode(GameMode::ADVENTURE());

        $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 2147483647, 255, false, false));
        $player->setCanBeDamaged(true);
    }

    public function doLeave(?PracticePlayer $killer, PracticePlayer $player): void
    {
        if (in_array($player->getName(), $this->dead, true)) return;

        $this->dead[] = $player->getName();

        if ($killer !== null) {
            $player->addDeath();
            $killer->addKill();
            foreach ($this->getSpawn()->getWorld()->getPlayers() as $p) {
                if ($p instanceof PracticePlayer && $p->isLoaded() && $p->getData()->getSettings()->asBool(SettingIDS::KILL_MESSAGE)) $p->sendMessage('§r§l§bSUMO §r§8» §r§7' . $player->getName() . ' was knocked off the edge by ' . $killer->getName());
            }
        } else {
            foreach ($this->getSpawn()->getWorld()->getPlayers() as $p) {
                if ($p instanceof PracticePlayer && $p->isLoaded() && $p->getData()->getSettings()->asBool(SettingIDS::KILL_MESSAGE)) $p->sendMessage('§r§l§bSUMO §r§8» §r§7' . $player->getName() . ' fell off the edge');
            }
        }

        OqexPractice::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
            unset($this->dead[array_search($player->getName(), $this->dead, true)]);
        }), 3);
    }
}