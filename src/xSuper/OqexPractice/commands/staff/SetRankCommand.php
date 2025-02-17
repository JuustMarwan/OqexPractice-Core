<?php

namespace xSuper\OqexPractice\commands\staff;

use pocketmine\player\OfflinePlayer;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\RawStringArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use xSuper\OqexPractice\commands\arguments\OfflinePlayerArgument;
use xSuper\OqexPractice\ffa\OITCFFA;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\PracticePlayer;

class SetRankCommand extends BaseCommand
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
            if ($sender->getData()->getRankPermission() < RankMap::permissionMap('owner')) {
                $sender->sendMessage('§r§cYou do not have permission to run this command!');
                return;
            }
        }

		/** @var ?string $p */
        $p = $args['offlinePlayer'] ?? null;

        if ($p === null) {
            $sender->sendMessage('§r§cYou need to specify a player!');
            return;
        }
		$player = Server::getInstance()->getOfflinePlayer($p);
		if ($player instanceof OfflinePlayer && !$player->hasPlayedBefore()) {
			$sender->sendMessage('§r§cThat player has never joined the server!');
			return;
		}

		$rank = $args['rank'] ?? null;

		if ($rank === null) {
			$sender->sendMessage('§r§cYou need to specify a rank!');
			return;
		}

		$rank = strtolower($rank);

		if (!in_array($rank, RankMap::RANKS, true)) {
			$sender->sendMessage('§r§cThat rank does not exist!');
			return;
		}

		OqexPractice::getDatabase()->executeGeneric('oqex-practice.players.set_rank_by_lowercase_username', [
			'username' => $p,
			'rank' => $rank
		], function () use ($rank, $p, $sender): void {
			$sender->sendMessage('§r§aYou have successfully set §b' . $p . "'s §7rank to §b" . $rank);
			if (($p = Server::getInstance()->getPlayerExact($p)) !== null && $p->isOnline() && $p instanceof PracticePlayer) {
				//if (RankMap::permissionMap($rank) <= RankMap::permissionMap('ultra')) $p->getData()->getCosmetics()->setChatColor('§f');
				$p->sendMessage('Your rank was updated to ' . RankMap::getRankTag($rank));
				if (!$p->getFFA() instanceof OITCFFA) RankMap::formatTag($p);
				$p->getData()->setRank($rank);
			}
		});
    }

    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new OfflinePlayerArgument('offlinePlayer', $this->values, true));
        $this->registerArgument(1, new RawStringArgument('rank', true)); // TODO: Rank argument
    }
}