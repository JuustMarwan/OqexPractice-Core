<?php

namespace xSuper\OqexPractice\events;

use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
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
use xSuper\OqexPractice\utils\ItemUtils;
use xSuper\OqexPractice\utils\scoreboard\Scoreboards;

class JuggernautEventV2 extends Event
{
    private const WINNER_NONE = 0;
    private const WINNER_JUGGERNAUT = 1;
    private const WINNER_PLAYERS = 2;
    private bool $open = false;

    private bool $hasStarted = false;

    /**
     * @var PracticePlayer[]
     * @phpstan-var array<string, PracticePlayer>
     */
    private array $players = [];
    /** @var array<string, PracticePlayer> */
    private array $alive = [];
    private string $juggernaut;

    private int $countdown = -1;
    private int $spawnIndex = 0;

    public function max(): int
    {
        return 25;
    }

    public function min(): int
    {
        return 4;
    }

    public function hasStarted(): bool
    {
        return $this->hasStarted;
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
        if ($this->hasStarted) {
            $player->sendMessage('§r§l§6EVENT §r§8» §7You joined after the event started, and will be treated as a spectator!');
            $player->spectator();
        } else $this->alive[$player->getUniqueId()->toString()] = $player;

        $this->players[$player->getUniqueId()->toString()] = $player;
        $player->setEvent($this);
    }

    public function getWorldName(): string
    {
        return 'event_current';
    }

    public function getCountdown(): int
    {
        return $this->countdown;
    }

    public function init(): void
    {
        $plugin = OqexPractice::getInstance();
        MapGenerator::genMap($plugin, $plugin->getServer()->getDataPath(), $this->getWorldName(), $plugin->getServer()->getDataPath() . '/worlds/' . $this->getWorldName(), function() use ($plugin): void{
            if(!$plugin->getServer()->getWorldManager()->loadWorld($this->getWorldName())){
                $this->end(self::WINNER_NONE);
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

        $max = count($data) - 1;

        if ($this->spawnIndex > $max) $this->spawnIndex = 0;

        $pos = $data[$this->spawnIndex];
        $this->spawnIndex++;
        return new Position($pos[0], $pos[1], $pos[2], Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName()));
    }

    public function getType(): string
    {
        return 'Juggernaut';
    }

    public function isTeamed(Player $one, Player $two): bool {
        if ($one->getUniqueId()->toString() === $this->juggernaut || $two->getUniqueId()->toString() === $this->juggernaut) return false;
        return true;
    }

    public function start(): void
    {
        $this->hasStarted = true;

        $players = $this->getRealPlayers();
        $rand = rand(0, count($players) - 1);
        $c = 0;
        foreach ($this->getRealPlayers() as $p) {
            if ($c === $rand) {
                $this->juggernaut = $p->getUniqueId()->toString();
                break;
            }

            $c++;
        }

        $juggernaut = Server::getInstance()->getPlayerByUUID(Uuid::fromString($this->juggernaut));
        $juggernaut->setMaxHealth(60);
        $juggernaut->getInventory()->setContents([
            0 => ItemUtils::enchant(VanillaItems::GOLDEN_SWORD()->setUnbreakable(), [VanillaEnchantments::SHARPNESS()], [5]),
            1 => VanillaItems::GOLDEN_APPLE()->setCount(16)
        ]);
        $juggernaut->getArmorInventory()->setContents([
            0 => ItemUtils::enchant(VanillaItems::DIAMOND_HELMET()->setUnbreakable(), [VanillaEnchantments::PROTECTION()], [5]),
            1 => ItemUtils::enchant(VanillaItems::DIAMOND_CHESTPLATE()->setUnbreakable(), [VanillaEnchantments::PROTECTION()], [5]),
            2 => ItemUtils::enchant(VanillaItems::DIAMOND_LEGGINGS()->setUnbreakable(), [VanillaEnchantments::PROTECTION()], [5]),
            3 => ItemUtils::enchant(VanillaItems::DIAMOND_BOOTS()->setUnbreakable(), [VanillaEnchantments::PROTECTION()], [5]),
        ]);
        $juggernaut->setCanBeDamaged(true);
        $juggernaut->setNoClientPredictions(false);
        $players = $this->players;
        unset($players[$this->juggernaut]);

        OqexPractice::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($players): void {
            foreach ($players as $player){
                $player->getInventory()->setContents([
                    1 => ItemUtils::enchant(VanillaItems::DIAMOND_SWORD(), [VanillaEnchantments::SHARPNESS()], [1])
                ]);
                $player->getArmorInventory()->setContents([
                    0 => ItemUtils::enchant(VanillaItems::IRON_HELMET(), [VanillaEnchantments::PROTECTION()], [3]),
                    1 => ItemUtils::enchant(VanillaItems::IRON_CHESTPLATE(), [VanillaEnchantments::PROTECTION()], [3]),
                    2 => ItemUtils::enchant(VanillaItems::IRON_LEGGINGS(), [VanillaEnchantments::PROTECTION()], [3]),
                    3 => ItemUtils::enchant(VanillaItems::IRON_BOOTS(), [VanillaEnchantments::PROTECTION()], [3])
                ]);
                $player->setCanBeDamaged(true);
                $player->setNoClientPredictions(false);
            }
        }), 10 * 20);

        OqexPractice::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function(): void
        {
            if ($this->ended) return;
            $this->end(self::WINNER_JUGGERNAUT);
            foreach ($this->players as $player){
                $player->sendMessage('§r§l§6EVENT §r§8» §bEvent has run out of time');
            }
        }), 10 * 60 * 20);
    }

    public function getMap(): string
    {
        return 'JuggernautEvent';
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
        $p->setMaxHealth(20);
        $p->setNoClientPredictions();

        $p->setCanBeDamaged(false);
        if ($p->getWorld()->getFolderName() !== $this->getWorldName()) $p->preTeleport($this->getRandomSpawn());
        else $p->teleport($this->getRandomSpawn());
        $p->getInventory()->setItem(8, InteractiveItems::LEAVE_EVENT()->getActualItem($p));
    }

    public function getAttackCoolDown(): int
    {
        $cooldown = Type::$config->getNested('Juggernaut.cooldown', 10);
        if(!is_int($cooldown)){
            throw new \TypeError('Expected int, got ' . gettype($cooldown));
        }
        return $cooldown;
    }

    /** @return array{'yKb': float, 'xzKb': float, 'maxHeight': int<0, max>, 'revert': bool} */
    public function getKB(): array
    {
        $yKb = Type::$config->getNested('Juggernaut.kb.y', 0.4); //TODO: Default for now
        if(!is_float($yKb)){
            throw new \TypeError('Expected float, got ' . gettype($yKb));
        }
        $xzKb = Type::$config->getNested('Juggernaut.kb.xz', 0.4); //TODO: Default for now
        if(!is_float($xzKb)){
            throw new \TypeError('Expected float, got ' . gettype($xzKb));
        }
        $maxHeight = Type::$config->getNested('Juggernaut.kb.height', 3);
        if(!is_int($maxHeight)){
            throw new \TypeError('Expected int, got ' . gettype($maxHeight));
        }
        if($maxHeight <= 0){
            throw new \TypeError('Expected positive int, got ' . $maxHeight);
        }
        $revert = Type::$config->getNested('Juggernaut.kb.revert', false);
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
            $p = $player->getUniqueId()->toString();
            $name = $player->getName();
        }
        else{
            $p = $player;
            if(($pl = Server::getInstance()->getPlayerByUUID(Uuid::fromString($p))) instanceof PracticePlayer){
                $name = $pl->getName();
            } else $name = 'Unknown';
        }

        foreach ($this->getRealPlayers() as $pl) {
            $pl->sendMessage('§r§l§6EVENT §r§8» §b' . $name . ' §7has been disqualified!');
        }
        if($p === $this->juggernaut){
            $this->end(self::WINNER_PLAYERS);
        } else {
            unset($this->alive[$p]);
            if(($i = Server::getInstance()->getPlayerByUUID(Uuid::fromString($p))) instanceof PracticePlayer) $i->spectator();
            if (count($this->alive) <= 1) {
                $this->end(self::WINNER_JUGGERNAUT);
            }
        }
    }

    public function leave(string $uuid): void
    {
        if (($p = Server::getInstance()->getPlayerByUUID(Uuid::fromString($uuid))) instanceof PracticePlayer) $p->setEvent(null);
        unset($this->players[$uuid]);
        if ($this->hasStarted) $this->disqualify($uuid);
        else if (count($this->getRealPlayers()) === 0) $this->end(self::WINNER_NONE);
    }

    public function end(int $winner): void
    {
        $this->ended = true;

        if ($winner === self::WINNER_JUGGERNAUT) {
            $jName = 'Unknown';
            if (($p = Server::getInstance()->getPlayerByUUID(Uuid::fromString($this->juggernaut))) instanceof PracticePlayer) $jName = $p->getName();

            foreach ($this->getRealPlayers() as $player) {
                if ($player->isOnline()) {
                    $player->sendMessage('§r§l§6EVENT §r§8» §b'  . $jName . ' (Juggernaut) §r§7has won the event!');
                }
            }
        } else if ($winner === self::WINNER_PLAYERS) {
            foreach ($this->getRealPlayers() as $player) {
                if ($player->isOnline() && $player->getUniqueId()->toString() !== $this->juggernaut) {
                    $player->sendMessage('§r§l§6EVENT §r§8» §bYou §r§7 (Player) have won the event!');
                } else if ($player->isOnline() && $player->getUniqueId()->toString() === $this->juggernaut) {
                    $player->sendMessage('§r§l§6EVENT §r§8» §bYou §r§7 (Juggernaut) have lost the event!');
                }
            }
        }

        foreach ($this->getRealPlayers() as $player) {
            if ($player->isOnline()) {
                $player->sendTitle('§r');
            }
        }

        $world = Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName()) ?? throw new AssumptionFailedError('Juggernaut world should not be unloaded');
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