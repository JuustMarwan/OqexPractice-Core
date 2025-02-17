<?php

namespace xSuper\OqexPractice\duel\special;

use pocketmine\entity\Location;
use pocketmine\player\GameMode;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\world\Position;
use xSuper\OqexPractice\bot\BotType;
use xSuper\OqexPractice\duel\Duel;
use xSuper\OqexPractice\duel\generator\MapGenerator;
use xSuper\OqexPractice\duel\generator\maps\Map;
use xSuper\OqexPractice\duel\type\Type;
use xSuper\OqexPractice\entities\bots\archer\DummyArcherBot;
use xSuper\OqexPractice\entities\bots\archer\EasyArcherBot;
use xSuper\OqexPractice\entities\bots\archer\GodlyArcherBot;
use xSuper\OqexPractice\entities\bots\archer\HardArcherBot;
use xSuper\OqexPractice\entities\bots\archer\NormalArcherBot;
use xSuper\OqexPractice\entities\bots\gapple\NormalGappleBot;
use xSuper\OqexPractice\entities\bots\nodebuff\DummyNoDebuffBot;
use xSuper\OqexPractice\entities\bots\nodebuff\DummySumoBot;
use xSuper\OqexPractice\entities\bots\nodebuff\EasyNoDebuffBot;
use xSuper\OqexPractice\entities\bots\nodebuff\EasySumoBot;
use xSuper\OqexPractice\entities\bots\nodebuff\GodlyNoDebuffBot;
use xSuper\OqexPractice\entities\bots\nodebuff\GodlySumoBot;
use xSuper\OqexPractice\entities\bots\nodebuff\HardNoDebuffBot;
use xSuper\OqexPractice\entities\bots\nodebuff\HardSumoBot;
use xSuper\OqexPractice\entities\bots\nodebuff\NormalNoDebuffBot;
use xSuper\OqexPractice\entities\bots\nodebuff\NormalSumoBot;
use xSuper\OqexPractice\entities\bots\soup\NormalSoupBot;
use xSuper\OqexPractice\entities\pathfinder\entity\ArcherEntity;
use xSuper\OqexPractice\entities\pathfinder\entity\GappleEntity;
use xSuper\OqexPractice\entities\pathfinder\entity\SmartEntity;
use xSuper\OqexPractice\entities\pathfinder\entity\SoupEntity;
use xSuper\OqexPractice\entities\pathfinder\entity\SumoEntity;
use xSuper\OqexPractice\player\kit\Kit;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\utils\scoreboard\Scoreboard;
use xSuper\OqexPractice\utils\scoreboard\Scoreboards;

class BotDuel extends Duel
{
    protected ?PracticePlayer $winner = null;
    private null|SmartEntity|ArcherEntity|SumoEntity|GappleEntity|SoupEntity $bot = null;

    public function __construct(string $id, Map $map, private PracticePlayer $player, Type $type, private BotType $botType)
    {
        parent::__construct($id, $map, $type, [$this->player]);
    }

    public function init(PluginBase $plugin): void
    {
        $map = $this->map;

        MapGenerator::genMap($plugin, $plugin->getServer()->getDataPath(), $this->getWorldName(), $plugin->getServer()->getDataPath() . '/worlds/' . $this->getWorldName(), function() use ($plugin, $map): void{
			if(!$plugin->getServer()->getWorldManager()->loadWorld($this->getWorldName())){
				if($this->player->isOnline()) $this->player->sendMessage('§r§cYour duel failed to generate a map, if you believe this is an error please contact a staff member!');

				$this->end($plugin);
				return;
			}

            $world = $plugin->getServer()->getWorldManager()->getWorldByName($this->getWorldName());
            $world->setTime(6000);
            $world->stopTime();

			if($this->player->isOnline()){
				$this->player->setDuel($this);
				$this->player->extinguish();
				$this->player->getInventory()->clearAll();
				$this->player->getArmorInventory()->clearAll();
				$this->player->getCursorInventory()->clearAll();
				$this->player->getOffHandInventory()->clear(0);
				$this->player->getHungerManager()->setFood($this->player->getHungerManager()->getMaxFood());
				$this->player->setHealth($this->player->getMaxHealth());
				$this->player->getEffects()->clear();
				$this->player->setAbsorption(0);
				$this->player->setGamemode(GameMode::SURVIVAL());
				if($this->ranked) $this->player->subtractRankedGame();

				$cords = $map->getSpawn1();

				$pos = new Position($cords->getX(), $cords->getY(), $cords->getZ(), $world);
				$this->player->preTeleport($pos);
			}else $this->end($plugin);

			if(!$this->ended) $this->countdown($plugin);
		}, $plugin->getDataFolder() . 'maps/' . $map->getRealName());
    }

    public function countdown(PluginBase $plugin): void
    {
        $countDown = 6;
        $plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use (& $countDown, $plugin): void {
            if ($countDown === 0) {
                if ($this->player->isOnline()) $this->player->sendTitle('§r');

                $this->start($plugin);
                throw new CancelTaskException();
            }

            if ($this->ended) {
                throw new CancelTaskException();
            }

            if (!$this->player->isOnline()) {
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

            $this->player->sendTitle($color . $countDown);
            $this->player->sendSound('random.click');

            $countDown--;
        }), 20);
    }

    public function start(PluginBase $plugin): void
    {
        $this->started = true;

        if ($this->player->isOnline()) {
            $this->player->giveKit($this->type->getKit());
            $this->player->setCanBeDamaged(true);

            $pos = $this->map->getSpawn2();
            $world = $plugin->getServer()->getWorldManager()->getWorldByName($this->getWorldName());

            $e = match ($this->botType) {
                BotType::DummyNoDebuff => new DummyNoDebuffBot(Location::fromObject($pos, $world), $this->player, null),
                BotType::EasyNoDebuff => new EasyNoDebuffBot(Location::fromObject($pos, $world), $this->player, null),
                BotType::NormalNoDebuff => new NormalNoDebuffBot(Location::fromObject($pos, $world), $this->player, null),
                BotType::HardNoDebuff => new HardNoDebuffBot(Location::fromObject($pos, $world), $this->player, null),
                BotType::GodlyNoDebuff => new GodlyNoDebuffBot(Location::fromObject($pos, $world), $this->player, null),
                BotType::DummyArcher => new DummyArcherBot(Location::fromObject($pos, $world), $this->player, null),
                BotType::EasyArcher => new EasyArcherBot(Location::fromObject($pos, $world), $this->player, null),
                BotType::NormalArcher => new NormalArcherBot(Location::fromObject($pos, $world), $this->player, null),
                BotType::HardArcher => new HardArcherBot(Location::fromObject($pos, $world), $this->player, null),
                BotType::GodlyArcher => new GodlyArcherBot(Location::fromObject($pos, $world), $this->player, null),
                BotType::NormalSoup => new NormalSoupBot(Location::fromObject($pos, $world), $this->player, null),
                default => new NormalGappleBot(Location::fromObject($pos, $world), $this->player, null)
            };

            $kit = Kit::getKit($this->type->getKit());
            $e->getArmorInventory()->setContents($kit->getArmor());
            $e->getInventory()->setContents($kit->getContents());
            $e->spawnToAll();

            $this->bot = $e;
        }

        $plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($plugin): void {
            if ($this->time >= self::MAX_TIME) {
                $this->end($plugin);
                throw new CancelTaskException();
            }

            if ($this->ended) {
                throw new CancelTaskException();
            }

            if (!$this->player->isOnline()) {
                $this->end($plugin);
                throw new CancelTaskException();
            }

            $this->time++;
        }), 20);
    }

    public function end(PluginBase $plugin): void
    {
        $this->ended = true;

        $winner = $this->winner;
        if ($winner !== null && $winner->isOnline()) {
            $winner->sendTitle('§r§l§aYou Won!');
            $winner->extinguish();
            $winner->getHungerManager()->setFood($winner->getHungerManager()->getMaxFood());
            $winner->setHealth($winner->getMaxHealth());
            $winner->getEffects()->clear();
            $winner->setGamemode(GameMode::ADVENTURE());
            $winner->removeCombatTag();
            $winner->setCanBeDamaged(false);
            $winner->setNoClientPredictions(false);
        }
        else if ($this->player->isOnline()) {
            $this->player->sendTitle('§r§l§cYou Lost!');
            $this->player->setNoClientPredictions();
            if ($this->bot instanceof SmartEntity) $this->bot->tBag();
            $this->player->spectator();
        }


        $plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($plugin): void {
            if ($this->player->isOnline()) {
                $this->player->setNoClientPredictions(false);
                $this->player->sendTitle('§l');
            }

            foreach ((Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName())?->getPlayers() ?? []) as $player) {
                /** @var PracticePlayer $player */
                $player->reset($plugin);
            }

            self::removeDuel($this);
            Scoreboard::updateScoreBoards(Scoreboards::LOBBY());
        }), 6 * 20);
    }
}