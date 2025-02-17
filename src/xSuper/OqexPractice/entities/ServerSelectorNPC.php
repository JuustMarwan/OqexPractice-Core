<?php

namespace xSuper\OqexPractice\entities;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use xSuper\OqexPractice\duel\utils\LeaderboardIds;
use xSuper\OqexPractice\entities\custom\CustomEntity;
use xSuper\OqexPractice\OqexPractice;

class ServerSelectorNPC extends CustomEntity implements LeaderboardIds
{
    public function __construct(Location $location, ?CompoundTag $nbt = null)
    {
        parent::__construct($location, self::createSkin(self::getSkinDataFromPNG(OqexPractice::getInstance()->getDataFolder() . 'images/NPC.png')), $nbt);

        $this->setNameTagAlwaysVisible();
        $this->setNameTag("§r§l§aServer Selector\n§r§7Interact to switch servers");
    }

    public function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(2, 1);
    }

    public function attack(EntityDamageEvent $source): void
    {
        if ($source instanceof EntityDamageByEntityEvent) {
			$damager = $source->getDamager();
			if ($damager instanceof Player) {
                $damager->sendMessage('§r§l§6Coming Soon');
            }
        }

        $source->cancel();
    }

    public function onInteract(Player $player, Vector3 $clickPos): bool
    {
        $player->sendMessage('§r§l§6Coming Soon');
        return true;
    }
}