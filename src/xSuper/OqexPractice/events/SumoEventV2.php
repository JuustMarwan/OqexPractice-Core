<?php

namespace xSuper\OqexPractice\events;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\GameMode;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\Position;
use Ramsey\Uuid\Uuid;
use xSuper\OqexPractice\duel\generator\MapGenerator;
use xSuper\OqexPractice\duel\type\Type;
use xSuper\OqexPractice\items\custom\InteractiveItems;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\utils\scoreboard\Scoreboards;

class SumoEventV2 extends Event
{
    private bool $open = false;

    /** @var PracticePlayer[] */
    private array $players = [];

    private ?EventLadder $ladder = null;

    private int $countdown = -1;

    public function max(): int
    {
        return 25;
    }

    public function min(): int
    {
        return 2;
    }

    public function hasStarted(): bool
    {
        return $this->ladder !== null;
    }

    public function getCountdown(): int
    {
        return $this->countdown;
    }

    public function join(PracticePlayer $player): void
    {
        if (!$this->open) {
            $player->sendMessage('§r§l§6EVENT §r§8» §7That event is no longer open!');
            return;
        }
        if (count($this->getRealPlayers()) >= $this->max()) {
            $player->sendMessage('§r§l§6EVENT §r§8» §7That event is currently full!');
            return;
        }

        $this->resetPlayer($player);
        if ($this->ladder !== null) $player->sendMessage('§r§l§6EVENT §r§8» §7You joined after the event started, and will be treated as a spectator!');

        $this->players[$player->getUniqueId()->toString()] = $player;
        $player->setEvent($this);
    }

    public function getType(): string
    {
        return 'Sumo';
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

            foreach($this->awaitingJoin as $p){
                $this->join($p);
            }

            OqexPractice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(): void{
                if($this->ended) throw new CancelTaskException();

                foreach($this->getRealPlayers() as $p){
                    Scoreboards::EVENT()->send($p);
                }
            }), 20);


            $this->countdown = 15;
            $lastPlayerCount = 0;
            OqexPractice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() use (&$lastPlayerCount): void{
                $players = count($this->getRealPlayers());

                if($this->ended) throw new CancelTaskException();
                if ($players <= 0) {
                    $this->end();
                    return;
                }

                if($players >= $this->min() && $this->autoStart){
                    if($this->countdown === -1) $this->countdown = 15;
                    if($players > $lastPlayerCount) $this->countdown = 15;

                    $lastPlayerCount = $players;

                    $this->countdown--;
                }else $this->countdown = -1;

                if($this->ended || $this->hasStarted()){
                    throw new CancelTaskException();
                }


                if($this->countdown === 0){
                    $this->start();
                    throw new CancelTaskException();
                }
            }), 20);
        }, $plugin->getDataFolder() . 'maps/' . $this->map->getRealName());
    }

    /** @return PracticePlayer[] */
    public function getRealPlayers(): array
    {
        $p = [];
        foreach ($this->players as $player) {
            if (!$player->isOnline()) $this->leave($player->getUniqueId()->toString());
            else $p[] = $player;
        }

        return $p;
    }

    public function getRandomSpawn(): Position
    {
        $data = $this->map->getData();

        $pos = $data[rand(0, 3)];
        return new Position($pos[0], $pos[1], $pos[2], Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName()));
    }

    public function getMap(): string
    {
        return 'SumoEvent';
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

        foreach ($this->getRealPlayers() as $player) {
            if ($p->getData()->getSettings()->asBool(SettingIDS::HIDE_PLAYERS_AT_EVENT)) $p->hidePlayer($player);
            else $p->showPlayer($player);

            if ($player->getData()->getSettings()->asBool(SettingIDS::HIDE_PLAYERS_AT_EVENT)) $player->hidePlayer($p);
            else $player->showPlayer($p);
        }

        $p->setCanBeDamaged(false);
        if ($p->getWorld()->getFolderName() !== $this->getWorldName()) $p->preTeleport($this->getRandomSpawn());
        else $p->teleport($this->getRandomSpawn());
        $p->getInventory()->setItem(8, InteractiveItems::LEAVE_EVENT()->getActualItem($p));
    }

    public function start(): void
    {
        $names = [];
        foreach ($this->getRealPlayers() as $p) $names[] = $p->getName();
        if ($this->ladder === null) $this->ladder = new EventLadder($names);
        $this->createFight();
    }

    public function createFight(): void
    {
        if($this->ladder === null){
            throw new AssumptionFailedError('Ladder should not be null at this point');
        }
        $r = $this->ladder->make();

        if (is_array($r)) {
            foreach ($this->getRealPlayers() as $p) {
                if ($p->getName() === $r[0]) {
                    $p->sendMessage('§r§l§6EVENT §r§8» §7Next fight in §b5 seconds§7: §aYou §7vs §c' . $r[1]);
                } else if ($p->getName() === $r[1]) {
                    $p->sendMessage('§r§l§6EVENT §r§8» §7Next fight in §b5 seconds§7: §c' . $r[0] . ' §7vs §aYou');
                } else {
                    $p->sendMessage('§r§l§6EVENT §r§8» §7Next fight in §b5 seconds§7: §c' . $r[0] . ' §7vs §c' . $r[1]);
                }
            }

            $oqexPractice = OqexPractice::getInstance();
            $server = $oqexPractice->getServer();
            $p1UuidString = $server->getPlayerExact($r[0])?->getUniqueId()->toString() ?? throw new AssumptionFailedError('Player 1 should not be offline');
            $p2UuidString = $server->getPlayerExact($r[1])?->getUniqueId()->toString() ?? throw new AssumptionFailedError('Player 2 should not be offline');
            $oqexPractice->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($r, $p1UuidString, $p2UuidString): void {
                $p1 = Server::getInstance()->getPlayerExact($r[0]);
                $p2 = Server::getInstance()->getPlayerExact($r[1]);

                if ($p1 instanceof PracticePlayer && $p1->isOnline()) {
                    if ($p2 instanceof PracticePlayer && $p2->isOnline()) {
                        foreach ($this->getRealPlayers() as $p) {
                            $p->showPlayer($p1);
                            $p->showPlayer($p2);
                        }

                        foreach ([$p1, $p2] as $i => $player) {
                            $player->extinguish();
                            $player->getInventory()->clearAll();
                            $player->getArmorInventory()->clearAll();
                            $player->getCursorInventory()->clearAll();
                            $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
                            $player->setHealth($player->getMaxHealth());
                            $player->getEffects()->clear();
                            $player->setAbsorption(0);
                            $player->setGamemode(GameMode::ADVENTURE());
                            $player->setNoClientPredictions();

                            if ($i === 0) $cords = $this->map->getSpawn1();
                            else $cords = $this->map->getSpawn2();

                            $pos = new Position($cords->getX(), $cords->getY(), $cords->getZ(), Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName()));
                            $player->teleport($pos);
                        }

                        $countdown = 6;
                        OqexPractice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($p1, $p2, &$countdown): void {
                            if ($countdown === 0) {
                                foreach ([$p1, $p2] as $i => $player) {
                                    if (!$player->isOnline()) {
                                        if ($i === 0) {
                                            $this->leave($p1->getUniqueId()->toString());
                                        } else {
                                            $this->leave($p2->getUniqueId()->toString());
                                        }

                                        $this->resetFight();
                                        throw new CancelTaskException();
                                    }

                                    $player->sendTitle('§r');
                                    $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 2147483647, 255, false, false));
                                    $player->setCanBeDamaged(true);
                                    $player->setNoClientPredictions(false);
                                }

                                $c = 0;
                                OqexPractice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use (& $c, $p1, $p2): void {
                                    if ($this->ended) {
                                        throw new CancelTaskException();
                                    }

                                    if ($c >= (60 * 3)) {
                                        $this->disqualify($p1);
                                        $this->disqualify($p2);
                                        $this->resetFight();
                                        throw new CancelTaskException();
                                    }

                                    $c++;

                                    foreach ([$p1, $p2] as $i => $player) {
                                        if (!$player->isOnline()) {
                                            if ($i === 0) {
                                                $this->leave($p1->getUniqueId()->toString());
                                            } else {
                                                $this->leave($p2->getUniqueId()->toString());
                                            }

                                            $this->resetFight();
                                            throw new CancelTaskException();
                                        }

                                        if ($player->getPosition()->getY() <= 64) {
                                            if ($i === 0) {
                                                $this->disqualify($p1->getName());
                                            } else {
                                                $this->disqualify($p2->getName());
                                            }

                                            $this->resetFight();
                                            throw new CancelTaskException();
                                        }
                                    }
                                }), 5);

                                throw new CancelTaskException();
                            }

                            if ($this->ended) {
                                throw new CancelTaskException();
                            }

                            foreach ([$p1, $p2] as $i => $player) {
                                if (!$player->isOnline()) {
                                    if ($i === 0) {
                                        $this->leave($p1->getUniqueId()->toString());
                                    } else {
                                        $this->leave($p2->getUniqueId()->toString());
                                    }

                                    $this->resetFight();
                                    throw new CancelTaskException();
                                }

                                $color = match ($countdown) {
                                    1 => '§r§l§4',
                                    2 => '§r§l§c',
                                    3 => '§r§l§6',
                                    4 => '§r§l§e',
                                    default => '§r§l§a'
                                };

                                $player->sendTitle($color . $countdown);
                                $player->sendSound('random.click');
                            }

                            $countdown--;
                        }), 20);
                    } else {
                        $this->leave($p2UuidString);
                        $this->resetFight();
                        throw new CancelTaskException();
                    }
                } else {
                    $this->leave($p1UuidString);
                    $this->resetFight();
                    throw new CancelTaskException();
                }
            }), 20 * 5);
        } else if ($r !== null) {
            foreach ($this->getRealPlayers() as $p) {
                $p->sendMessage('§r§l§6EVENT §r§8» §b'  . $r . ' §7has won the §aSumo Event');
            }

            $this->end();
        }
    }

    public function leave(string $uuid): void
    {
        if (($p = Server::getInstance()->getPlayerByUUID(Uuid::fromString($uuid))) instanceof PracticePlayer) $p->setEvent(null);

        if ($this->ladder !== null) $this->disqualify($this->players[$uuid]);
        unset($this->players[$uuid]);

        if (count($this->getRealPlayers()) === 0) $this->end();
    }

    public function resetFight(): void
    {
        foreach ($this->getRealPlayers() as $p) {
            $this->resetPlayer($p);
        }

        $this->createFight();
    }

    public function disqualify(PracticePlayer|string $player): void
    {
        if ($this->ladder === null) return;

        if (!is_string($player)) $p = $player->getName();
        else $p = $player;

        foreach ($this->getRealPlayers() as $pl) {
            $pl->sendMessage('§r§l§6EVENT §r§8» §b' . $p . ' §7has been disqualified!');
        }

        $this->ladder->removePlayer($player);
    }

    public function getAttackCoolDown(): int
    {
        $cooldown = Type::$config->getNested('Sumo.cooldown', 10);
        if(!is_int($cooldown)){
            throw new \TypeError('Expected int, got ' . gettype($cooldown));
        }
        return $cooldown;
    }

    /** @return array{'yKb': float, 'xzKb': float, 'maxHeight': int<0, max>, 'revert': bool} */
    public function getKB(): array
    {
        $yKb = Type::$config->getNested('Sumo.kb.y', 0.388);
        if(!is_float($yKb)){
            throw new \TypeError('Expected float, got ' . gettype($yKb));
        }
        $xzKb = Type::$config->getNested('Sumo.kb.xz', 0.401);
        if(!is_float($xzKb)){
            throw new \TypeError('Expected float, got ' . gettype($xzKb));
        }
        $maxHeight = Type::$config->getNested('Sumo.kb.height', 3);
        if(!is_int($maxHeight)){
            throw new \TypeError('Expected int, got ' . gettype($maxHeight));
        }
        if($maxHeight <= 0){
            throw new \TypeError('Expected positive int, got ' . $maxHeight);
        }
        $revert = Type::$config->getNested('Sumo.kb.revert', false);
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

    public function end(): void
    {
        $this->ended = true;
        foreach ($this->getRealPlayers() as $player) {
            if ($player->isOnline()) {
                $player->sendTitle('§r');
            }
        }

        $world = Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName()) ?? throw new AssumptionFailedError('Sumo world should not be unloaded');
        foreach ($world->getPlayers() as $player) {
            /** @var PracticePlayer $player */
            $player->reset(OqexPractice::getInstance());
        }

        OqexPractice::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
            MapGenerator::deleteMap($this->getWorldName());
            OqexPractice::getInstance()->getEventManager()->removeEvent();
        }), 20);
    }
}