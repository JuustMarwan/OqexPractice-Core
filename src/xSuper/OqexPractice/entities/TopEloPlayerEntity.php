<?php

namespace xSuper\OqexPractice\entities;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;
use xSuper\OqexPractice\duel\utils\Leaderboard;
use xSuper\OqexPractice\duel\utils\LeaderboardIds;
use xSuper\OqexPractice\entities\custom\CustomEntity;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\cosmetic\CosmeticManager;

class TopEloPlayerEntity extends CustomEntity
{
    private static ?TopEloPlayerEntity $current = null;

    public static function closeCurrent(): void
    {
        if (self::$current !== null) {
            if (!self::$current->isFlaggedForDespawn() && !self::$current->isClosed()) self::$current->flagForDespawn();
        }
    }

    public static function update(): void
    {
        self::closeCurrent();

        $d = Leaderboard::getLeaderboard(LeaderboardIds::AVERAGE_ELO)->getData();

        $p = $d[0] ?? null;

        if ($p === null) return;

        $path = OqexPractice::getInstance()->getDataFolder() . 'players/skin/' . $p[0] . '.png';
        if (!file_exists($path)) $skin = self::createSkin(self::getSkinDataFromPNG(OqexPractice::getInstance()->getDataFolder() . 'cosmetic/default_skin.png'));
        else $skin = self::createSkin(self::getSkinDataFromPNG($path));

        $e = new self(new Location(75.5, 60.8, 0.5, Server::getInstance()->getWorldManager()->getDefaultWorld(), 0, 0), $skin, null);
        $e->setScale(2);
        $e->setNameTagAlwaysVisible();
        $e->setNameTag($p[0]);

        CosmeticManager::giveCrown($e, $p[0], $e->getSkin());

        $e->spawnToAll();

        self::$current = $e;
    }

    public function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(2, 2);
    }

    public function attack(EntityDamageEvent $source): void
    {
        $source->cancel();
    }

    public function onInteract(Player $player, Vector3 $clickPos): bool
    {
        return true;
    }
}