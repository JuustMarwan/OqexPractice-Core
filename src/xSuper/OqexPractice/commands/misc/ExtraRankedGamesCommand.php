<?php

namespace xSuper\OqexPractice\commands\misc;

use pocketmine\player\OfflinePlayer;
use pocketmine\Server;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\IntegerArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\RawStringArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use xSuper\OqexPractice\player\PlayerSqlHelper;
use xSuper\OqexPractice\player\PracticePlayer;

class ExtraRankedGamesCommand extends BaseCommand
{
	/** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof PracticePlayer) {
            if (!$sender->isLoaded()) return;
            if (!$sender->getData()->isOP()) {
                $sender->sendMessage('§r§cYou do not have permission to run this command!');
                return;
            }
        }

		/** @var ?string $player */
        $player = $args['player'] ?? null;
        if ($player === null) {
            $sender->sendMessage('§r§cYou need to specify a player!');
            return;
        }
		$offlinePlayer = Server::getInstance()->getOfflinePlayer($player);
		if($offlinePlayer instanceof OfflinePlayer && !$offlinePlayer->hasPlayedBefore()){
			$sender->sendMessage('§r§cYThat player does not exist!');
			return;
		}

		$EGames = $args['amount'] ?? 1;
		PlayerSqlHelper::addEGamesByName($player, $EGames);
		$practicePlayer = Server::getInstance()->getPlayerExact($player);
		if($practicePlayer instanceof PracticePlayer){
			$practicePlayer->getData()->setExtraRankedGames($practicePlayer->getData()->getExtraRankedGames() + $EGames);
		}
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new RawStringArgument('player', true));
        $this->registerArgument(1,  new IntegerArgument('amount', true));
    }
}