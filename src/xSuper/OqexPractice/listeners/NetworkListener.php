<?php

namespace xSuper\OqexPractice\listeners;

use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\event\world\ChunkUnloadEvent;
use pocketmine\item\EnderPearl;
use pocketmine\item\GoldenApple;
use pocketmine\item\Potion;
use pocketmine\item\SplashPotion;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\types\ActorEvent;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use xSuper\OqexPractice\entities\ArrowEntity;
use xSuper\OqexPractice\items\custom\CustomItem;
use xSuper\OqexPractice\player\PracticePlayer;

class NetworkListener implements Listener
{
    /** @var array<string, true> */
    private array $blocked = [];

    public function onDataPacketReceive(DataPacketReceiveEvent $event): void
    {
        $player = $event->getOrigin()->getPlayer();
        $packet = $event->getPacket();

        if (!$player instanceof PracticePlayer) return;

        if ($packet instanceof LevelSoundEventPacket) {
            if ($packet->sound === LevelSoundEvent::ATTACK_NODAMAGE) {
                echo "b\n";
                if ($player->getData()->getInfo()->isPE()) {
                    echo "a\n";
                    $i = $player->getInventory()->getItemInHand();

                    if ($i->getNamedTag()->getTag('customItem') !== null) {
                        $name = $i->getNamedTag()->getString('customItem');
                        $item = CustomItem::getItem($name);
                        $item?->interact($player);
                        return;
                    }

                    if (!$i instanceof Potion && !$i instanceof GoldenApple && $player->isLoaded()) {
                        echo "tap\n";
                        $player->useHeldItem();
                    }

                    if ($i instanceof SplashPotion || $i instanceof EnderPearl) {
                        echo "tap2\n";
                        $player->useHeldItem();
                    }
                }
                return;
            }
        }
    }

    public function onDataPacketSend(DataPacketSendEvent $ev): void
    {
        $packets = $ev->getPackets();

        foreach ($packets as $i => $pk) {
            if ($pk instanceof MoveActorAbsolutePacket || ($pk instanceof ActorEventPacket && $pk->eventId === ActorEvent::HURT_ANIMATION)) {
                foreach ($ev->getTargets() as $session) {
                    $id = $pk->pid() . $session->getPlayer()?->getId();
                    if (isset($this->blocked[$id])) unset($this->blocked[$id]);
                    else {
                        unset($packets[$i]);

                        $this->blocked[$id] = true;
                        $session->sendDataPacket($pk, true);

                        $ev->setPackets($packets);
                    }
                }
            }

            if ($pk instanceof LevelSoundEventPacket) {
                if ($pk->sound === LevelSoundEvent::ATTACK_NODAMAGE) {
                    $ev->cancel();
                    return;
                }
            }
        }
    }

    public function onChunkUnload(ChunkUnloadEvent $ev): void
    {
        if ($ev->getWorld()->getFolderName() === 'Lobby') $ev->cancel();
    }

    public function onQuery(QueryRegenerateEvent $event): void
    {
        $event->getQueryInfo()->setPlugins([]);
        $event->getQueryInfo()->setWorld('');
    }

    public function onShootBow(EntityShootBowEvent $ev): void
    {
        $e = new ArrowEntity($ev->getProjectile()->getLocation(), $ev->getProjectile()->getOwningEntity(), false);
        $e->setMotion($ev->getEntity()->getDirectionVector());
        $ev->setProjectile($e);
    }
}