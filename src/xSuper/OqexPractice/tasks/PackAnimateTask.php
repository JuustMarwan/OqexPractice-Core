<?php

namespace xSuper\OqexPractice\tasks;

use pocketmine\block\utils\DyeColor;
use pocketmine\color\Color;
use pocketmine\entity\Entity;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\ActorEvent;
use pocketmine\network\mcpe\protocol\types\ParticleIds;
use pocketmine\Server;
use pocketmine\world\particle\Particle;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\player\Player;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\Task;
use pocketmine\world\World;
use xSuper\OqexPractice\entities\firework\FireworkRocket;
use xSuper\OqexPractice\entities\firework\FireworkRocketExplosion;
use xSuper\OqexPractice\entities\firework\FireworkRocketType;
use xSuper\OqexPractice\entities\PackItemEntity;
use xSuper\OqexPractice\entities\VotePartySheepEntity;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;

class PackAnimateTask extends Task
{
    private int $time = 0;

    public function __construct(private readonly PracticePlayer $player, private readonly Vector3 $vector)
    {

    }

    private ?EntityCircleTask $task = null;
    /** @var VotePartySheepEntity[] */
    private array $entities = [];

    public function onRun(): void
    {
        if ($this->time === 0) {
            $radius = 2;
            $angleStep = 360 / 8;

            $world = $this->player->getWorld();

            $entities = [];
            foreach ([DyeColor::YELLOW, DyeColor::GREEN, DyeColor::CYAN, DyeColor::BLUE, DyeColor::PURPLE, DyeColor::PINK, DyeColor::RED, DyeColor::ORANGE] as $index => $color) {
                $angle = $index * $angleStep;
                $x = $this->vector->getX() + $radius * cos(deg2rad($angle));
                $z = $this->vector->getZ() + $radius * sin(deg2rad($angle));

                $location = new Location($x, $this->vector->getY(), $z, $world, 0, 0);

                $e = new PackItemEntity($location, VanillaItems::PAPER());
                $e->setOwningEntity($this->player);
                $e->setNameTag("§r§l§fHAT '§r§bExample§l§f'");
                $e->setNameTagAlwaysVisible();
                $e->setNameTagVisible();
                $e->spawnTo($this->player);
                $entities[] = $e;
            }

            $this->entities = $entities;

            $speed = 1;

            $task = new EntityCircleTask($entities, $this->vector, $radius, $speed);
            OqexPractice::getInstance()->getScheduler()->scheduleDelayedRepeatingTask($task, 20, 1);
            $this->task = $task;
        }

        if ($this->time < 5 && $this->time % 2 === 0) {
            $this->player->sendSound('item.book.page_turn');
        }

        if ($this->time === 5 && $this->task !== null) $this->task->cancel();

        if ($this->time >= 7 && $this->time % 2 === 0) {
            if (count($this->entities) === 0) throw new CancelTaskException();

            $world = $this->player->getWorld();

            foreach ($this->entities as $k => $entity) {
                if (!$entity->isClosed()) {
                    $this->playLightning($entity->getLocation());

                    $e = new FireworkRocket(new Location($this->vector->x, $this->vector->y, $this->vector->z, $world, 0, 0), 3, [
                        new FireworkRocketExplosion(FireworkRocketType::BURST, [DyeColor::YELLOW])
                    ]);
                    $e->setOwningEntity($this->player);
                    $e->spawnTo($this->player);

                    $entity->flagForDespawn();
                    unset($this->entities[$k]);
                    shuffle($this->entities);
                    break;
                }
            }
        }

        $this->time++;
    }

    public function playLightning(Location $location): void
    {
        $vec = new Vector3($location->x, $location->y, $location->z);

        $light = AddActorPacket::create(Entity::nextRuntimeId(), 1, "minecraft:lightning_bolt", $vec, null, 0, 0, 0.0, 0, [], [], new PropertySyncData([], []), []);
        $sound = PlaySoundPacket::create("ambient.weather.lightning.impact", $location->x, $location->y, $location->z, 1, 1);

        $this->player->getNetworkSession()->sendDataPacket($light);
        $this->player->getNetworkSession()->sendDataPacket($sound);
    }

}