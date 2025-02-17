<?php

namespace xSuper\OqexPractice\commands\staff;

use pocketmine\player\OfflinePlayer;
use pocketmine\Server;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use xSuper\OqexPractice\commands\arguments\OfflinePlayerArgument;
use xSuper\OqexPractice\duel\utils\Leaderboard;
use xSuper\OqexPractice\duel\utils\LeaderboardIds;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\poggit\libasynql\result\SqlSelectResult;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\poggit\libasynql\SqlThread;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\PracticePlayer;

class ResetStatsCommand extends BaseCommand
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

		OqexPractice::getDatabase()->executeMulti('oqex-practice.stats.reset_and_get_by_lowercase_username', [
			'username' => $p
		], SqlThread::MODE_SELECT, function(array $results): void{
			$statsRows = array_map(fn(SqlSelectResult $result) => $result->getRows(), $results);
			foreach([
				LeaderboardIds::DAILY => 0,
				LeaderboardIds::WEEKLY => 1,
				LeaderboardIds::MONTHLY => 2,
				LeaderboardIds::LIFETIME => 3
			] as $timeframe => $addedIndex){
				Leaderboard::updateKillsWithData($timeframe, $statsRows[3 + $addedIndex]);
				Leaderboard::updateDeathsWithData($timeframe, $statsRows[7 + $addedIndex]);
				Leaderboard::updateKDWithData($timeframe, $statsRows[11 + $addedIndex]);
				Leaderboard::updateParkourWithData($timeframe, $statsRows[15 + $addedIndex]);
			}
		});
    }

    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new OfflinePlayerArgument('offlinePlayer', $this->values, true));
    }
}