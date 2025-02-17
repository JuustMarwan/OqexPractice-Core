<?php

namespace xSuper\OqexPractice\ffa;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\world\Position;
use xSuper\OqexPractice\duel\generator\MapGenerator;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\utils\ItemUtils;

class BUHCFFA extends FFA
{
    private array $placed = [];

    public function addPlaced(Position $pos): void
    {
        $this->placed[$pos->x . $pos->y . $pos->z] = true;
    }

    public function removePlaced(Position $pos): void
    {
        unset($this->placed[$pos->x . $pos->y . $pos->z]);
    }

    public function isPlaced(Position $pos): bool
    {
        return isset($this->placed[$pos->x . $pos->y . $pos->z]);
    }

    protected bool $open = false;

    public function resetMap(): void
    {
        $pl = OqexPractice::getInstance();

        $this->open = false;
        $worldManager = Server::getInstance()->getWorldManager();
        $world = $worldManager->getWorldByName($this->getMap());
        $players = [];
        if ($world !== null) {
            /** @var PracticePlayer $player */
            foreach ($world->getPlayers() as $player) {
                $this->players--;
                $player->reset(OqexPractice::getInstance());
                $player->sendMessage('§r§l§bBUHC §r§8» §r§7The map is resetting...');
                $players[] = $player->getUniqueId();
            }

            MapGenerator::deleteMap($this->getMap());
        }
        MapGenerator::genMap(OqexPractice::getInstance(), $pl->getServer()->getDataPath(), $this->getMap(), $pl->getServer()->getDataPath() . '/worlds/' . $this->getMap(), function () use ($worldManager, $players): void {
            if (!$worldManager->loadWorld($this->getMap())) {
                return;
            }

            $world = $worldManager->getWorldByName($this->getMap());
            $world->setTime(6000);
            $world->stopTime();

            $this->open = true;

            $server = Server::getInstance();
            foreach ($players as $uuid) {
                $p = $server->getPlayerByUUID($uuid);
                if ($p instanceof PracticePlayer && $p->isOnline() && $p->isLoaded()) {
                    $this->join($p);
                }
            }
        }, $pl->getDataFolder() . 'maps/' . $this->getMap());
    }

    public function getName(): string
    {
        return 'BuildUHC';
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
        return VanillaItems::FISHING_ROD()->setCustomName($this->parseName([
            '§r§7Dominate your opponents by either hitting them off',
            '§r§7the map, or besting them in combat. Use your blocks',
            '§r§7and pearls to clutch up.',
            '§r',
            ' §r§8- §r§7Playing: §e' . FFA::getArena('BuildUHC')->getPlayers(),
            ' §r',
            '§r§l§aClick §r§7to join.'
        ]));
    }

    /** @param list<string> $data */
    public function parseName(array $data): string
    {
        $s = '§r§l§6BuildUHC';
        foreach ($data as $line) {
            $s .= "\n" . $line;
        }
        return $s;
    }

    public function getMap(): string
    {
        return 'BUHCFFA';
    }

    public function fallDamage(): bool
    {
        return false;
    }

    public function getSpawn(): Position
    {
        $center = [498, 93, 341];


        $x = mt_rand($center[0] - 50, $center[0] + 50);
        $y = $center[1];
        $z = mt_rand($center[2] - 50, $center[2] + 50);


        return new Position($x, $y, $z, Server::getInstance()->getWorldManager()->getWorldByName($this->getMap()));
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
        $player->setGamemode(GameMode::SURVIVAL());
        $player->giveKit('BuildUHC', true);

        $player->setCanBeDamaged(true);
    }

    public function doLeave(?PracticePlayer $killer, PracticePlayer $player): void
    {
        if (in_array($player->getName(), $this->dead, true)) return;

        $this->dead[] = $player->getName();

        if ($killer !== null) {
            $killer->addKill();
            $killer->setHealth($killer->getMaxHealth());
            $player->giveKit('BuildUHC', true);
            foreach ($this->getSpawn()->getWorld()->getPlayers() as $p) {
                if ($p instanceof PracticePlayer && $p->isLoaded() && $p->getData()->getSettings()->asBool(SettingIDS::KILL_MESSAGE)) $p->sendMessage('§r§l§bBUHC §r§8» §r§7' . $player->getName() . ' was knocked off the edge by ' . $killer->getName());
            }
        } else {
            foreach ($this->getSpawn()->getWorld()->getPlayers() as $p) {
                if ($p instanceof PracticePlayer && $p->isLoaded() && $p->getData()->getSettings()->asBool(SettingIDS::KILL_MESSAGE)) $p->sendMessage('§r§l§bBUHC §r§8» §r§7' . $player->getName() . ' fell off the edge');
            }
        }

        $player->addDeath();

        OqexPractice::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
            unset($this->dead[array_search($player->getName(), $this->dead, true)]);
        }), 3);
    }
}