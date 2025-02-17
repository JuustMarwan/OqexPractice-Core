<?php

namespace xSuper\OqexPractice\commands\staff;

use pocketmine\player\OfflinePlayer;
use pocketmine\Server;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use xSuper\OqexPractice\commands\arguments\OfflinePlayerArgument;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\PracticePlayer;

class UnbanCommand extends BaseCommand
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
            if ($sender->getData()->getRankPermission() < RankMap::permissionMap('moderator')) {
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

		OqexPractice::getDatabase()->executeChange('oqex-practice.players.unban_by_lowercase_username', [
			'username' => $p
		], function(int $changedRows) use ($p, $sender): void{
			if ($changedRows === 0) {
				$sender->sendMessage('§r§l§c(!) §r§cThat player is not banned!');
				return;
			}
			$sender->sendMessage("§r§l§5Versai §r§8» §fYou've unbanned §b" . $p . "§f from the server");
			/**  ServerManager::sendMessages([
			'§r§l§eSTAFF §r§8» §e' . $p->getName() . '§7 was banned for §e' . $p->getRemainingTime() . ' §7by §e' . $staff . '§7 for §e' . $reason
			], ServerManager::getAllOnlineStaff()); */
		});
    }

    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new OfflinePlayerArgument('offlinePlayer', $this->values, true));
    }
}