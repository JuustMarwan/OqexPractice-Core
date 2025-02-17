<?php

namespace xSuper\OqexPractice\listeners;

use pocketmine\block\Slime;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\world\sound\BlazeShootSound;
use xSuper\OqexPractice\entities\ServerSelectorNPC;
use xSuper\OqexPractice\entities\TopEloPlayerEntity;
use xSuper\OqexPractice\ffa\OITCFFA;
use xSuper\OqexPractice\ffa\SumoFFA;
use xSuper\OqexPractice\items\custom\CustomItem;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\ChatHandler;
use xSuper\OqexPractice\player\cosmetics\CosmeticManager;
use xSuper\OqexPractice\player\data\PlayerInfo;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\PlayerSqlHelper;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\tasks\ProfanityFilterTask;
use xSuper\OqexPractice\utils\LocalAC;
use xSuper\OqexPractice\utils\translate\ChatTranslateEvent;
use xSuper\OqexPractice\utils\translate\Loader;

class PlayerListener implements Listener
{
    public function __construct(private PluginBase $plugin)
    {

    }

    public function onPlayerCreate(PlayerCreationEvent $ev): void
    {
        $ev->setPlayerClass(PracticePlayer::class);
    }

    public function onPreJoin(PlayerPreLoginEvent $ev): void
    {
		/** @var array{'ClientRandomId'?: int, 'CurrentInputMode'?: int, 'DefaultInputMode'?: int, 'DeviceId'?: string, 'DeviceModel'?: string, 'DeviceOS'?: int, 'GameVersion'?: string, 'GuiScale'?: int, 'SelfSignedId'?: string, 'UIProfile'?: int} $data */
		$data = $ev->getPlayerInfo()->getExtraData();
		PlayerInfo::create($ev->getPlayerInfo()->getUuid()->toString(), $data);

        PlayerSqlHelper::create($ev->getPlayerInfo()->getUuid(), $ev->getPlayerInfo()->getUsername());
    }

    public function onJoin(PlayerJoinEvent $ev): void
    {
        $player = $ev->getPlayer();
        $ev->setJoinMessage('');

        if ($player instanceof PracticePlayer) {
            //$player->banEvasion();
            $player->init();
        }
    }

    public function onPickupItem(EntityItemPickupEvent $e): void
    {
        $player = $e->getEntity();

        if ($player instanceof PracticePlayer) {
            if ($player->getVanished() || $player->getSpectator() || !$player->isLoaded()) $e->cancel();
        }
    }

    public function onPlayerChangeSkin(PlayerChangeSkinEvent $event) : void{
        $p = $event->getPlayer();
        if($p instanceof PracticePlayer && $p->tryChangeSkin()){
            $p->setChangeSkin(false);
            CosmeticManager::applyCosmetics($p, $event->getNewSkin(), true);
            $p->sendSkin();
        }
        $event->cancel();
    }

    public function onQuit(PlayerQuitEvent $ev): void {
        $player = $ev->getPlayer();
        if ($player instanceof PracticePlayer && $player->isLoaded()) {
			$FFA = $player->getFFA();
			if ($FFA !== null) {
                if ($player->getTagger() !== null) {
                    $FFA->leave($player->getTagger(), $player);
                } else $FFA->subtractPlayer();
            }

            $player->getData()->getCosmetics()->save($player->getUniqueId());

            $player->reset($this->plugin);
            if ($player->getParty() !== null) {
                Party::getParty($player->getParty())?->kick($player);
            }
        }
        $ev->setQuitMessage('');


        if ($player->spawned && $player->isLoaded()) foreach ($this->plugin->getServer()->getOnlinePlayers() as $p) {
            $p->sendMessage('§r§7[§c-§7] §c' . $ev->getPlayer()->getName());
        }
    }

    public function onItemUse(PlayerItemUseEvent $ev): void
    {
        $i = $ev->getItem();
        if ($i->getNamedTag()->getTag('customItem') !== null) {
            $name = $i->getNamedTag()->getString('customItem');
            $item = CustomItem::getItem($name);
            $p = $ev->getPlayer();
            /** @var PracticePlayer $p */
            if ($item !== null && $p->isLoaded()) $item->interact($p);
        }
    }

    public function onHunger(PlayerExhaustEvent $ev): void
    {
        $ev->cancel();
    }

    public function onChat(PlayerChatEvent $e): void
    {
        /** @var PracticePlayer $player */
        $player = $e->getPlayer();
        $e->cancel();

        if (!$player->isLoaded()) return;

        $name = $player->editKit;
        if ($name !== null) {
            $msg = strtolower($e->getMessage());
            if ($msg !== 'cancel' && $msg !== 'save') {
                $player->sendMessage("\n§r§l§6Editing Kit: $name\n\n§r§7Type §l§cCANCEL §r§for §l§aSAVE");
                return;
            }

            if ($msg === 'cancel') {
                $player->editKit = null;
                $player->sendMessage("§r§cYour Kit has not been saved!");
                $player->reset($this->plugin);
                return;
            } else if ($msg === 'save') {
                $player->editKit = null;
                $player->reset($this->plugin);
                PlayerSqlHelper::saveKit($player->getUniqueId(), $name, $player->getInventory());
                $player->sendMessage("§r§aYour Kit has been saved!");
                return;
            }
        }

        //if ($e->getMessage() === '.test') {
        //    CosmeticManager::test($e->getPlayer(), $player->getSkin());
        //   return;
        //}

        //if ($e->getMessage() === '.test1') {
        //    $task = new PackAnimateTask($player, $player->getPosition());
        //    OqexPractice::getInstance()->getScheduler()->scheduleRepeatingTask($task, 20);
        //}



        $r = $player->getChatHandler()->canSendMessage($e->getMessage());

        if ($r !== null) switch ($r[0]) {
            case ChatHandler::MUTED:
                $player->sendMessage('§r§cYou are currently muted.');
                return;
            case ChatHandler::COOL_DOWN:
                $player->sendMessage('§r§cPlease slow down with your messages!');
                return;
        }

        $format = RankMap::formatChat($player, $e->getMessage());

        $players = [];
        foreach (Server::getInstance()->getOnlinePlayers() as $p) {
            if ($p instanceof PracticePlayer && $p->isLoaded()) {
                if ($p->getData()->getSettings()->asBool(SettingIDS::CHAT_MESSAGE)) {
                    if ($p->getData()->getSettings()->asBool(SettingIDS::PROFANITY)) $p->sendMessage($format);
                    else $players[] = $p->getUniqueId()->toString();
                }
            }
        }


        Server::getInstance()->getAsyncPool()->submitTask(new ProfanityFilterTask($e->getMessage(), $player->getUniqueId()->toString(), $players, OqexPractice::getInstance()->getDataFolder()));
    }



    public function onInventoryTransaction(InventoryTransactionEvent $event): void
    {
        $player = $event->getTransaction()->getSource();

        if ($player instanceof PracticePlayer) {
            if ($player->editKit !== null) {
                foreach ($event->getTransaction()->getInventories() as $inventory) {
                    if ($inventory instanceof ArmorInventory) $event->cancel();;
                }

                return;
            }
            if ($player->getDuel() === null && !$player->canBeDamaged()) {
                $event->cancel();
            }
        }
    }

    public function onPlayerMove(PlayerMoveEvent $ev): void {
        $p = $ev->getPlayer();
        if ($p instanceof PracticePlayer && !$p->isLoaded()) {
            $ev->cancel();
            return;
        }

        if ($ev->getTo()->getWorld()->getFolderName() === 'Lobby') {
            $player = $ev->getPlayer();
            $from = $ev->getFrom();
            $to = $ev->getTo();
            if ($from->distance($to) < 0.1) {
                return;
            }
            $maxDistance = 15;
            foreach ($player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy($maxDistance, $maxDistance, $maxDistance), $player) as $e) {
                if ($e instanceof ServerSelectorNPC || $e instanceof TopEloPlayerEntity) {
                    $pos = $player->getPosition();
                    $ePos = $e->getPosition();
                    $xdiff = $pos->x - $ePos->x;
                    $zdiff = $pos->z - $ePos->z;
                    $angle = atan2($zdiff, $xdiff);
                    $yaw = (($angle * 180) / M_PI) - 90;
                    $ydiff = $pos->y - $ePos->y;
                    $v = new Vector2($ePos->x, $ePos->z);
                    $dist = $v->distance(new Vector2($pos->x, $pos->z));
                    $angle = atan2($dist, $ydiff);
                    $pitch = (($angle * 180) / M_PI) - 90;

                    $pk = new MovePlayerPacket();
                    $pk->actorRuntimeId = $e->getId();
                    $pk->position = $e->getLocation()->add(0, $e->getEyeHeight(), 0);
                    $pk->yaw = $yaw;
                    $pk->pitch = $pitch;
                    $pk->headYaw = $yaw;
                    $pk->onGround = $e->onGround;
                    $player->getNetworkSession()->sendDataPacket($pk);
                }
            }
        }
    }

    public function onPlayerMovement(PlayerMoveEvent $event): void{
        /** @var PracticePlayer $p */
        $p = $event->getPlayer();
        if ($p instanceof PracticePlayer && !$p->isLoaded()) {
            $event->cancel();
            return;
        }

        if ($p->getFFA() instanceof OITCFFA || $p->getFFA() instanceof SumoFFA) {
            $min = match ($p->getFFA()->getName()) {
                default => 0,
                'OITC' => -66,
                'Sumo' => 52
            };

            if ($event->getTo()->getY() <= $min) {
                $tagger = $p->getTagger();
                if ($tagger !== null) {
                    $tagger->removeCombatTag();
                    $tagger->rmPearl();
                }

                if ($p->isLoaded() && $p->getData()->getSettings()->getSetting(SettingIDS::FFA_RESPAWN)) $p->getFFA()->reset($tagger, $p);
                else {
                    $p->getFFA()->leave($tagger, $p);
                    $p->reset($this->plugin);
                }
            }
        }

        if ($event->getTo()->getWorld()->getFolderName() === 'Lobby' || $event->getTo()->getWorld()->getFolderName() === 'OITCFFA') {
            $player = $event->getPlayer();
            $world = $player->getLocation()->getWorld();

            if ($event->getTo()->asVector3()->equals($event->getFrom())) return;


            $pos = $player->getPosition();

            $block = $world->getBlock($pos->add(0, -1, 0));
            if ($block instanceof Slime) {
                $y = 1.5;
                if ($block->getPosition()->distance(new Vector3(-84, 73, 0)) <= 15) {
                    $direction = $player->getDirectionPlane()->normalize()->multiply(3.5);
                } else {
                    $direction = $player->getDirectionPlane()->normalize()->multiply(0.3);
                }

                $world->addSound(
                    $player->getPosition(),
                    new BlazeShootSound(),
                    [
                        $player
                    ]
                );

                $player->setMotion(
                    new Vector3(
                        $direction->getX(),
                        $y,
                        $direction->getY()
                    )
                );
            }

            if ($event->getTo()->getWorld()->getFolderName() === 'OITCFFA' && $event->getTo()->getY() <= -54) {
                $player->getEffects()->add(new EffectInstance(VanillaEffects::WITHER(), 9999999, 9, false, false));
                $player->sendTip('§r§l§4Return to the Arena');
            }

            if ($event->getTo()->getY() > -54 && $player->getEffects()->has(VanillaEffects::WITHER()) && $event->getTo()->getWorld()->getFolderName() === 'OITCFFA') {
                $player->getEffects()->clear();
                $player->sendTip('§r');
            }
        }
    }
}