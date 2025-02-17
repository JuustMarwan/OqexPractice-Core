<?php

namespace xSuper\OqexPractice\commands\defaults;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\commands\arguments\GamemodeArgument;
use xSuper\OqexPractice\commands\arguments\PlayerArgument;
use xSuper\OqexPractice\player\PracticePlayer;

class GamemodeCommand extends BaseCommand
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

		/** @var string $gm */
        $gm = $args['gamemode'];
        $gm = match (strtolower($gm)) {
            'c', 'creative' => GameMode::CREATIVE(),
            's', 'survival' => GameMode::SURVIVAL(),
            'a', 'adventure' => GameMode::ADVENTURE(),
            'sp', 'spectator' => GameMode::SPECTATOR(),
            default => throw new AssumptionFailedError('Unreachable'),
        };

        $player = $args['player'] ?? null;

        if ($sender instanceof ConsoleCommandSender && $player === null) {
            $sender->sendMessage('§r§cYou need to specify a player!');
            return;
        }

        if ($sender instanceof Player && $player === null) $player = $sender;

        if (!$player instanceof Player) {
            $sender->sendMessage('§r§cThat player is not online!');
            return;
        }

        $player->setGamemode($gm);
        $sender->sendMessage('§r§7Successfully set ' . $player->getName() . "'s" . ' gamemode to ' . $gm->getEnglishName() . '!');
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new GamemodeArgument('gamemode'));
        $this->registerArgument(1, new PlayerArgument('player', true));
    }
}