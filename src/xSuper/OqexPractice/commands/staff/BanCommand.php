<?php

namespace xSuper\OqexPractice\commands\staff;

use DateTime;
use pocketmine\player\OfflinePlayer;
use pocketmine\player\Player;
use pocketmine\Server;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\RawStringArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\TextArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use xSuper\OqexPractice\commands\arguments\OfflinePlayerArgument;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\PlayerSqlHelper;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\utils\TimeUtils;

class BanCommand extends BaseCommand
{
	/** @param array<string, string> $values */
    public function __construct(private array $values, PluginBase $plugin, string $name, string $description = "", array $aliases = [])
    {
        parent::__construct($plugin, $name, $description, $aliases);
    }

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

        $p = $args['offlinePlayer'] ?? null;

        if ($p === null) {
            $sender->sendMessage('§r§cYou need to specify a player!');
            return;
        }

		$player = Server::getInstance()->getOfflinePlayer($p);
		if ($player instanceof OfflinePlayer && !$player->hasPlayedBefore()) {
			$sender->sendMessage('§r§l§c(!) §r§cThat player has never joined the server!');
			return;
		}

		$t = $args['duration'] ?? 'perm';

		if ($t !== 'perm') {
			$d = TimeUtils::stringToTimestampAdd($t, new DateTime());
			if ($d === null) {
				$sender->sendMessage('§r§cYou need to specify a valid amount of time (perm or s=seconds,m=minutes,h=hours,d=days,w=weeks,mo=months,y=years)!');
				return;
			}
		}

		$reason = $args['reason'] ?? null;

		if ($sender instanceof Player){
			$staff = $sender->getName();
			$staffUuid = $sender->getUniqueId();
		}else{
			$staff = 'Console';
			$staffUuid = null;
		}

		PlayerSqlHelper::banByLowerName($staffUuid, $p, $t, $staff, $reason, function: function (int $successCode) use
		(
			$staff,
			$t,
			$p,
			$reason,
			$sender
		): void{
			if ($successCode === 1) {
				$sender->sendMessage('§r§l§c(!) §r§cThat player is already banned!');
				return;
			}

			if ($successCode === 2) {
				$sender->sendMessage('§r§l§c(!) §r§cThat player has a rank equal to or higher than yours!');
				return;
			}

			if ($t !== 'perm') $t = (TimeUtils::stringToTimestampAdd($t, new DateTime()))[0]->format('Y-m-d H-i-s');
			if ($reason === null) $reason = 'No reason given!';

			$arr = ['duration' => $t, 'staff' => $staff, 'reason' => $reason];
			$p = Server::getInstance()->getPlayerExact($p);
			if ($p instanceof PracticePlayer && $p->isOnline()) {
				$p->getData()->setBanned($arr);
				if ($t === 'perm') {
					$s = '§r§cYou are permanently banned!';
				} else {
					$to = new DateTime();
					$from = date_create_from_format('Y-m-d H-i-s', $t);
					if ($from <= $to) return;
					else $s = '§r§cYou are banned for ' . TimeUtils::formatDate(new DateTime(), $from);
				}


				$s .= "\n" . ' §fReason: ' . $reason . ' [' . $staff . "]\n";
				$p->kick($s);
			}
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->sendMessage('§r§l§5Versai §r§8» §b' . $sender->getName() . '§f has banned §b' . $p . '§f from the server for - ' . $reason);
            }

            /**  ServerManager::sendMessages([
                '§r§l§eSTAFF §r§8» §e' . $p->getName() . '§7 was banned for §e' . $p->getRemainingTime() . ' §7by §e' . $staff . '§7 for §e' . $reason
            ], ServerManager::getAllOnlineStaff()); */

        });
    }

    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new OfflinePlayerArgument('offlinePlayer', $this->values, true));
        $this->registerArgument(1, new RawStringArgument('duration', true));
        $this->registerArgument(2, new TextArgument('reason', true));
    }
}