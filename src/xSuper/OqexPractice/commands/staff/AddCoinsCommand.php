<?php

namespace xSuper\OqexPractice\commands\staff;

use pocketmine\player\OfflinePlayer;
use pocketmine\Server;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\IntegerArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use xSuper\OqexPractice\commands\arguments\OfflinePlayerArgument;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\PlayerSqlHelper;
use xSuper\OqexPractice\player\PracticePlayer;

class AddCoinsCommand extends BaseCommand
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

		$offlinePlayer = Server::getInstance()->getOfflinePlayer($p);
		if ($offlinePlayer instanceof OfflinePlayer && !$offlinePlayer->hasPlayedBefore()) {
			$sender->sendMessage('§r§cThat player has never joined the server!');
			return;
		}

		$add = $args['amount'] ?? null;

		if ($add === null) {
			$sender->sendMessage('§r§cYou need to specify an amount!');
			return;
		}
        PlayerSqlHelper::addCoinsByLowerName($p, $add);
		$player = Server::getInstance()->getPlayerExact($p);
		if($player instanceof PracticePlayer){
			$player->getData()->setCoins($player->getData()->getCoins() + $add);
		}
    }

    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new OfflinePlayerArgument('offlinePlayer', $this->values, true));
        $this->registerArgument(1, new IntegerArgument('amount', true));
    }
}