<?php

namespace xSuper\OqexPractice\commands\staff;

use pocketmine\Server;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\RawStringArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\TextArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use DateTime;
use pocketmine\command\CommandSender;
use xSuper\OqexPractice\commands\arguments\PlayerArgument;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\utils\LocalAC;
use xSuper\OqexPractice\utils\TimeUtils;

class MuteCommand extends BaseCommand
{
	/** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof PracticePlayer) {
            if (!$sender->isLoaded()) return;
            if ($sender->getData()->getRankPermission() < RankMap::permissionMap('helper')) {
                $sender->sendMessage('§r§cYou do not have permission to run this command!');
                return;
            }
        }

        $p = $args['player'] ?? null;

        if ($p === null) {
            $sender->sendMessage('§r§cYou need to specify a player!');
            return;
        }
        if (!$p instanceof PracticePlayer || !$p->isOnline()) {
            $sender->sendMessage('§r§cThat player is not online!');
            return;
        }

        $t = $args['time'] ?? null;
        if ($t === null) {
            $sender->sendMessage('§r§cYou need to specify an amount of time (s=seconds,m=minutes,h=hours,d=days,w=weeks,mo=months,y=years)!');
            return;
        }

        $t = TimeUtils::stringToTimestampAdd($t, new DateTime());
        if ($t === null) {
            $sender->sendMessage('§r§cYou need to specify a valid amount of time (s=seconds,m=minutes,h=hours,d=days,w=weeks,mo=months,y=years)!');
            return;
        }

        $r = $args['reason'] ?? 'Unknown Reason';
        if ($sender instanceof PracticePlayer) $s = $sender->getName();
        else $s = 'Console';

        $p->getChatHandler()->mute($t[0], $s, $r);

        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $player->sendMessage('§r§l§5Versai §r§8» §b' . $sender->getName() . '§f has muted §b' . $p->getName() . '§f for - ' . $r);
        }

        /** $m = '§r§l§eSTAFF §r§8» §b' . $p->getName() . ' §7was muted by §b' . $s . ' §7for §b' . $r . ' §8- §b' . TimeUtils::formatDate($t[0], new DateTime());
        foreach (LocalAC::get() as $p1) {
            if ($p1->isOnline()) $p1->sendMessage($m);
        } */
    }

    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new PlayerArgument('player', true));
        $this->registerArgument(1, new RawStringArgument('time', true));
        $this->registerArgument(2, new TextArgument('reason', true));
    }
}